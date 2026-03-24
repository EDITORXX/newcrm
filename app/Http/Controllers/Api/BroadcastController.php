<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BroadcastMessage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BroadcastController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send broadcast message (Admin/CRM only)
     */
    public function sendBroadcast(Request $request)
    {
        $user = $request->user();

        // Only Admin and CRM can send broadcasts
        if (!$user->isAdmin() && !$user->isCrm()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Admin and CRM can send broadcasts.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target_type' => 'required|in:all_users,role_based',
            'target_roles' => 'required_if:target_type,role_based|array',
            'target_roles.*' => 'string|exists:roles,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->notificationService->sendBroadcast(
                $user,
                $request->title,
                $request->message,
                $request->target_type,
                $request->target_roles ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Broadcast sent successfully',
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Broadcast Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send broadcast: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unread broadcasts for current user
     */
    public function getUnreadBroadcasts(Request $request)
    {
        $user = $request->user();

        $broadcasts = BroadcastMessage::where(function ($query) use ($user) {
            $query->where('target_type', 'all_users')
                ->orWhere(function ($q) use ($user) {
                    $q->where('target_type', 'role_based')
                        ->whereJsonContains('target_roles', $user->role->slug);
                });
        })
        ->get()
        ->filter(function ($broadcast) use ($user) {
            return !$broadcast->isReadBy($user->id);
        })
        ->values();
        
        // Load sender relationship
        $broadcasts->load('sender');

        return response()->json([
            'success' => true,
            'data' => $broadcasts,
        ]);
    }

    /**
     * Mark broadcast as read
     */
    public function markAsRead(Request $request, BroadcastMessage $broadcast)
    {
        $user = $request->user();

        $broadcast->markAsReadBy($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Broadcast marked as read',
        ]);
    }
}
