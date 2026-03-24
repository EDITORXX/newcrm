<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ImportBatch;
use App\Models\ImportedLead;
use App\Services\LeadAssignmentService;
use App\Services\TaskService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadImportService
{
    protected $assignmentService;
    protected $taskService;

    public function __construct(
        LeadAssignmentService $assignmentService,
        TaskService $taskService
    ) {
        $this->assignmentService = $assignmentService;
        $this->taskService = $taskService;
    }

    public function importFromCsv(array $leads, int $userId, ?int $ruleId = null): ImportBatch
    {
        $batch = ImportBatch::create([
            'user_id' => $userId,
            'source_type' => 'csv',
            'total_leads' => count($leads),
            'status' => 'processing',
            'assignment_rule_id' => $ruleId,
        ]);

        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($leads as $index => $leadData) {
                try {
                    // Validate required fields
                    if (empty($leadData['name']) || empty($leadData['phone'])) {
                        $failed++;
                        $errors[] = "Row " . ($index + 1) . ": Missing name or phone";
                        continue;
                    }

                    // Create lead
                    $lead = Lead::create([
                        'name' => $leadData['name'],
                        'phone' => $leadData['phone'],
                        'email' => $leadData['email'] ?? null,
                        'address' => $leadData['address'] ?? null,
                        'city' => $leadData['city'] ?? null,
                        'state' => $leadData['state'] ?? null,
                        'pincode' => $leadData['pincode'] ?? null,
                        'source' => $leadData['source'] ?? 'other',
                        'status' => 'new',
                        'created_by' => $userId,
                    ]);

                    // Assign lead using rule
                    $assignedTo = null;
                    if ($ruleId) {
                        try {
                            $assignedTo = $this->assignmentService->assignLead($lead, $ruleId, $userId);
                        } catch (\Exception $e) {
                            Log::error("Assignment error for lead {$lead->id}: " . $e->getMessage());
                        }
                    }

                    // Track imported lead
                    ImportedLead::create([
                        'import_batch_id' => $batch->id,
                        'lead_id' => $lead->id,
                        'assigned_to' => $assignedTo,
                        'assigned_at' => $assignedTo ? now() : null,
                        'import_data' => $leadData,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    Log::error("Lead import error: " . $e->getMessage());
                }
            }

            $batch->update([
                'imported_leads' => $imported,
                'failed_leads' => $failed,
                'status' => 'completed',
                'error_log' => $errors,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $batch->update([
                'status' => 'failed',
                'error_log' => array_merge($errors, [$e->getMessage()]),
            ]);
            throw $e;
        }

        return $batch->fresh();
    }

    public function parseCsvFile($file): array
    {
        $leads = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new \Exception('CSV file is empty or invalid');
        }

        // Normalize headers (lowercase, trim)
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);

        // Find required column indices
        $nameIndex = array_search('name', $headers);
        $phoneIndex = array_search('phone', $headers) !== false 
            ? array_search('phone', $headers) 
            : array_search('number', $headers);

        if ($nameIndex === false || $phoneIndex === false) {
            throw new \Exception('CSV must contain "name" and "phone" (or "number") columns');
        }

        // Read data rows
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            $leadData = [
                'name' => $row[$nameIndex] ?? '',
                'phone' => $row[$phoneIndex] ?? '',
            ];

            // Map optional fields
            $emailIndex = array_search('email', $headers);
            if ($emailIndex !== false) {
                $leadData['email'] = $row[$emailIndex] ?? null;
            }

            $addressIndex = array_search('address', $headers);
            if ($addressIndex !== false) {
                $leadData['address'] = $row[$addressIndex] ?? null;
            }

            $cityIndex = array_search('city', $headers);
            if ($cityIndex !== false) {
                $leadData['city'] = $row[$cityIndex] ?? null;
            }

            $stateIndex = array_search('state', $headers);
            if ($stateIndex !== false) {
                $leadData['state'] = $row[$stateIndex] ?? null;
            }

            $pincodeIndex = array_search('pincode', $headers);
            if ($pincodeIndex !== false) {
                $leadData['pincode'] = $row[$pincodeIndex] ?? null;
            }

            $sourceIndex = array_search('source', $headers);
            if ($sourceIndex !== false) {
                $leadData['source'] = $row[$sourceIndex] ?? null;
            }

            $leads[] = $leadData;
        }

        fclose($handle);
        return $leads;
    }

    /**
     * @deprecated — Smart Import removed. Method kept as stub to avoid DI errors.
     */
    public function importFromCsvWithAutomation(array $leads, int $userId, $automation = null): \App\Models\ImportBatch
    {
        $batch = ImportBatch::create([
            'user_id' => $userId,
            'source_type' => 'csv',
            'total_leads' => count($leads),
            'status' => 'processing',
            'automation_id' => $automation->id,
        ]);

        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($leads as $index => $leadData) {
                try {
                    // Validate required fields
                    if (empty($leadData['name']) || empty($leadData['phone'])) {
                        $failed++;
                        $errors[] = "Row " . ($index + 1) . ": Missing name or phone";
                        continue;
                    }

                    // Create lead
                    $lead = Lead::create([
                        'name' => $leadData['name'],
                        'phone' => $leadData['phone'],
                        'email' => $leadData['email'] ?? null,
                        'address' => $leadData['address'] ?? null,
                        'city' => $leadData['city'] ?? null,
                        'state' => $leadData['state'] ?? null,
                        'pincode' => $leadData['pincode'] ?? null,
                        'source' => $leadData['source'] ?? 'other',
                        'status' => 'new',
                        'created_by' => $userId,
                    ]);

                    // Assign lead using automation config
                    $assignedTo = null;
                    $automationConfig = [
                        'assignment_mode' => $automation->assignment_mode,
                        'distribution_config' => $automation->distribution_config,
                        'conditions' => $automation->conditions ?? [],
                        'fallback_user_id' => $automation->fallback_user_id,
                    ];

                    try {
                        $assignedTo = $this->smartAssignmentService->assignLead($lead, $automationConfig, $userId);
                        
                        // Create phone call task if enabled
                        if ($assignedTo && ($automation->auto_create_call_task ?? true)) {
                            try {
                                $this->taskService->createPhoneCallTask($lead, $assignedTo, $userId);
                            } catch (\Exception $e) {
                                Log::warning("Failed to create phone call task for lead {$lead->id}: " . $e->getMessage());
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Assignment error for lead {$lead->id}: " . $e->getMessage());
                    }

                    // Track imported lead
                    ImportedLead::create([
                        'import_batch_id' => $batch->id,
                        'lead_id' => $lead->id,
                        'assigned_to' => $assignedTo,
                        'assigned_at' => $assignedTo ? now() : null,
                        'import_data' => $leadData,
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    Log::error("Lead import error: " . $e->getMessage());
                }
            }

            $batch->update([
                'imported_leads' => $imported,
                'failed_leads' => $failed,
                'status' => 'completed',
                'error_log' => $errors,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $batch->update([
                'status' => 'failed',
                'error_log' => array_merge($errors, [$e->getMessage()]),
            ]);
            throw $e;
        }

        return $batch->fresh();
    }
}

