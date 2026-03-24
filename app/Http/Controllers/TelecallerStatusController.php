<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\TelecallerStatusService;
use App\Services\UserStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TelecallerStatusController extends Controller
{
    protected $statusService;
    protected $userStatusService;

    public function __construct(
        TelecallerStatusService $statusService,
        UserStatusService $userStatusService
    ) {
        $this->statusService = $statusService;
        $this->userStatusService = $userStatusService;
    }

    /**
     * Index - show all user statuses (except admin)
     */
    public function index()
    {
        $adminRoleId = Role::where('slug', Role::ADMIN)->value('id');
        
        $users = User::where('role_id', '!=', $adminRoleId)
            ->where('is_active', true)
            ->with(['role', 'userProfile', 'telecallerProfile', 'telecallerDailyLimit'])
            ->get()
            ->map(function ($user) {
                $userProfile = $user->userProfile;
                $telecallerProfile = $user->telecallerProfile;
                $isAbsent = $userProfile ? $userProfile->isCurrentlyAbsent() : false;
                
                // For telecallers, use TelecallerStatusService for detailed checks
                // For other users, use UserStatusService for absent check only
                if ($user->isSalesExecutive()) {
                    $canReceive = $this->statusService->canReceiveAssignment($user->id);
                    $pendingCount = $this->statusService->getPendingLeadsCount($user->id);
                    $maxPendingLeads = $telecallerProfile?->max_pending_leads ?? 50;
                } else {
                    $canReceive = [
                        'can_receive' => $this->userStatusService->canUserReceiveLeads($user->id),
                        'is_absent' => $isAbsent,
                        'has_reached_threshold' => false,
                        'pending_count' => 0,
                        'max_pending' => null,
                    ];
                    $pendingCount = 0;
                    $maxPendingLeads = null;
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? '',
                    'is_sales_executive' => $user->isSalesExecutive(),
                    'is_absent' => $isAbsent,
                    'absent_reason' => $userProfile?->absent_reason,
                    'absent_until' => $userProfile?->absent_until,
                    'pending_count' => $pendingCount,
                    'max_pending_leads' => $maxPendingLeads,
                    'can_receive' => $canReceive['can_receive'],
                ];
            });

        return view('lead-assignment.telecaller-status', compact('users'));
    }

    /**
     * Update telecaller status
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'is_absent' => 'required|boolean',
            'absent_reason' => 'nullable|string|max:500',
            'absent_until' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        
        // Check if user is admin (admins cannot be marked absent)
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin users cannot be marked as absent.'
            ], 422);
        }

        $absentUntil = $request->absent_until ? Carbon::parse($request->absent_until) : null;

        // Use UserStatusService for all users (not just telecallers)
        $profile = $this->userStatusService->toggleAbsentStatus(
            $user->id,
            $request->is_absent,
            $request->absent_reason,
            $absentUntil
        );

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'profile' => $profile->fresh(),
        ]);
    }

    /**
     * Get user status (API)
     */
    public function getStatus(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->input('telecaller_id'); // Support both for backward compatibility
        
        if (!$userId) {
            return response()->json(['error' => 'User ID required'], 422);
        }

        $user = User::findOrFail($userId);
        $userProfile = $this->userStatusService->getOrCreateProfile($user->id);
        
        if ($user->isTelecaller()) {
            $canReceive = $this->statusService->canReceiveAssignment($user->id);
            $pendingCount = $this->statusService->getPendingLeadsCount($user->id);
            $telecallerProfile = $this->statusService->getOrCreateProfile($user->id);
        } else {
            $canReceive = [
                'can_receive' => $this->userStatusService->canUserReceiveLeads($user->id),
                'is_absent' => $userProfile->isCurrentlyAbsent(),
                'has_reached_threshold' => false,
                'pending_count' => 0,
                'max_pending' => null,
            ];
            $pendingCount = 0;
            $telecallerProfile = null;
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'is_absent' => $userProfile->isCurrentlyAbsent(),
            'absent_reason' => $userProfile->absent_reason,
            'absent_until' => $userProfile->absent_until,
            'pending_count' => $pendingCount,
            'max_pending_leads' => $telecallerProfile?->max_pending_leads ?? null,
            'can_receive' => $canReceive['can_receive'],
        ]);
    }
}
