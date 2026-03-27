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

    public function importFromCsv(array $leads, int $userId, ?int $ruleId = null, array $options = []): array
    {
        $selectedStages = collect($options['selected_stages'] ?? [])
            ->filter(fn ($stage) => is_string($stage) && trim($stage) !== '')
            ->map(fn ($stage) => $this->normalizeStageValue($stage))
            ->unique()
            ->values()
            ->all();

        $stageFilterMode = in_array($options['stage_filter_mode'] ?? 'include', ['include', 'exclude'], true)
            ? $options['stage_filter_mode']
            : 'include';
        $importMode = ($options['import_mode'] ?? 'all') === 'demo' ? 'demo' : 'all';

        $filterResult = $this->applyStageFilter($leads, $selectedStages, $stageFilterMode);
        $filteredLeads = $filterResult['leads'];
        $skippedByFilter = $filterResult['skipped_by_filter'];

        if ($importMode === 'demo' && count($filteredLeads) > 1) {
            $skippedByFilter += count($filteredLeads) - 1;
            $filteredLeads = array_slice($filteredLeads, 0, 1);
        }

        $batch = ImportBatch::create([
            'user_id' => $userId,
            'source_type' => 'csv',
            'total_leads' => count($leads),
            'status' => 'processing',
            'assignment_rule_id' => $ruleId,
        ]);

        $imported = 0;
        $failed = 0;
        $skippedDuplicates = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($filteredLeads as $index => $leadData) {
                try {
                    // Validate required fields
                    if (empty($leadData['name']) || empty($leadData['phone'])) {
                        $failed++;
                        $errors[] = "Row " . ($leadData['_row_number'] ?? ($index + 2)) . ": Missing name or phone";
                        continue;
                    }

                    if ($this->leadExistsByPhone($leadData['phone'])) {
                        $skippedDuplicates++;
                        $errors[] = "Row " . ($leadData['_row_number'] ?? ($index + 2)) . ": Duplicate phone skipped";
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
                        'source' => Lead::normalizeSource($leadData['source'] ?? 'other'),
                        'status' => 'new',
                        'notes' => $this->buildImportNotes($leadData),
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
                    $errors[] = "Row " . ($leadData['_row_number'] ?? ($index + 2)) . ": " . $e->getMessage();
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

        return [
            'batch' => $batch->fresh(),
            'skipped_by_filter' => $skippedByFilter,
            'skipped_duplicates' => $skippedDuplicates,
            'stage_filter_mode' => $stageFilterMode,
            'selected_stages' => $selectedStages,
            'import_mode' => $importMode,
        ];
    }

    public function parseCsvFile($file): array
    {
        return $this->analyzeCsvFile($file)['leads'];
    }

    public function analyzeCsvFile($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new \Exception('CSV file is empty or invalid');
        }

        $headerMap = [];
        foreach ($headers as $index => $header) {
            $headerMap[$this->normalizeHeader($header)] = $index;
        }

        $nameIndex = $this->findHeaderIndex($headerMap, ['name', 'full name', 'customer name']);
        $phoneIndex = $this->findHeaderIndex($headerMap, ['phone', 'number', 'phone number']);
        $mobileIndex = $this->findHeaderIndex($headerMap, ['mobile', 'mobile number']);

        if ($nameIndex === null || ($phoneIndex === null && $mobileIndex === null)) {
            throw new \Exception('CSV must contain name/full name and phone/phone number/mobile number columns');
        }

        $emailIndex = $this->findHeaderIndex($headerMap, ['email', 'email address']);
        $addressIndex = $this->findHeaderIndex($headerMap, ['address']);
        $cityIndex = $this->findHeaderIndex($headerMap, ['city']);
        $stateIndex = $this->findHeaderIndex($headerMap, ['state']);
        $pincodeIndex = $this->findHeaderIndex($headerMap, ['pincode', 'pin code', 'zipcode', 'zip code']);
        $sourceIndex = $this->findHeaderIndex($headerMap, ['source', 'lead source']);
        $remarksIndex = $this->findHeaderIndex($headerMap, ['remarks', 'remark', 'notes', 'note', 'comment', 'comments']);
        $stageIndex = $this->findHeaderIndex($headerMap, ['lead stage', 'stage', 'status']);
        $scoreIndex = $this->findHeaderIndex($headerMap, ['lead score', 'score']);
        $ownerIndex = $this->findHeaderIndex($headerMap, ['owner']);
        $createdOnIndex = $this->findHeaderIndex($headerMap, ['created on', 'created at']);
        $sourceCampaignIndex = $this->findHeaderIndex($headerMap, ['source campaign', 'campaign']);
        $prospectIdIndex = $this->findHeaderIndex($headerMap, ['prospect id', 'lead id']);

        $leads = [];
        $stageSummary = [];
        $duplicatePhonesInFile = [];
        $seenPhones = [];

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (empty(array_filter($row, fn ($value) => trim((string) $value) !== ''))) {
                continue;
            }

            $primaryPhone = $this->normalizePhone($this->rowValue($row, $phoneIndex));
            $mobilePhone = $this->normalizePhone($this->rowValue($row, $mobileIndex));
            $selectedPhone = $primaryPhone ?: $mobilePhone;
            $alternatePhone = $primaryPhone && $mobilePhone && $primaryPhone !== $mobilePhone
                ? $mobilePhone
                : null;

            $stageValue = trim((string) $this->rowValue($row, $stageIndex));
            $normalizedStage = $this->normalizeStageValue($stageValue);
            $summaryKey = $normalizedStage === '' ? '(Blank Stage)' : $stageValue;
            $stageSummary[$summaryKey] = ($stageSummary[$summaryKey] ?? 0) + 1;

            if ($selectedPhone !== '') {
                if (isset($seenPhones[$selectedPhone])) {
                    $duplicatePhonesInFile[$selectedPhone] = ($duplicatePhonesInFile[$selectedPhone] ?? 1) + 1;
                } else {
                    $seenPhones[$selectedPhone] = true;
                }
            }

            $leads[] = [
                '_row_number' => $rowNumber,
                'name' => trim((string) $this->rowValue($row, $nameIndex)),
                'phone' => $selectedPhone,
                'alternate_phone' => $alternatePhone,
                'email' => $this->emptyToNull($this->rowValue($row, $emailIndex)),
                'address' => $this->emptyToNull($this->rowValue($row, $addressIndex)),
                'city' => $this->emptyToNull($this->rowValue($row, $cityIndex)),
                'state' => $this->emptyToNull($this->rowValue($row, $stateIndex)),
                'pincode' => $this->emptyToNull($this->rowValue($row, $pincodeIndex)),
                'source' => $this->emptyToNull($this->rowValue($row, $sourceIndex)) ?? 'other',
                'old_remark' => $this->emptyToNull($this->rowValue($row, $remarksIndex)),
                'lead_stage' => $stageValue,
                'lead_score' => $this->emptyToNull($this->rowValue($row, $scoreIndex)),
                'owner' => $this->emptyToNull($this->rowValue($row, $ownerIndex)),
                'created_on' => $this->emptyToNull($this->rowValue($row, $createdOnIndex)),
                'source_campaign' => $this->emptyToNull($this->rowValue($row, $sourceCampaignIndex)),
                'prospect_id' => $this->emptyToNull($this->rowValue($row, $prospectIdIndex)),
                'raw_headers' => $headers,
                'raw_row' => $row,
            ];
        }

        fclose($handle);

        return [
            'leads' => $leads,
            'headers' => $headers,
            'detected_columns' => [
                'name' => $this->columnLabel($headers, $nameIndex),
                'phone' => $this->columnLabel($headers, $phoneIndex),
                'mobile' => $this->columnLabel($headers, $mobileIndex),
                'email' => $this->columnLabel($headers, $emailIndex),
                'source' => $this->columnLabel($headers, $sourceIndex),
                'remarks' => $this->columnLabel($headers, $remarksIndex),
                'lead_stage' => $this->columnLabel($headers, $stageIndex),
            ],
            'stage_summary' => $stageSummary,
            'has_stage_column' => $stageIndex !== null,
            'duplicate_phones_in_file' => array_keys($duplicatePhonesInFile),
        ];
    }

    protected function normalizeHeader(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    protected function findHeaderIndex(array $headerMap, array $aliases): ?int
    {
        foreach ($aliases as $alias) {
            $normalized = $this->normalizeHeader($alias);
            if (array_key_exists($normalized, $headerMap)) {
                return $headerMap[$normalized];
            }
        }

        return null;
    }

    protected function rowValue(array $row, ?int $index): ?string
    {
        if ($index === null) {
            return null;
        }

        return isset($row[$index]) ? trim((string) $row[$index]) : null;
    }

    protected function emptyToNull(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    protected function normalizePhone(?string $value): string
    {
        $value = trim((string) $value);
        $value = trim($value, " \t\n\r\0\x0B'\"");
        $value = preg_replace('/[^0-9+]+/', '', $value);

        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, '+')) {
            return '+' . ltrim(substr($value, 1), '+');
        }

        return $value;
    }

    protected function normalizeStageValue(?string $value): string
    {
        if ($value === '__blank__') {
            return '';
        }

        return strtolower(trim((string) $value));
    }

    protected function applyStageFilter(array $leads, array $selectedStages, string $mode): array
    {
        if (empty($selectedStages)) {
            return [
                'leads' => $leads,
                'skipped_by_filter' => 0,
            ];
        }

        $filtered = [];
        $skipped = 0;

        foreach ($leads as $lead) {
            $stage = $this->normalizeStageValue($lead['lead_stage'] ?? '');
            $matches = in_array($stage, $selectedStages, true);
            $keep = $mode === 'exclude' ? !$matches : $matches;

            if ($keep) {
                $filtered[] = $lead;
            } else {
                $skipped++;
            }
        }

        return [
            'leads' => $filtered,
            'skipped_by_filter' => $skipped,
        ];
    }

    protected function leadExistsByPhone(string $phone): bool
    {
        if ($phone === '') {
            return false;
        }

        return Lead::where('phone', $phone)->exists();
    }

    protected function buildImportNotes(array $leadData): ?string
    {
        $lines = [];

        if (!empty($leadData['old_remark'])) {
            $lines[] = 'Old CRM Remark:';
            $lines[] = trim((string) $leadData['old_remark']);
            $lines[] = '';
        }

        $lines[] = 'Imported from External CRM';

        $metaMap = [
            'prospect_id' => 'Old Prospect ID',
            'lead_stage' => 'Lead Stage',
            'lead_score' => 'Lead Score',
            'owner' => 'Old Owner',
            'source' => 'Lead Source',
            'source_campaign' => 'Source Campaign',
            'created_on' => 'Created On',
            'alternate_phone' => 'Alternate Number',
        ];

        foreach ($metaMap as $key => $label) {
            $value = $leadData[$key] ?? null;
            if ($value !== null && trim((string) $value) !== '') {
                $lines[] = $label . ': ' . trim((string) $value);
            }
        }

        $notes = trim(implode("\n", $lines));

        return $notes !== '' ? $notes : null;
    }

    protected function columnLabel(array $headers, ?int $index): ?string
    {
        return $index !== null && isset($headers[$index]) ? $headers[$index] : null;
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
