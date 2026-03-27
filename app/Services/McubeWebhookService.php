<?php

namespace App\Services;

use App\Models\CallLog;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\TelecallerTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Processes incoming MCube webhook payloads.
 *
 * Returns an array:
 *   status  => 'success' | 'skipped' | 'failed'
 *   message => human-readable result
 *   lead_id, agent_id, call_log_id (when available)
 */
class McubeWebhookService
{
    /**
     * Main entry point – call this from the controller.
     */
    public function process(array $payload): array
    {
        try {
            // 1. Only process answered calls
            if (strtoupper($payload['dialstatus'] ?? '') !== 'ANSWER') {
                return $this->result('skipped', 'Skipped: dialstatus is not ANSWER (got: ' . ($payload['dialstatus'] ?? 'null') . ')');
            }

            // 2. Find agent by emp_phone OR agentname
            $empPhone  = $this->normalizePhone($payload['emp_phone'] ?? '');
            $agentName = trim($payload['agentname'] ?? '');
            $agent     = $this->findUserByPhone($empPhone);
            if (!$agent && !empty($agentName)) {
                $agent = $this->findUserByName($agentName);
            }
            if (!$agent) {
                return $this->result('failed', "Agent not found for phone: {$empPhone} or name: {$agentName}");
            }

            // 3. Find or create lead by customer phone
            $customerPhone = $this->normalizePhone($payload['callto'] ?? '');
            if (empty($customerPhone)) {
                return $this->result('failed', 'Customer phone (callto) is missing.');
            }

            $leadCreated = false;
            $lead = $this->findLeadByPhone($customerPhone);

            if (!$lead) {
                // Create new lead
                $lead = Lead::create([
                    'name'       => 'MCube Lead (' . $customerPhone . ')',
                    'phone'      => $customerPhone,
                    'source'     => Lead::normalizeSource('mcube'),
                    'status'     => 'new',
                    'created_by' => $agent->id,
                ]);
                $leadCreated = true;
            }

            // 4. Assign agent to lead (if not already assigned)
            $alreadyAssigned = LeadAssignment::where('lead_id', $lead->id)
                ->where('assigned_to', $agent->id)
                ->where('is_active', true)
                ->exists();

            if (!$alreadyAssigned) {
                LeadAssignment::create([
                    'lead_id'     => $lead->id,
                    'assigned_to' => $agent->id,
                    'assigned_by' => $agent->id,
                    'is_active'   => true,
                ]);
            }

            // 5. Save call log (use existing call_logs table)
            $startTime = $this->parseDateTime($payload['starttime'] ?? null);
            $endTime   = $this->parseDateTime($payload['endtime']   ?? null);
            $duration  = ($startTime && $endTime) ? $endTime->diffInSeconds($startTime) : 0;
            $direction = strtolower($payload['direction'] ?? 'inbound');
            $callType  = ($direction === 'inbound') ? 'incoming' : 'outgoing';

            $callLog = CallLog::create([
                'telecaller_id'    => $agent->id,
                'user_id'          => $agent->id,
                'lead_id'          => $lead->id,
                'phone_number'     => $customerPhone,
                'call_type'        => $callType,
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'duration'         => $duration,
                'status'           => 'completed',
                'recording_url'    => $payload['filename'] ?? null,
                'mcube_call_id'    => $payload['callid']   ?? null,
                'mcube_agent_phone'=> $empPhone,
                'synced_from_mobile' => false,
            ]);

            // 6. Create task based on agent role
            $agentRole = $agent->role->slug ?? $agent->role ?? '';
            $isSalesExecutive = in_array($agentRole, ['sales_executive', 'telecaller']);
            if ($isSalesExecutive) {
                TelecallerTask::create([
                    'lead_id'      => $lead->id,
                    'assigned_to'  => $agent->id,
                    'created_by'   => $agent->id,
                    'task_type'    => 'lead_form_fill',
                    'status'       => 'pending',
                    'scheduled_at' => now(),
                ]);
            } else {
                // Sales Manager / Senior Manager / etc. -> Task model
                \App\Models\Task::create([
                    'lead_id'      => $lead->id,
                    'assigned_to'  => $agent->id,
                    'created_by'   => $agent->id,
                    'type'         => 'phone_call',
                    'title'        => 'Call ' . $lead->name,
                    'status'       => 'pending',
                    'scheduled_at' => now(),
                ]);
            }

            $msg = $leadCreated
                ? "New lead created, call logged, task created. Agent: {$agent->name}"
                : "Existing lead updated, call logged, task created. Agent: {$agent->name}";

            return $this->result('success', $msg, $lead->id, $agent->id, $callLog->id);

        } catch (\Throwable $e) {
            Log::error('McubeWebhookService::process failed', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
            return $this->result('failed', 'Server error: ' . $e->getMessage());
        }
    }

    // ── Helpers ─────────────────────────────────────────────

    /**
     * Normalize phone: strip +91 / 91 prefix, keep 10 digits.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone); // digits only
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            $phone = substr($phone, 1);
        }
        return $phone;
    }

    /** Find user where phone matches (last 10 digits). */
    private function findUserByPhone(string $phone): ?User
    {
        if (empty($phone)) return null;

        return User::where('phone', $phone)
            ->orWhere('phone', 'like', '%' . substr($phone, -10))
            ->first();
    }

    /** Find user by name (case-insensitive, partial match). */
    private function findUserByName(string $name): ?User
    {
        if (empty($name)) return null;
        // Exact match first
        $user = User::whereRaw("LOWER(name) = ?", [strtolower($name)])->where("is_active", true)->first();
        if ($user) return $user;
        // Partial match - first word match
        $firstName = explode(" ", $name)[0];
        return User::whereRaw("LOWER(name) LIKE ?", ["%" . strtolower($firstName) . "%"])->where("is_active", true)->first();
    }

    /** Find lead where phone matches (last 10 digits). */
    private function findLeadByPhone(string $phone): ?Lead
    {
        if (empty($phone)) return null;

        return Lead::where('phone', $phone)
            ->orWhere('phone', 'like', '%' . substr($phone, -10))
            ->whereNull('deleted_at')
            ->first();
    }

    /** Parse datetime string from MCube (format: "2023-10-12 11:49:57"). */
    private function parseDateTime(?string $dt): ?Carbon
    {
        if (empty($dt)) return null;
        try {
            return Carbon::parse($dt);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Build a standard result array. */
    private function result(string $status, string $message, ?int $leadId = null, ?int $agentId = null, ?int $callLogId = null): array
    {
        return compact('status', 'message', 'leadId', 'agentId', 'callLogId');
    }
}
