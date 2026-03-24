<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\LeadAssignment;
use App\Events\LeadAssigned;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function create()
    {
        $users = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->whereIn('slug', ['sales_manager', 'sales_executive']);
            })
            ->with('role')
            ->get();

        return view('crm.automation.create-lead', compact('users'));
    }

    public function store(Request $request)
    {
        // For CRM users, only name and phone are required
        // Detailed requirements will be filled later via centralized form
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $validated['created_by'] = $request->user()->id;
            $validated['status'] = 'new';
            $validated['source'] = 'crm_manual'; // Mark as manually created by CRM

            $lead = Lead::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'created_by' => $validated['created_by'],
                'status' => $validated['status'],
                'source' => $validated['source'],
            ]);

            if (!empty($validated['assigned_to'])) {
                $this->assignLead($lead, (int) $validated['assigned_to'], $request->user()->id);
            }

            DB::commit();

            $successMessage = $request->filled('assigned_to')
                ? "Lead '{$lead->name}' created successfully and assigned. A calling task has been created for the assigned user. Please fill the detailed requirements below."
                : "Lead '{$lead->name}' created successfully. Please fill the detailed requirements below.";

            return redirect()
                ->route('leads.edit', $lead->id)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to create lead: ' . $e->getMessage()])
                ->withInput();
        }
    }

    private function assignLead(Lead $lead, int $assignedTo, int $assignedBy, bool $createCallingTask = false): void
    {
        // Deactivate existing assignments
        $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);

        // Create new assignment
        LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedBy,
            'assignment_type' => 'primary',
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        // Create calling task if requested (for any role, not just telecallers)
        if ($createCallingTask) {
            try {
                // Check if task already exists
                $existingTask = \App\Models\Task::where('lead_id', $lead->id)
                    ->where('assigned_to', $assignedTo)
                    ->where('type', 'phone_call')
                    ->where('status', 'pending')
                    ->first();

                if (!$existingTask) {
                    $this->taskService->createPhoneCallTask($lead, $assignedTo, $assignedBy);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the lead creation
                \Illuminate\Support\Facades\Log::error("Failed to create calling task for lead {$lead->id}: " . $e->getMessage());
            }
        }

        // Fire event (listener CreateTelecallerTask will create calling task)
        event(new LeadAssigned($lead, $assignedTo, $assignedBy));

        // Fallback: ensure calling task exists for assignee (when admin/CRM assigns, task must appear for user)
        try {
            $assignee = \App\Models\User::with('role')->find($assignedTo);
            if ($assignee && $assignee->role) {
                $slug = $assignee->role->slug ?? '';
                $hasTask = false;
                if ($slug === \App\Models\Role::SALES_EXECUTIVE) {
                    $hasTask = \App\Models\TelecallerTask::where('lead_id', $lead->id)
                        ->where('assigned_to', $assignedTo)
                        ->whereIn('status', ['pending', 'in_progress'])->exists();
                    if (!$hasTask) {
                        $this->taskService->createPhoneCallTask($lead, $assignedTo, $assignedBy);
                    }
                } elseif (in_array($slug, [\App\Models\Role::SALES_MANAGER, \App\Models\Role::ASSISTANT_SALES_MANAGER])) {
                    $hasTask = \App\Models\Task::where('lead_id', $lead->id)
                        ->where('assigned_to', $assignedTo)
                        ->where('type', 'phone_call')
                        ->whereIn('status', ['pending', 'in_progress'])->exists();
                    if (!$hasTask) {
                        \App\Models\Task::create([
                            'lead_id' => $lead->id,
                            'assigned_to' => $assignedTo,
                            'type' => 'phone_call',
                            'title' => "Call lead: {$lead->name}",
                            'description' => "Phone call task for lead: {$lead->name} ({$lead->phone})",
                            'status' => 'pending',
                            'scheduled_at' => now()->addMinutes(10),
                            'created_by' => $assignedBy,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("CRM assignLead: fallback task creation failed for lead {$lead->id}: " . $e->getMessage());
        }
    }
}

