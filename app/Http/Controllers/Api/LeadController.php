<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\LeadAssigned;
use App\Events\LeadStatusUpdated;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\User;
use App\Models\Task;
use App\Models\TelecallerTask;
use App\Services\TelecallerTaskService;
use App\Services\LeadTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Lead::with(['creator', 'activeAssignments.assignedTo']);

        // Role-based filtering
        if ($user->isSalesExecutive()) {
            $query->whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        } elseif ($user->isSalesManager() || $user->isSeniorManager() || $user->isAssistantSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $managerAndTeamIds = $teamMemberIds->merge([$user->id])->unique()->values();

            // Show leads: (1) assigned to this manager or their team, OR (2) from team's verified prospects
            $query->where(function ($q) use ($managerAndTeamIds, $teamMemberIds, $user) {
                $q->whereHas('activeAssignments', function ($aq) use ($managerAndTeamIds) {
                    $aq->whereIn('assigned_to', $managerAndTeamIds);
                });
                if ($teamMemberIds->isNotEmpty()) {
                    $q->orWhereHas('prospects', function ($subQ) use ($teamMemberIds, $user) {
                        $subQ->whereIn('telecaller_id', $teamMemberIds)
                             ->whereIn('verification_status', ['verified', 'approved'])
                             ->where('verified_by', $user->id);
                    });
                }
            });
        }

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by assigned user
        if ($request->has('assigned_to')) {
            $assignedToId = $request->assigned_to;
            $query->whereHas('activeAssignments', function ($q) use ($assignedToId) {
                $q->where('assigned_to', $assignedToId);
            });
        }

        if ($request->boolean('fresh_today')) {
            $query->whereHas('assignments', function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->whereDate('created_at', today());
            });
        }

        // Increase default per_page to show all leads (was 15, now 50)
        $perPage = $request->get('per_page', 50);
        $leads = $query->latest()->paginate($perPage);

        return response()->json($leads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'source' => 'nullable|in:' . implode(',', array_keys(Lead::sourceOptions())),
            'property_type' => 'nullable|in:apartment,villa,plot,commercial,other',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'requirements' => 'nullable|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'new';
        $validated['source'] = Lead::normalizeSource($validated['source'] ?? null);

        $lead = Lead::create($validated);

        // Assign lead if provided
        if ($request->has('assigned_to')) {
            $this->assignLead($lead, $request->assigned_to, $request->user()->id);
        }

        return response()->json($lead->load(['creator', 'activeAssignments.assignedTo']), 201);
    }

    public function show(Lead $lead)
    {
        $user = request()->user();

        // Check access
        if (!$this->canAccessLead($user, $lead)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $lead->load([
            'creator',
            'assignments.assignedTo',
            'assignments.assignedBy',
            'siteVisits.assignedTo',
            'followUps.creator',
        ]);

        return response()->json($lead);
    }

    public function update(Request $request, Lead $lead)
    {
        $user = $request->user();

        if (!$this->canAccessLead($user, $lead)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'source' => 'nullable|in:' . implode(',', array_keys(Lead::sourceOptions())),
            'status' => 'sometimes|in:new,connected,verified_prospect,meeting_scheduled,meeting_completed,visit_scheduled,visit_done,revisited_scheduled,revisited_completed,closed,dead,junk,on_hold',
            'property_type' => 'nullable|in:apartment,villa,plot,commercial,other',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'requirements' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $lead->status;
        
        // Handle smart override logic for manual status changes
        if (isset($validated['status']) && $oldStatus !== $validated['status']) {
            $newStatus = $validated['status'];
            
            // If manager manually sets to 'dead' or 'closed', disable auto-updates
            if (in_array($newStatus, ['dead', 'closed', 'junk'])) {
                $lead->disableAutoUpdate();
            }
            // If changing from a terminal status to something else, enable auto-updates
            elseif (in_array($oldStatus, ['dead', 'closed', 'junk'])) {
                $lead->enableAutoUpdate();
            }
        }
        
        if (isset($validated['source'])) {
            $validated['source'] = Lead::normalizeSource($validated['source']);
        }

        $lead->update($validated);

        // Fire event if status changed
        if (isset($validated['status']) && $oldStatus !== $validated['status']) {
            event(new LeadStatusUpdated($lead, $oldStatus, $validated['status']));
        }

        return response()->json($lead->load(['creator', 'activeAssignments.assignedTo']));
    }

    public function assign(Request $request, Lead $lead)
    {
        $user = $request->user();

        if (!$user->canAssignLeads()) {
            return response()->json(['message' => 'Forbidden. You cannot assign leads.'], 403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'create_calling_task' => 'nullable|boolean',
            'transfer_existing_tasks' => 'nullable|boolean',
        ]);

        $result = $this->assignLead(
            $lead,
            $validated['assigned_to'],
            $user->id,
            $validated['notes'] ?? null,
            (bool) ($validated['create_calling_task'] ?? true),
            (bool) ($validated['transfer_existing_tasks'] ?? true)
        );

        return response()->json([
            'message' => 'Lead assigned successfully',
            'data' => $result,
        ]);
    }

    /**
     * Bulk assign leads to a user (single or multiple selection).
     */
    public function bulkAssign(Request $request)
    {
        $user = $request->user();

        if (!$user->canAssignLeads()) {
            return response()->json(['message' => 'Forbidden. You cannot assign leads.'], 403);
        }

        $validated = $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'required|integer|exists:leads,id',
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'create_calling_task' => 'nullable|boolean',
            'transfer_existing_tasks' => 'nullable|boolean',
        ]);

        $leadIds = array_values(array_unique($validated['lead_ids']));
        $assignedTo = (int) $validated['assigned_to'];
        $notes = $validated['notes'] ?? null;
        $createCallingTask = (bool) ($validated['create_calling_task'] ?? true);
        $transferExistingTasks = (bool) ($validated['transfer_existing_tasks'] ?? true);

        $transferred = 0;
        $failed = 0;
        $errors = [];

        foreach ($leadIds as $leadId) {
            try {
                $lead = Lead::find($leadId);
                if (!$lead) {
                    $failed++;
                    $errors[] = ['lead_id' => $leadId, 'error' => 'Lead not found'];
                    continue;
                }
                $this->assignLead($lead, $assignedTo, $user->id, $notes, $createCallingTask, $transferExistingTasks);
                $transferred++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = ['lead_id' => $leadId, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => true,
            'transferred' => $transferred,
            'failed' => $failed,
            'message' => $transferred > 0
                ? "{$transferred} lead(s) transferred successfully." . ($failed > 0 ? " {$failed} failed." : '')
                : 'No leads were transferred.',
            'errors' => $errors,
        ]);
    }

    /**
     * Transfer all leads assigned to a given user to another user (one-click).
     */
    public function transferAllFromUser(Request $request)
    {
        $user = $request->user();

        if (!$user->canAssignLeads()) {
            return response()->json(['message' => 'Forbidden. You cannot assign leads.'], 403);
        }

        $validated = $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
            'create_calling_task' => 'nullable|boolean',
            'transfer_existing_tasks' => 'nullable|boolean',
        ]);

        $fromUserId = (int) $validated['from_user_id'];
        $assignedTo = (int) $validated['assigned_to'];
        $notes = $validated['notes'] ?? null;
        $createCallingTask = (bool) ($validated['create_calling_task'] ?? true);
        $transferExistingTasks = (bool) ($validated['transfer_existing_tasks'] ?? true);

        $leadIds = Lead::whereHas('activeAssignments', function ($q) use ($fromUserId) {
            $q->where('assigned_to', $fromUserId);
        })->pluck('id')->take(1000)->all();

        $transferred = 0;
        $failed = 0;
        $errors = [];

        foreach ($leadIds as $leadId) {
            try {
                $lead = Lead::find($leadId);
                if (!$lead) {
                    $failed++;
                    continue;
                }
                $this->assignLead($lead, $assignedTo, $user->id, $notes, $createCallingTask, $transferExistingTasks);
                $transferred++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = ['lead_id' => $leadId, 'error' => $e->getMessage()];
            }
        }

        $message = $transferred > 0
            ? "{$transferred} lead(s) transferred successfully." . ($failed > 0 ? " {$failed} failed." : '')
            : 'No leads were transferred.';

        if (count($leadIds) >= 1000) {
            $message .= ' Capped at 1000 leads; more may exist for this user.';
        }

        return response()->json([
            'success' => true,
            'transferred' => $transferred,
            'failed' => $failed,
            'message' => $message,
            'errors' => array_slice($errors, 0, 10),
        ]);
    }

    private function assignLead(
        Lead $lead,
        int $assignedTo,
        int $assignedBy,
        ?string $notes = null,
        bool $createCallingTask = true,
        bool $transferExistingTasks = true
    ): array
    {
        return DB::transaction(function () use ($lead, $assignedTo, $assignedBy, $notes, $createCallingTask, $transferExistingTasks) {
            $oldAssignments = $lead->assignments()
                ->where('is_active', true)
                ->get(['assigned_to']);

            $oldOwnerIds = $oldAssignments
                ->pluck('assigned_to')
                ->filter(fn ($id) => (int) $id !== (int) $assignedTo)
                ->unique()
                ->values();

            // Deactivate existing assignments
            $lead->assignments()->where('is_active', true)->update([
                'is_active' => false,
                'unassigned_at' => now(),
            ]);

            // Create new assignment
            LeadAssignment::create([
                'lead_id' => $lead->id,
                'assigned_to' => $assignedTo,
                'assigned_by' => $assignedBy,
                'assignment_type' => 'primary',
                'notes' => $notes,
                'assigned_at' => now(),
                'is_active' => true,
            ]);

            $transferredTaskCounts = [
                'telecaller_tasks' => 0,
                'manager_tasks' => 0,
            ];

            if ($transferExistingTasks && $oldOwnerIds->isNotEmpty()) {
                $transferredTaskCounts['telecaller_tasks'] = TelecallerTask::where('lead_id', $lead->id)
                    ->whereIn('assigned_to', $oldOwnerIds)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update(['assigned_to' => $assignedTo]);

                $transferredTaskCounts['manager_tasks'] = Task::where('lead_id', $lead->id)
                    ->whereIn('assigned_to', $oldOwnerIds)
                    ->where('type', 'phone_call')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update(['assigned_to' => $assignedTo]);
            }

            $createdNewTask = false;
            if ($createCallingTask) {
                $beforeTelecallerCount = TelecallerTask::where('lead_id', $lead->id)
                    ->where('assigned_to', $assignedTo)
                    ->where('task_type', 'calling')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count();

                $beforeManagerCount = Task::where('lead_id', $lead->id)
                    ->where('assigned_to', $assignedTo)
                    ->where('type', 'phone_call')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count();

                event(new LeadAssigned($lead, $assignedTo, $assignedBy));

                $afterTelecallerCount = TelecallerTask::where('lead_id', $lead->id)
                    ->where('assigned_to', $assignedTo)
                    ->where('task_type', 'calling')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count();

                $afterManagerCount = Task::where('lead_id', $lead->id)
                    ->where('assigned_to', $assignedTo)
                    ->where('type', 'phone_call')
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count();

                $createdNewTask = $afterTelecallerCount > $beforeTelecallerCount || $afterManagerCount > $beforeManagerCount;

                // Fallback: create task if no open task exists after event processing.
                if (!$createdNewTask && $afterTelecallerCount === 0 && $afterManagerCount === 0) {
                    $assignee = User::with('role')->find($assignedTo);
                    if ($assignee) {
                        if ($assignee->isSalesExecutive()) {
                            app(TelecallerTaskService::class)->createCallingTask($lead, $assignee, $assignedBy);
                            $createdNewTask = true;
                        } elseif ($assignee->isSalesManager() || $assignee->isAssistantSalesManager()) {
                            Task::create([
                                'lead_id' => $lead->id,
                                'assigned_to' => $assignedTo,
                                'type' => 'phone_call',
                                'title' => "Call lead: {$lead->name}",
                                'description' => "Phone call task for lead: {$lead->name} ({$lead->phone})",
                                'status' => 'pending',
                                'scheduled_at' => now()->addMinutes(10),
                                'created_by' => $assignedBy,
                            ]);
                            $createdNewTask = true;
                        }
                    }
                }
            }

            return [
                'lead_id' => $lead->id,
                'old_owner_ids' => $oldOwnerIds->values()->all(),
                'new_owner_id' => $assignedTo,
                'transfer_existing_tasks' => $transferExistingTasks,
                'create_calling_task' => $createCallingTask,
                'transferred_task_counts' => $transferredTaskCounts,
                'created_new_task' => $createdNewTask,
            ];
        });
    }

    /**
     * Get leads pending verification
     */
    public function pendingVerifications(Request $request)
    {
        $user = $request->user();

        // Only Admin or CRM can view pending verifications
        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $leads = Lead::where('needs_verification', true)
            ->with(['verificationRequestedBy', 'pendingManager', 'activeAssignments.assignedTo'])
            ->latest('verification_requested_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($leads);
    }

    /**
     * Verify and transfer lead to new manager
     */
    public function verifyLead(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Only Admin or CRM can verify leads
        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$lead->needs_verification) {
            return response()->json(['message' => 'Lead does not need verification'], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $leadTransferService = app(LeadTransferService::class);
        $success = $leadTransferService->verifyAndTransferLead($lead, $user->id, $validated['notes'] ?? null);

        if ($success) {
            return response()->json([
                'message' => 'Lead verified and transferred successfully',
                'lead' => $lead->fresh()->load(['verifiedBy', 'activeAssignments.assignedTo'])
            ]);
        }

        return response()->json(['message' => 'Failed to verify and transfer lead'], 500);
    }

    /**
     * Reject verification and keep lead with current assignment
     */
    public function rejectVerification(Request $request, Lead $lead)
    {
        $user = $request->user();

        // Only Admin or CRM can reject verifications
        if (!$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (!$lead->needs_verification) {
            return response()->json(['message' => 'Lead does not need verification'], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $leadTransferService = app(LeadTransferService::class);
        $success = $leadTransferService->rejectVerification($lead, $user->id, $validated['notes'] ?? null);

        if ($success) {
            return response()->json([
                'message' => 'Verification rejected, lead kept with current assignment',
                'lead' => $lead->fresh()->load(['verifiedBy', 'activeAssignments.assignedTo'])
            ]);
        }

        return response()->json(['message' => 'Failed to reject verification'], 500);
    }

    private function canAccessLead($user, Lead $lead): bool
    {
        if ($user->canViewAllLeads()) {
            return true;
        }

        // Check if lead is directly assigned to user
        if ($lead->activeAssignments()->where('assigned_to', $user->id)->exists()) {
            return true;
        }

        // Senior Manager, Manager, Assistant Sales Manager: team's leads
        if ($user->isSalesManager() || $user->isSeniorManager() || $user->isAssistantSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            if ($teamMemberIds->isNotEmpty() && $lead->activeAssignments()->whereIn('assigned_to', $teamMemberIds)->where('is_active', true)->exists()) {
                return true;
            }
            if ($teamMemberIds->isNotEmpty()) {
                return $lead->prospects()
                    ->whereIn('telecaller_id', $teamMemberIds)
                    ->whereIn('verification_status', ['verified', 'approved'])
                    ->exists();
            }
        }

        return false;
    }
}
