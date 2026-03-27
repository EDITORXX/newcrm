<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\SiteVisitCreated;
use App\Models\SiteVisit;
use App\Models\Lead;
use App\Models\Prospect;
use App\Services\AsmCnpAutomationService;
use App\Services\TelecallerTaskService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SiteVisitController extends Controller
{
    protected $notificationService;
    protected $asmCnpAutomationService;

    public function __construct(NotificationService $notificationService, AsmCnpAutomationService $asmCnpAutomationService)
    {
        $this->notificationService = $notificationService;
        $this->asmCnpAutomationService = $asmCnpAutomationService;
    }

    private function getRuntimeBudgetRangeOptions(): array
    {
        static $options = null;

        if ($options !== null) {
            return $options;
        }

        $column = DB::selectOne("SHOW COLUMNS FROM site_visits LIKE 'budget_range'");
        if (!$column || empty($column->Type)) {
            return $options = [];
        }

        preg_match_all("/'([^']*)'/", $column->Type, $matches);
        return $options = $matches[1] ?? [];
    }

    private function simplifyBudgetRangeLabel(string $value): string
    {
        $value = str_replace(['Ã¢â‚¬â€œ', 'â€“', '–', '—', '-'], ' ', $value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
        return trim(strtolower((string) $value));
    }

    private function normalizeBudgetRangeForStorage(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $aliases = [
            'Below 50 Lacs' => 'Under 50 Lac',
            '75 Lacs-1 Cr' => '50 Lac 1 Cr',
            'Above 1 Cr' => '1 Cr 2 Cr',
            'N.A' => 'Under 50 Lac',
        ];

        $comparisonValue = $aliases[$normalized] ?? $normalized;
        $comparisonValue = $this->simplifyBudgetRangeLabel($comparisonValue);

        foreach ($this->getRuntimeBudgetRangeOptions() as $option) {
            if ($this->simplifyBudgetRangeLabel($option) === $comparisonValue) {
                return $option;
            }
        }

        return $normalized;
    }

    private function normalizeBudgetRangeValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $map = [
            'Below 50 Lacs' => 'Under 50 Lac',
            'Under 50 Lac' => 'Under 50 Lac',
            '50 Lac - 1 Cr' => '50 Lac â€“ 1 Cr',
            '50 Lac – 1 Cr' => '50 Lac â€“ 1 Cr',
            '50 Lac â€“ 1 Cr' => '50 Lac â€“ 1 Cr',
            '75 Lacs-1 Cr' => '50 Lac â€“ 1 Cr',
            '1 Cr - 2 Cr' => '1 Cr â€“ 2 Cr',
            '1 Cr – 2 Cr' => '1 Cr â€“ 2 Cr',
            '1 Cr â€“ 2 Cr' => '1 Cr â€“ 2 Cr',
            '2 Cr - 3 Cr' => '2 Cr â€“ 3 Cr',
            '2 Cr – 3 Cr' => '2 Cr â€“ 3 Cr',
            '2 Cr â€“ 3 Cr' => '2 Cr â€“ 3 Cr',
            'Above 1 Cr' => '1 Cr â€“ 2 Cr',
            'Above 3 Cr' => 'Above 3 Cr',
            'N.A' => 'Under 50 Lac',
        ];

        return $map[$normalized] ?? $normalized;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        $query = SiteVisit::with(['lead', 'creator', 'assignedTo']);

        // Role-based filtering
        if ($user->isAdmin() || $user->isCrm()) {
            // Admin and CRM can see all site visits
            // No additional filtering needed
        } elseif ($user->isSalesHead()) {
            // Sales Head can see all site visits from their entire team hierarchy
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->where(function($q) use ($allTeamMemberIds, $user) {
                    $q->whereIn('created_by', $allTeamMemberIds)
                      ->orWhere('created_by', $user->id)
                      ->orWhereIn('assigned_to', $allTeamMemberIds)
                      ->orWhere('assigned_to', $user->id);
                });
            } else {
                // No team members, show only their own
                $query->where('created_by', $user->id);
            }
            $query->where('is_dead', false);
        } elseif ($user->isSalesManager()) {
            // Senior Manager sees site visits from their direct team members
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->where(function($q) use ($teamMemberIds, $user) {
                $q->where('created_by', $user->id);
                if ($teamMemberIds->isNotEmpty()) {
                    $q->orWhereIn('created_by', $teamMemberIds)
                      ->orWhereIn('assigned_to', $teamMemberIds);
                }
            })->where('is_dead', false);
        } elseif ($user->isSalesExecutive() || $user->isAssistantSalesManager()) {
            $query->where('assigned_to', $user->id);
        } else {
            // Other roles - return empty
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'last_page' => 1
            ]);
        }

        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('verification_status') && $request->verification_status && $request->verification_status !== 'all') {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->has('closer_status') && $request->closer_status && $request->closer_status !== 'all') {
            $query->where('closer_status', $request->closer_status);
        }

        if ($request->has('closing_verification_status') && $request->closing_verification_status && $request->closing_verification_status !== 'all') {
            $query->where('closing_verification_status', $request->closing_verification_status);
        }

        if ($request->boolean('closed_pipeline')) {
            $query->where(function ($closedQuery) {
                $closedQuery->whereNotNull('closing_verification_status')
                    ->orWhereNotNull('closer_status')
                    ->orWhereHas('lead', function ($leadQuery) {
                        $leadQuery->where('status', 'closed');
                    });
            });
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('property_name', 'like', "%{$search}%")
                  ->orWhereHas('lead', function($leadQuery) use ($search) {
                      $leadQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Date filter
        if ($request->has('date_filter') && $request->date_filter) {
            $dateFilter = $request->date_filter;
            $today = now()->startOfDay();
            
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('scheduled_at', $today);
                    break;
                case 'this_week':
                    $query->whereBetween('scheduled_at', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereBetween('scheduled_at', [
                        $today->copy()->startOfMonth(),
                        $today->copy()->endOfMonth()
                    ]);
                    break;
                case 'this_year':
                    $query->whereBetween('scheduled_at', [
                        $today->copy()->startOfYear(),
                        $today->copy()->endOfYear()
                    ]);
                    break;
                case 'custom':
                    if ($request->has('date_from') && $request->has('date_to')) {
                        $query->whereBetween('scheduled_at', [
                            $request->date_from . ' 00:00:00',
                            $request->date_to . ' 23:59:59'
                        ]);
                    }
                    break;
            }
        }

        $visits = $query->latest('scheduled_at')->paginate($request->get('per_page', 15));

        return response()->json($visits);
    }

    public function requestClose(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isSalesManager() && !$user->isAssistantSalesManager()) {
            return response()->json(['success' => false, 'message' => 'Only ASM users can mark a visit as closed.'], 403);
        }

        if ($siteVisit->verification_status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Only verified site visits can be marked as closed.',
            ], 422);
        }

        if ($siteVisit->closing_verification_status === 'pending' || $siteVisit->closing_verification_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Closing request already exists for this site visit.',
            ], 422);
        }

        try {
            $siteVisit->closer_status = 'pending';
            $siteVisit->converted_to_closer_at = now();
            $siteVisit->closing_verification_status = 'pending';
            $siteVisit->closing_verified_by = null;
            $siteVisit->closing_verified_at = null;
            $siteVisit->closing_rejection_reason = null;
            $siteVisit->save();

            if ($siteVisit->lead) {
                $siteVisit->lead->update([
                    'status' => 'closed',
                    'status_auto_update_enabled' => false,
                ]);
                $this->asmCnpAutomationService->cancelLeadAutomation($siteVisit->lead, 'Lead moved to closed flow.');
            }

            try {
                $this->notificationService->notifyClosingVerificationPending($siteVisit, $user->id);
            } catch (\Exception $e) {
                Log::warning('Error sending close request notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Close request submitted. Lead moved to Closed section and is awaiting verification.',
                'data' => $siteVisit->fresh(['lead', 'creator', 'assignedTo']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error requesting close: ' . $e->getMessage(), [
                'site_visit_id' => $siteVisit->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark site visit as closed.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->merge([
            'budget_range' => $this->normalizeBudgetRangeForStorage($request->input('budget_range')),
        ]);

        $validator = Validator::make($request->all(), [
            'lead_id' => 'nullable|exists:leads,id',
            'prospect_id' => 'nullable|exists:prospects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'property_name' => 'nullable|string|max:255',
            'property_address' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'visit_notes' => 'nullable|string',
            // New form fields
            'customer_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:16',
            'employee' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'date_of_visit' => 'nullable|date',
            'project' => 'nullable|string|max:255',
            'budget_range' => 'nullable|string|max:255',
            'team_leader' => 'nullable|string|max:255',
            'property_type' => 'nullable|in:Plot/Villa,Flat,Commercial,Just Exploring',
            'payment_mode' => 'nullable|in:Self Fund,Loan',
            'tentative_period' => 'nullable|in:Within 1 Month,Within 3 Months,Within 6 Months,More than 6 Months',
            'lead_type' => 'nullable|in:New Visit,Revisited,Meeting,Prospect',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB each
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        if ($request->filled('budget_range') && !in_array($validated['budget_range'] ?? null, $this->getRuntimeBudgetRangeOptions(), true)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => ['budget_range' => ['The selected budget range is invalid.']],
            ], 422);
        }

        $validated['created_by'] = $request->user()->id;
        $validated['assigned_to'] = $validated['assigned_to'] ?? $request->user()->id;
        $validated['status'] = 'scheduled';
        $validated['verification_status'] = 'pending';

        // Prevent duplicate: same lead must not have another active site visit (scheduled/pending, not completed, not dead)
        if (!empty($validated['lead_id'])) {
            $hasActiveVisit = SiteVisit::where('lead_id', $validated['lead_id'])
                ->where('status', '!=', 'completed')
                ->where(function ($q) {
                    $q->whereNull('is_dead')->orWhere('is_dead', false);
                })
                ->exists();
            if ($hasActiveVisit) {
                return response()->json([
                    'success' => false,
                    'message' => 'This customer already has an active site visit (scheduled or pending). Complete, reschedule, or mark that visit as dead before creating a new one.',
                ], 422);
            }
        }

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photoPaths = [];
            foreach ($request->file('photos') as $photo) {
                $filename = 'site-visits/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $photoPaths[] = $filename;
            }
            $validated['photos'] = $photoPaths;
        }

        $siteVisit = SiteVisit::create($validated);

        // Update lead status based on lead_type
        if (isset($validated['lead_id'])) {
            $lead = Lead::find($validated['lead_id']);
            if ($lead) {
                $leadType = $validated['lead_type'] ?? null;
                if ($leadType === 'Revisited') {
                    $lead->updateStatusIfAllowed('revisited_scheduled');
                } else {
                    // Default to visit_scheduled for 'New Visit' or other types
                    $lead->updateStatusIfAllowed('visit_scheduled');
                }
                $this->asmCnpAutomationService->cancelLeadAutomation($lead, 'Lead moved to site visit flow.');
            }
        }

        // Fire event (wrap in try-catch to handle broadcasting errors)
        try {
            event(new SiteVisitCreated($siteVisit));
        } catch (\Exception $e) {
            // Broadcasting errors (like Pusher) shouldn't stop site visit creation
            // Log but continue - the site visit is successfully created
            \Log::warning("Broadcasting error in SiteVisitController (non-critical): " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Site visit scheduled successfully',
            'data' => $siteVisit->load(['lead', 'creator', 'assignedTo']),
        ], 201);
    }

    public function show(SiteVisit $siteVisit)
    {
        $user = request()->user();

        // Check access
        if (!$this->canAccessSiteVisit($user, $siteVisit)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $siteVisit->load(['lead', 'creator', 'assignedTo']);

        return response()->json($siteVisit);
    }

    public function update(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$this->canAccessSiteVisit($user, $siteVisit)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'property_name' => 'sometimes|string|max:255',
            'property_address' => 'nullable|string',
            'scheduled_at' => 'sometimes|date',
            'completed_at' => 'nullable|date',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,rescheduled',
            'visit_notes' => 'nullable|string',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $siteVisit->update($validated);

        // Update lead status if visit completed
        if (isset($validated['status']) && $validated['status'] === 'completed') {
            if ($siteVisit->lead) {
                $leadType = $siteVisit->lead_type ?? null;
                if ($leadType === 'Revisited') {
                    $siteVisit->lead->updateStatusIfAllowed('revisited_completed');
                } else {
                    $siteVisit->lead->updateStatusIfAllowed('visit_done');
                }
            }
        }

        return response()->json($siteVisit->load(['lead', 'creator', 'assignedTo']));
    }

    /**
     * Mark site visit as completed
     */
    public function complete(Request $request, SiteVisit $siteVisit)
    {
        try {
            $user = $request->user();

            // Check access
            if (!$this->canAccessSiteVisit($user, $siteVisit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            if ($siteVisit->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Site visit already completed',
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'feedback' => 'nullable|string',
                'rating' => 'nullable|integer|min:1|max:5',
                'visit_notes' => 'nullable|string',
                'visited_projects' => 'nullable|string',
                'tentative_closing_time' => 'nullable|in:within_3_days,tomorrow,this_week,this_month,it_will_take_time',
                'proof_photos' => 'required|array|min:1',
                'proof_photos.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // Max 5MB per image
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422)->header('Content-Type', 'application/json');
            }

            // Handle proof photo uploads
            $proofPhotoPaths = [];
            if ($request->hasFile('proof_photos')) {
                foreach ($request->file('proof_photos') as $photo) {
                    $filename = 'site-visits/proof/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $photo->storeAs('public', $filename);
                    $proofPhotoPaths[] = $filename;
                }
            }

            $data = $validator->validated();
            unset($data['proof_photos']); // Remove from update data
            $data['completion_proof_photos'] = $proofPhotoPaths;
            
            $siteVisit->markAsCompleted();
            $siteVisit->update($data);

            // Update lead status based on lead_type
            if ($siteVisit->lead) {
                $leadType = $siteVisit->lead_type ?? null;
                if ($leadType === 'Revisited') {
                    $siteVisit->lead->updateStatusIfAllowed('revisited_completed');
                } else {
                    // Default to visit_done for 'New Visit' or other types
                    $siteVisit->lead->updateStatusIfAllowed('visit_done');
                }
            }

            // Notify only allowed verifiers: seniors of creator, or CRM when creator has no senior. Do not notify Admin.
            try {
                $siteVisit->load('creator');
                $creator = $siteVisit->creator;
                $toNotify = collect();
                if ($creator) {
                    if ($creator->manager_id === null) {
                        $toNotify = User::whereHas('role', fn ($q) => $q->where('slug', 'crm'))
                            ->where('is_active', true)->get();
                    } else {
                        $current = $creator->manager_id;
                        $seen = [];
                        while ($current && !isset($seen[$current])) {
                            $seen[$current] = true;
                            $manager = User::find($current);
                            if ($manager && $manager->is_active) {
                                $toNotify->push($manager);
                            }
                            $current = $manager ? $manager->manager_id : null;
                        }
                    }
                }
                $actionUrl = url('/crm/verifications');
                $customerName = $siteVisit->customer_name ?? ($siteVisit->lead ? $siteVisit->lead->name : 'Customer');
                foreach ($toNotify as $verificationUser) {
                    $this->notificationService->notifyNewVerification(
                        $verificationUser,
                        'site_visit',
                        'New Site Visit Verification',
                        "Site visit for '{$customerName}' requires verification",
                        $actionUrl,
                        [
                            'site_visit_id' => $siteVisit->id,
                            'customer_name' => $customerName,
                        ]
                    );
                }
            } catch (\Exception $e) {
                Log::error('Error sending site visit verification notifications: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Site visit completed with proof photos. Awaiting verification.',
                'data' => $siteVisit->fresh(['lead', 'creator', 'assignedTo']),
            ])->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Error completing site visit: ' . $e->getMessage(), [
                'site_visit_id' => $siteVisit->id ?? null,
                'user_id' => $request->user()?->id,
                'error' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing the site visit. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Reschedule a site visit
     */
    public function reschedule(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        // Check access
        if (!$this->canAccessSiteVisit($user, $siteVisit)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Can only reschedule scheduled site visits
        if ($siteVisit->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Can only reschedule site visits with status "scheduled"',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update site visit with new scheduled time
        $oldScheduledAt = $siteVisit->scheduled_at;
        $siteVisit->scheduled_at = $request->scheduled_at;
        $siteVisit->status = 'scheduled'; // Keep as scheduled
        $siteVisit->is_rescheduled = true;
        $siteVisit->reschedule_count = ($siteVisit->reschedule_count ?? 0) + 1;
        $siteVisit->rescheduled_at = now();
        $siteVisit->rescheduled_by = $user->id;
        $siteVisit->reschedule_reason = $request->reason;
        // Reset verification status to pending (verification required after reschedule)
        $siteVisit->verification_status = 'pending';
        $siteVisit->verified_by = null;
        $siteVisit->verified_at = null;
        $siteVisit->rejection_reason = null;
        $siteVisit->save();

        // Create calling task 30 minutes before new scheduled time
        $taskService = app(TelecallerTaskService::class);
        $taskService->createCallTaskBeforeScheduled($siteVisit, $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Site visit rescheduled successfully. Verification required.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'assignedTo', 'rescheduledBy']),
        ]);
    }

    /**
     * Verify a site visit. Creator's senior verifies; if creator has no senior, CRM verifies. Admin cannot verify.
     */
    public function verify(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Admin cannot verify site visits.'], 403);
        }

        $siteVisit->load('creator');
        $creator = $siteVisit->creator;
        if (!$creator) {
            return response()->json(['message' => 'Site visit creator not found'], 404);
        }

        if (!$user->isAdmin() && !$user->isCrm()) {
            if (!$user->isSeniorOf($creator)) {
                return response()->json([
                    'message' => 'Forbidden. Only Admin, CRM, or a senior of the visit creator can verify this site visit.',
                ], 403);
            }
        }

        if ($siteVisit->verification_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Site visit already verified',
            ], 422);
        }

        if ($siteVisit->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Site visit must be completed before verification',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'lead_status' => 'nullable|in:hot,warm,cold,junk',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notes = $request->input('notes');
        $leadStatus = $request->input('lead_status');
        $siteVisit->verify($user->id, $notes, $leadStatus);

        // Check if this site visit is eligible for Telecaller incentive
        // Load lead with prospects to check telecaller_id
        $siteVisit->load(['lead.prospects']);
        
        if ($siteVisit->lead) {
            $lead = $siteVisit->lead;
            
            // Check if lead has a prospect with telecaller_id
            $prospect = $lead->prospects()
                ->whereNotNull('telecaller_id')
                ->latest('created_at')
                ->first();
            
            if ($prospect && $prospect->telecaller_id) {
                try {
                    $telecaller = User::find($prospect->telecaller_id);
                    
                    if ($telecaller && $telecaller->isTelecaller()) {
                        // Check if incentive already requested for this site visit
                        $existingIncentive = \App\Models\Incentive::where('site_visit_id', $siteVisit->id)
                            ->where('type', 'site_visit')
                            ->where('user_id', $telecaller->id)
                            ->first();
                        
                        if (!$existingIncentive) {
                            // Send notification to Telecaller
                            $actionUrl = url('/telecaller/notifications');
                            $this->notificationService->notifyEligibleSiteVisitForIncentive(
                                $telecaller,
                                $siteVisit,
                                $actionUrl
                            );
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail verification
                    Log::warning('Error notifying telecaller about eligible site visit: ' . $e->getMessage(), [
                        'site_visit_id' => $siteVisit->id,
                        'prospect_id' => $prospect->id ?? null,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Site visit verified successfully. This counts as a Site Visit achievement.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'verifiedBy']),
        ]);
    }

    /**
     * Reject a site visit. Admin and CRM can reject any pending visit; seniors keep existing access.
     */
    public function reject(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        $siteVisit->load('creator');
        $creator = $siteVisit->creator;
        if (!$creator) {
            return response()->json(['message' => 'Site visit creator not found'], 404);
        }

        if (!$user->isAdmin() && !$user->isCrm()) {
            if (!$user->isSeniorOf($creator)) {
                return response()->json([
                    'message' => 'Forbidden. Only Admin, CRM, or a senior of the visit creator can reject this site visit.',
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $siteVisit->reject($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Site visit rejected',
            'data' => $siteVisit->fresh(['lead', 'creator', 'verifiedBy']),
        ]);
    }

    /**
     * Convert verified site visit to closer (deprecated - use requestCloser)
     */
    public function convertToCloser(Request $request, SiteVisit $siteVisit)
    {
        // Redirect to requestCloser for backward compatibility
        return $this->requestCloser($request, $siteVisit);
    }

    /**
     * Verify closer (Sales Head only)
     */
    public function verifyCloser(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isSalesHead() && !$user->isCrm() && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Only Sales Head, CRM, or Admin can verify closers.'], 403);
        }

        if ($siteVisit->closer_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Closer already verified',
            ], 422);
        }

        if ($siteVisit->closer_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Closer must be pending before verification',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'adjusted_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notes = $request->input('notes');
        $adjustedAmount = $request->input('adjusted_amount');
        
        // Update incentive amount in site visit
        $siteVisit->incentive_amount = $adjustedAmount;
        $siteVisit->save();
        
        // Update incentive record if exists
        $incentive = \App\Models\Incentive::where('site_visit_id', $siteVisit->id)
            ->where('type', 'closer')
            ->first();
        
        if ($incentive) {
            $incentive->amount = $adjustedAmount;
            $incentive->sales_head_verified_by = $user->id;
            $incentive->sales_head_verified_at = now();
            $incentive->status = 'verified';
            $incentive->save();
        }
        
        // Verify closer (without lead_status, as it's not needed for closer verification)
        $siteVisit->verifyCloser($user->id, $notes, null);

        return response()->json([
            'success' => true,
            'message' => 'Closer verified successfully with adjusted incentive amount. This counts as a Closer achievement.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'closerVerifiedBy']),
        ]);
    }

    /**
     * Reject closer (Sales Head only)
     */
    public function rejectCloser(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isSalesHead() && !$user->isCrm() && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Only Sales Head, CRM, or Admin can reject closers.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($siteVisit->closer_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Closer must be pending before rejection',
            ], 422);
        }

        $siteVisit->rejectCloser($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Closer rejected',
            'data' => $siteVisit->fresh(['lead', 'creator', 'closerVerifiedBy']),
        ]);
    }

    /**
     * Verify closing (CRM/Admin)
     */
    public function verifyClosing(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isCrm() && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Only CRM or Admin can verify closing.'], 403);
        }

        if ($siteVisit->closing_verification_status === 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Closing already verified',
            ], 422);
        }

        if ($siteVisit->closing_verification_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Closing must be pending before verification',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notes = $request->input('notes');
        
        // Verify closing - this will also set closer_status to verified
        $siteVisit->verifyClosing($user->id, $notes);

        // Send notification to user who requested closing
        try {
            $this->notificationService->notifyClosingVerified($siteVisit, $user->id);
        } catch (\Exception $e) {
            Log::warning('Error sending closing verification notification: ' . $e->getMessage());
        }

        // Send notification to CRM about pending closing verification (when request is made)
        // This is handled in requestCloser method

        return response()->json([
            'success' => true,
            'message' => 'Closing verified successfully. KYC can now be submitted from the Closed section.',
            'data' => $siteVisit->fresh(['lead', 'creator', 'closingVerifiedBy']),
        ]);
    }

    /**
     * Reject closing (CRM/Admin only)
     */
    public function rejectClosing(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        if (!$user->isCrm() && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Only CRM or Admin can reject closing.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($siteVisit->closing_verification_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Closing must be pending before rejection',
            ], 422);
        }

        $siteVisit->rejectClosing($user->id, $request->reason);

        // Send notification to user who requested closing
        try {
            $this->notificationService->notifyClosingRejected($siteVisit, $user->id, $request->reason);
        } catch (\Exception $e) {
            Log::warning('Error sending closing rejection notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Closing rejected',
            'data' => $siteVisit->fresh(['lead', 'creator', 'closingVerifiedBy']),
        ]);
    }

    /**
     * Submit KYC after close request verification
     */
    public function submitKyc(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        // Check access - Only Senior Managers and Assistant Sales Managers can submit KYC
        if (!$user->isSalesManager() && !$user->isAssistantSalesManager()) {
            return response()->json(['message' => 'Only Senior Managers and Assistant Sales Managers can submit KYC.'], 403);
        }
        
        if ($user->isSalesManager() && $siteVisit->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($siteVisit->closing_verification_status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Close request must be verified before submitting KYC.',
            ], 422);
        }

        if (!empty($siteVisit->kyc_documents)) {
            return response()->json([
                'success' => false,
                'message' => 'KYC already submitted for this closed lead.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'nominee_name' => 'required|string|max:255',
            'second_customer_name' => 'nullable|string|max:255',
            'customer_dob' => 'required|date',
            'pan_card' => 'required|string|max:20',
            'aadhaar_card_no' => 'required|string|max:20',
            'kyc_documents' => 'required|array|min:1',
            'kyc_documents.*' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
            'proof_photos' => 'required|array|min:1',
            'proof_photos.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Handle KYC document uploads
        $kycDocumentPaths = [];
        if ($request->hasFile('kyc_documents')) {
            foreach ($request->file('kyc_documents') as $document) {
                $filename = 'closings/kyc/' . time() . '_' . uniqid() . '.' . $document->getClientOriginalExtension();
                $document->storeAs('public', $filename);
                $kycDocumentPaths[] = $filename;
            }
        }

        // Handle proof photo uploads
        $proofPhotoPaths = [];
        if ($request->hasFile('proof_photos')) {
            foreach ($request->file('proof_photos') as $photo) {
                $filename = 'site-visits/closer-proof/' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('public', $filename);
                $proofPhotoPaths[] = $filename;
            }
        }

        try {
            $siteVisit->customer_name = $request->input('customer_name');
            $siteVisit->nominee_name = $request->input('nominee_name');
            $siteVisit->second_customer_name = $request->input('second_customer_name');
            $siteVisit->customer_dob = $request->input('customer_dob');
            $siteVisit->pan_card = $request->input('pan_card');
            $siteVisit->aadhaar_card_no = $request->input('aadhaar_card_no');
            $siteVisit->kyc_documents = $kycDocumentPaths;
            $siteVisit->closer_request_proof_photos = $proofPhotoPaths;
            $siteVisit->save();

            return response()->json([
                'success' => true,
                'message' => 'KYC submitted successfully. You can now submit incentive request from Closed section.',
                'data' => $siteVisit->fresh(['lead', 'creator', 'assignedTo']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error submitting KYC: ' . $e->getMessage(), [
                'site_visit_id' => $siteVisit->id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark site visit as dead
     */
    public function requestCloser(Request $request, SiteVisit $siteVisit)
    {
        return $this->submitKyc($request, $siteVisit);
    }

    /**
     * Mark site visit as dead
     */
    public function markDead(Request $request, SiteVisit $siteVisit)
    {
        $user = $request->user();

        // Check access
        if ($user->isSalesManager() && $siteVisit->created_by !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $siteVisit->markAsDead($user->id, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Site visit marked as dead successfully',
            'data' => $siteVisit->fresh(['lead', 'creator', 'markedDeadBy']),
        ]);
    }

    private function canAccessSiteVisit($user, SiteVisit $siteVisit): bool
    {
        if ($user->canViewAllLeads()) {
            return true;
        }

        if ($user->isSalesManager()) {
            return $siteVisit->created_by === $user->id;
        }

        return $siteVisit->assigned_to === $user->id || $siteVisit->created_by === $user->id;
    }
}
