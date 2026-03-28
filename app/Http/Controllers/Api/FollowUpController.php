<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = FollowUp::with(['lead', 'creator']);

        // Role-based filtering
        if ($user->isSalesExecutive() || $user->isAssistantSalesManager()) {
            $query->where('created_by', $user->id);
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->whereIn('created_by', $teamMemberIds);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $followUps = $query->latest('scheduled_at')->paginate($request->get('per_page', 15));

        return response()->json($followUps);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'type' => 'required|in:call,email,meeting,site_visit,other',
            'notes' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'scheduled';

        $followUp = FollowUp::create($validated);
        $followUp->load(['lead', 'creator']);

        // Update lead's next follow-up date
        $lead = Lead::find($validated['lead_id']);
        $lead->update(['next_followup_at' => $validated['scheduled_at']]);

        return response()->json($followUp, 201);
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $user = $request->user();

        // Check access
        if ($followUp->created_by !== $user->id && !$user->canViewAllLeads()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:call,email,meeting,site_visit,other',
            'notes' => 'sometimes|string',
            'scheduled_at' => 'sometimes|date',
            'completed_at' => 'nullable|date',
            'status' => 'sometimes|in:scheduled,completed,missed,cancelled',
            'outcome' => 'nullable|string',
        ]);

        $followUp->update($validated);

        return response()->json($followUp->load(['lead', 'creator']));
    }

    public function destroy(FollowUp $followUp)
    {
        $user = request()->user();

        if ($followUp->created_by !== $user->id && !$user->canManageUsers()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $followUp->delete();

        return response()->json(['message' => 'Follow-up deleted successfully']);
    }
}
