<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Models\User;
use App\Models\Role;
use App\Services\TargetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TargetController extends Controller
{
    protected $targetService;

    protected function getTargetsRouteBase(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? '';
        if (str_starts_with($routeName, 'crm.targets.')) {
            return 'crm.targets';
        }
        if (str_starts_with($routeName, 'admin.targets.')) {
            return 'admin.targets';
        }
        return $request->user() && $request->user()->isCrm() ? 'crm.targets' : 'admin.targets';
    }

    public function __construct(TargetService $targetService)
    {
        $this->targetService = $targetService;
        
        // Only allow admin, CRM, and Sales Head users
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (!$user || (!$user->isAdmin() && !$user->isCrm() && !$user->isSalesHead())) {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of targets
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->get('month', now()->format('Y-m'));
        
        $targetsQuery = Target::whereYear('target_month', Carbon::parse($month . '-01')->year)
            ->whereMonth('target_month', Carbon::parse($month . '-01')->month)
            ->with('user.role');
        
        // If Sales Head, filter targets for their team members only
        if ($user->isSalesHead()) {
            $teamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($teamMemberIds)) {
                $targetsQuery->whereIn('user_id', $teamMemberIds);
            } else {
                $targetsQuery->whereRaw('1 = 0'); // No team members, show empty
            }
        }
        
        $targets = $targetsQuery->orderBy('target_month', 'desc')
            ->orderBy('user_id')
            ->get();

        // Get users for filter based on role
        if ($user->isSalesHead()) {
            // Sales Head can VIEW targets for Assistant Senior Manager, Senior Manager, and Sales Executive (but only SET for Assistant Senior Manager and Senior Manager)
            $users = User::where('is_active', true)
                ->whereHas('role', function($q) {
                    $q->whereIn('slug', [Role::SALES_MANAGER, Role::ASSISTANT_SALES_MANAGER, Role::SALES_EXECUTIVE]);
                })
                ->orderBy('name')
                ->get();
        } else {
            // Admin/CRM can see all Sales Executives, Assistant Senior Managers, and Senior Managers
            $users = User::where('is_active', true)
                ->whereHas('role', function($q) {
                    $q->whereIn('slug', [Role::SALES_EXECUTIVE, Role::ASSISTANT_SALES_MANAGER, Role::SALES_MANAGER]);
                })
                ->orderBy('name')
                ->get();
        }

        return view('admin.targets.index', compact('targets', 'users', 'month'));
    }

    /**
     * Show the form for creating a new target
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $month = $request->get('month', now()->format('Y-m'));
        $userId = $request->get('user_id');

        // Get users based on role
        if ($user->isSalesHead()) {
            // Sales Head can set targets for Assistant Senior Manager and Senior Manager only
            $users = User::where('is_active', true)
                ->whereHas('role', function($q) {
                    $q->whereIn('slug', [Role::SALES_MANAGER, Role::ASSISTANT_SALES_MANAGER]);
                })
                ->orderBy('name')
                ->get();
        } else {
            // Admin/CRM can set targets for Sales Executives, Assistant Senior Managers, and Senior Managers
            $users = User::where('is_active', true)
                ->whereHas('role', function($q) {
                    $q->whereIn('slug', [Role::SALES_EXECUTIVE, Role::ASSISTANT_SALES_MANAGER, Role::SALES_MANAGER]);
                })
                ->orderBy('name')
                ->get();
        }

        // If user_id is provided, get existing target for that user
        $existingTarget = null;
        if ($userId) {
            $targetMonth = Carbon::parse($month . '-01')->startOfMonth();
            $existingTarget = Target::where('user_id', $userId)
                ->where('target_month', $targetMonth)
                ->first();
        }

        return view('admin.targets.create', compact('users', 'month', 'userId', 'existingTarget'));
    }

    /**
     * Store a newly created target
     */
    public function store(Request $request)
    {
        $routeBase = $this->getTargetsRouteBase($request);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|date_format:Y-m',
            'target_prospects_extract' => 'nullable|integer|min:0',
            'target_prospects_verified' => 'nullable|integer|min:0',
            'target_calls' => 'nullable|integer|min:0',
            'target_visits' => 'nullable|integer|min:0',
            'target_meetings' => 'nullable|integer|min:0',
            'target_closers' => 'nullable|integer|min:0',
            'manager_target_calculation_logic' => 'nullable|in:juniors_sum,individual_plus_team',
            'manager_junior_scope' => 'nullable|in:executives_only,executives_and_telecallers',
            'incentive_per_closer' => 'nullable|numeric|min:0',
            'incentive_per_visit' => 'nullable|numeric|min:0',
        ]);

        // Verify user role based on who is setting the target
        $currentUser = $request->user();
        $targetUser = User::findOrFail($validated['user_id']);
        
        if ($currentUser->isSalesHead()) {
            // Sales Head can set targets for Sales Executive, Senior Manager, and Assistant Sales Manager
            if (!$targetUser->isSalesManager() && !$targetUser->isSalesExecutive() && !$targetUser->isAssistantSalesManager()) {
                return back()->withErrors(['user_id' => 'Targets can only be set for Senior Managers, Sales Executives, and Assistant Sales Managers.'])->withInput();
            }
        } else {
            // Admin/CRM can set targets for Telecallers, Sales Executives, Senior Managers, and Assistant Sales Managers
            if (!$targetUser->isTelecaller() && !$targetUser->isSalesExecutive() && !$targetUser->isSalesManager() && !$targetUser->isAssistantSalesManager()) {
                return back()->withErrors(['user_id' => 'Targets can only be set for Telecallers, Sales Executives, Senior Managers, and Assistant Sales Managers.'])->withInput();
            }
        }

        try {
            // For Senior Managers and Assistant Sales Managers, set prospect/call targets to 0 (they don't have those)
            $isManagerRole = $targetUser->isSalesManager() || $targetUser->isAssistantSalesManager();
            
            $target = $this->targetService->setTargetsForUser(
                $validated['user_id'],
                $validated['month'],
                [
                    'target_prospects_extract' => $isManagerRole ? 0 : ($validated['target_prospects_extract'] ?? 0),
                    'target_prospects_verified' => $isManagerRole ? 0 : ($validated['target_prospects_verified'] ?? 0),
                    'target_calls' => $isManagerRole ? 0 : ($validated['target_calls'] ?? 0),
                    'target_visits' => $validated['target_visits'] ?? 0,
                    'target_meetings' => $validated['target_meetings'] ?? 0,
                    'target_closers' => $validated['target_closers'] ?? 0,
                    'manager_target_calculation_logic' => $validated['manager_target_calculation_logic'] ?? null,
                    'manager_junior_scope' => $validated['manager_junior_scope'] ?? null,
                    'incentive_per_closer' => $validated['incentive_per_closer'] ?? null,
                    'incentive_per_visit' => $validated['incentive_per_visit'] ?? null,
                ]
            );

            return redirect()
                ->route($routeBase . '.index', ['month' => $validated['month']])
                ->with('success', "Targets set successfully for {$targetUser->name}");

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to set targets: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing a target
     */
    public function edit(Request $request, $id)
    {
        $user = $request->user();
        $target = Target::with('user.role')->findOrFail($id);
        if (!$target->user) {
            $routeBase = $this->getTargetsRouteBase($request);
            return redirect()
                ->route($routeBase . '.index', ['month' => $target->target_month->format('Y-m')])
                ->withErrors(['error' => 'This target belongs to a deleted/missing user. Please delete the target record.']);
        }

        // Get users based on role
        if ($user->isSalesHead()) {
            // Sales Head can set targets for Assistant Senior Manager and Senior Manager only
            $users = User::where('is_active', true)
                ->whereHas('role', function($q) {
                    $q->whereIn('slug', [Role::SALES_MANAGER, Role::ASSISTANT_SALES_MANAGER]);
                })
                ->orderBy('name')
                ->get();
        } else {
            // Admin/CRM can set targets for Sales Executives, Assistant Senior Managers, and Senior Managers
            $users = User::where('is_active', true)
                ->whereHas('role', function($q) {
                    $q->whereIn('slug', [Role::SALES_EXECUTIVE, Role::ASSISTANT_SALES_MANAGER, Role::SALES_MANAGER]);
                })
                ->orderBy('name')
                ->get();
        }

        $month = $target->target_month->format('Y-m');

        return view('admin.targets.edit', compact('target', 'users', 'month'));
    }

    /**
     * Update the specified target
     */
    public function update(Request $request, $id)
    {
        $target = Target::with('user.role')->findOrFail($id);
        if (!$target->user) {
            $routeBase = $this->getTargetsRouteBase($request);
            return redirect()
                ->route($routeBase . '.index', ['month' => $target->target_month->format('Y-m')])
                ->withErrors(['error' => 'Cannot update this target because the user is missing. Please delete the target record.']);
        }
        $routeBase = $this->getTargetsRouteBase($request);

        $validated = $request->validate([
            'target_prospects_extract' => 'nullable|integer|min:0',
            'target_prospects_verified' => 'nullable|integer|min:0',
            'target_calls' => 'nullable|integer|min:0',
            'target_visits' => 'nullable|integer|min:0',
            'target_meetings' => 'nullable|integer|min:0',
            'target_closers' => 'nullable|integer|min:0',
            'manager_target_calculation_logic' => 'nullable|in:juniors_sum,individual_plus_team',
            'manager_junior_scope' => 'nullable|in:executives_only,executives_and_telecallers',
            'incentive_per_closer' => 'nullable|numeric|min:0',
            'incentive_per_visit' => 'nullable|numeric|min:0',
        ]);

        try {
            // For Senior Managers and Assistant Sales Managers, set prospect/call targets to 0
            $isManagerRole = $target->user->isSalesManager() || $target->user->isAssistantSalesManager();
            
            $target->update([
                'target_prospects_extract' => $isManagerRole ? 0 : ($validated['target_prospects_extract'] ?? 0),
                'target_prospects_verified' => $isManagerRole ? 0 : ($validated['target_prospects_verified'] ?? 0),
                'target_calls' => $isManagerRole ? 0 : ($validated['target_calls'] ?? 0),
                'target_visits' => $validated['target_visits'] ?? 0,
                'target_meetings' => $validated['target_meetings'] ?? 0,
                'target_closers' => $validated['target_closers'] ?? 0,
                'manager_target_calculation_logic' => $validated['manager_target_calculation_logic'] ?? null,
                'manager_junior_scope' => $validated['manager_junior_scope'] ?? null,
                'incentive_per_closer' => $validated['incentive_per_closer'] ?? null,
                'incentive_per_visit' => $validated['incentive_per_visit'] ?? null,
            ]);

            return redirect()
                ->route($routeBase . '.index', ['month' => $target->target_month->format('Y-m')])
                ->with('success', 'Target updated successfully');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to update target: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified target
     */
    public function destroy(Request $request, $id)
    {
        $routeBase = $this->getTargetsRouteBase($request);
        $target = Target::findOrFail($id);
        $month = $target->target_month->format('Y-m');
        
        try {
            $target->delete();

            return redirect()
                ->route($routeBase . '.index', ['month' => $month])
                ->with('success', 'Target deleted successfully');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete target: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk set targets for multiple users
     */
    public function bulkSet(Request $request)
    {
        $routeBase = $this->getTargetsRouteBase($request);
        $currentUser = $request->user();
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'month' => 'required|date_format:Y-m',
            'target_prospects_extract' => 'nullable|integer|min:0',
            'target_prospects_verified' => 'nullable|integer|min:0',
            'target_calls' => 'nullable|integer|min:0',
            'target_visits' => 'nullable|integer|min:0',
            'target_meetings' => 'nullable|integer|min:0',
            'target_closers' => 'nullable|integer|min:0',
        ]);

        // Verify all users are allowed based on role
        $users = User::whereIn('id', $validated['user_ids'])->get();
        foreach ($users as $user) {
            if ($currentUser->isSalesHead()) {
                // Sales Head can set targets for Sales Executive, Senior Manager, and Assistant Sales Manager
                if (!$user->isSalesManager() && !$user->isSalesExecutive() && !$user->isAssistantSalesManager()) {
                    return back()->withErrors(['user_ids' => "Targets can only be set for Senior Managers, Sales Executives, and Assistant Sales Managers. User {$user->name} is not allowed."])->withInput();
                }
            } else {
                // Admin/CRM can set targets for Telecallers, Sales Executives, Senior Managers, and Assistant Sales Managers
                if (!$user->isTelecaller() && !$user->isSalesExecutive() && !$user->isSalesManager() && !$user->isAssistantSalesManager()) {
                    return back()->withErrors(['user_ids' => "Targets can only be set for Telecallers, Sales Executives, Senior Managers, and Assistant Sales Managers. User {$user->name} is not allowed."])->withInput();
                }
            }
        }

        try {
            $this->targetService->bulkSetTargets(
                $validated['user_ids'],
                $validated['month'],
                [
                    'target_prospects_extract' => $validated['target_prospects_extract'] ?? 0,
                    'target_prospects_verified' => $validated['target_prospects_verified'] ?? 0,
                    'target_calls' => $validated['target_calls'] ?? 0,
                    'target_visits' => $validated['target_visits'] ?? 0,
                    'target_meetings' => $validated['target_meetings'] ?? 0,
                    'target_closers' => $validated['target_closers'] ?? 0,
                ]
            );

            return redirect()
                ->route($routeBase . '.index', ['month' => $validated['month']])
                ->with('success', 'Targets set successfully for ' . count($validated['user_ids']) . ' users');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to set targets: ' . $e->getMessage()])
                ->withInput();
        }
    }
}

