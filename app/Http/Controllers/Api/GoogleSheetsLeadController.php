<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoogleSheetsConfig;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Events\LeadCreated;
use App\Services\DuplicateDetectionService;
use App\Services\FieldMappingService;
use App\Services\LeadAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GoogleSheetsLeadController extends Controller
{
    protected $fieldMappingService;
    protected $leadAssignmentService;

    public function __construct(FieldMappingService $fieldMappingService, LeadAssignmentService $leadAssignmentService)
    {
        $this->fieldMappingService = $fieldMappingService;
        $this->leadAssignmentService = $leadAssignmentService;
    }

    /**
     * Store a new lead from Google Apps Script
     */
    public function store(Request $request)
    {
        try {
            // Validate required fields
            $validator = Validator::make($request->all(), [
                'sheet_id' => 'required|string',
                'sheet_row_number' => 'required|integer|min:1',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get sheet config
            $sheetId = GoogleSheetsConfig::extractSheetId($request->sheet_id);
            $config = GoogleSheetsConfig::where('sheet_id', $sheetId)
                ->where('is_active', true)
                ->first();

            if (!$config) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google Sheet configuration not found or inactive',
                ], 404);
            }

            // Map fields using configuration
            $payload = $request->all();
            $mappedData = $this->fieldMappingService->mapFieldsFromPayload($payload, $config);

            // Ensure required fields
            $mappedData['name'] = $mappedData['name'] ?? $request->name;
            $mappedData['phone'] = $mappedData['phone'] ?? $request->phone;
            $mappedData['source'] = Lead::normalizeSource($mappedData['source'] ?? 'google_sheets');
            $mappedData['status'] = 'new';

            // Sanitize/validate phone (avoid importing IDs/timestamps as phone numbers)
            /** @var DuplicateDetectionService $duplicateService */
            $duplicateService = app(DuplicateDetectionService::class);
            $sanitizedPhone = $duplicateService->sanitizePhone((string) $mappedData['phone']);
            $phoneDigits = preg_replace('/[^0-9]/', '', $sanitizedPhone);
            if (!$duplicateService->isValidPhone($phoneDigits)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => [
                        'phone' => ['Invalid phone number'],
                    ],
                ], 422);
            }
            $mappedData['phone'] = $phoneDigits;

            // Check for duplicate by phone
            $existingLead = Lead::where('phone', $mappedData['phone'])->first();
            if ($existingLead) {
                // Update sheet reference if exists
                $assignment = LeadAssignment::where('lead_id', $existingLead->id)
                    ->where('sheet_config_id', $config->id)
                    ->where('sheet_row_number', $request->sheet_row_number)
                    ->first();

                if (!$assignment) {
                    $fallbackAssignedTo = optional($existingLead->activeAssignments()->first())->assigned_to
                        ?? $config->linked_telecaller_id
                        ?? $config->created_by;

                    LeadAssignment::create([
                        'lead_id' => $existingLead->id,
                        'sheet_config_id' => $config->id,
                        'sheet_row_number' => $request->sheet_row_number,
                        'assigned_to' => $fallbackAssignedTo,
                        'assigned_by' => $config->created_by,
                        // Don't change the lead's real assignment; this is only a sheet-row link
                        'assignment_type' => 'secondary',
                        'assigned_at' => now(),
                        'is_active' => false,
                    ]);
                }

                return response()->json([
                    'status' => 'ok',
                    'message' => 'Lead already exists',
                    'lead_id' => $existingLead->id,
                    'assigned_to' => null,
                ]);
            }

            // Create lead
            $lead = Lead::create([
                'name' => $mappedData['name'],
                'phone' => $mappedData['phone'],
                'email' => $mappedData['email'] ?? null,
                'city' => $mappedData['city'] ?? null,
                'state' => $mappedData['state'] ?? null,
                'property_type' => $mappedData['property_type'] ?? null,
                'budget' => $mappedData['budget'] ?? null,
                'requirements' => $mappedData['requirements'] ?? null,
                'notes' => $mappedData['notes'] ?? null,
                'source' => Lead::normalizeSource($mappedData['source'] ?? 'google_sheets'),
                'status' => $mappedData['status'] ?? 'new',
                'created_by' => $config->created_by,
            ]);

            // Fire LeadCreated event
            event(new LeadCreated($lead));

            // Auto-assign (preferred). If no assignment config is set, fall back to owner.
            $assignedUser = null;
            try {
                // Use assignLead method with sheet config ID
                $assignedUserId = $this->leadAssignmentService->assignLead($lead, $config->id, $config->created_by);
                if (!$assignedUserId) {
                    // Ensure DB constraint is satisfied and row tracking is stored
                    $assignedUserId = $config->linked_telecaller_id ?? $config->created_by;

                    LeadAssignment::create([
                        'lead_id' => $lead->id,
                        'assigned_to' => $assignedUserId,
                        'assigned_by' => $config->created_by,
                        'assignment_type' => 'primary',
                        'assignment_method' => 'manual',
                        'assigned_at' => now(),
                        'is_active' => true,
                        'sheet_config_id' => $config->id,
                        'sheet_row_number' => $request->sheet_row_number,
                    ]);
                } else {
                    // Add sheet row tracking to the created assignment record
                    $lead->refresh();
                    $active = $lead->activeAssignments()->first();
                    if ($active) {
                        $active->update([
                            'sheet_row_number' => $request->sheet_row_number,
                            'sheet_config_id' => $config->id,
                        ]);
                    }
                }

                $assignedUser = \App\Models\User::find($assignedUserId);
            } catch (\Exception $e) {
                Log::error("Failed to auto-assign lead from Google Sheet", [
                    'lead_id' => $lead->id,
                    'config_id' => $config->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info("Lead created from Google Sheet", [
                'lead_id' => $lead->id,
                'sheet_id' => $sheetId,
                'row_number' => $request->sheet_row_number,
                'assigned_to' => $assignedUser?->id,
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Lead created successfully',
                'lead_id' => $lead->id,
                'assigned_to' => $assignedUser ? [
                    'id' => $assignedUser->id,
                    'name' => $assignedUser->name,
                ] : null,
            ]);

        } catch (\Exception $e) {
            Log::error("Error creating lead from Google Sheet", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create lead: ' . $e->getMessage(),
            ], 500);
        }
    }
}
