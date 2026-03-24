<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleSheetsConfig;
use App\Models\GoogleSheetsColumnMapping;
use App\Services\FieldMappingService;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormIntegrationController extends Controller
{
    protected $fieldMappingService;
    protected $sheetsService;

    public function __construct(FieldMappingService $fieldMappingService, GoogleSheetsService $sheetsService)
    {
        $this->fieldMappingService = $fieldMappingService;
        $this->sheetsService = $sheetsService;
    }

    /**
     * List all form integrations
     */
    public function index()
    {
        $configs = GoogleSheetsConfig::where('created_by', auth()->id())
            ->with('columnMappings')
            ->latest()
            ->get();

        return view('integrations.google-sheets-form', compact('configs'));
    }

    /**
     * Show setup wizard Step 1: Select Sheet Type
     */
    public function create()
    {
        return view('integrations.google-sheets-form-setup', ['step' => 1]);
    }

    /**
     * Store Step 1: Save sheet type and create config
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'sheet_type' => 'required|in:meta_facebook,google_forms,custom',
        ]);

        $config = GoogleSheetsConfig::create([
            'sheet_type' => $request->sheet_type,
            'sheet_id' => '', // Will be filled in step 2
            'sheet_name' => 'Sheet1',
            'created_by' => auth()->id(),
            'is_active' => false, // Not active until setup complete
        ]);

        return redirect()->route('integrations.form-integration.step2', $config->id);
    }

    /**
     * Show Step 2: Google Sheet Configuration
     */
    public function step2($id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        return view('integrations.google-sheets-form-setup', ['step' => 2, 'config' => $config]);
    }

    /**
     * Store Step 2: Save sheet configuration
     */
    public function storeStep2(Request $request, $id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'sheet_id' => 'required|string',
            'sheet_name' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'service_account_json_path' => 'nullable|string|max:500',
        ]);

        $sheetId = GoogleSheetsConfig::extractSheetId($request->sheet_id);
        if (!$sheetId) {
            return back()->withErrors(['sheet_id' => 'Invalid sheet ID format'])->withInput();
        }

        $config->update([
            'sheet_id' => $sheetId,
            'sheet_name' => $request->sheet_name,
            'api_key' => $request->api_key,
            'service_account_json_path' => $request->service_account_json_path,
        ]);

        return redirect()->route('integrations.form-integration.step3', $config->id);
    }

    /**
     * Show Step 3: Field Mapping
     */
    public function step3($id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        // Get standard CRM fields
        $standardFields = $this->fieldMappingService->getStandardMappings();

        // Get form template if applicable
        $template = [];
        if ($config->sheet_type !== 'custom') {
            $template = $this->fieldMappingService->getFormTemplate($config->sheet_type);
        }

        return view('integrations.google-sheets-form-setup', [
            'step' => 3,
            'config' => $config,
            'standardFields' => $standardFields,
            'template' => $template,
        ]);
    }

    /**
     * Auto-detect columns from Google Sheet
     */
    public function autoDetectColumns(Request $request)
    {
        $request->validate([
            'sheet_id' => 'required|string',
            'sheet_name' => 'required|string',
            'api_key' => 'nullable|string',
            'service_account_json_path' => 'nullable|string',
        ]);

        try {
            $sheetId = GoogleSheetsConfig::extractSheetId($request->sheet_id);
            if (!$sheetId) {
                Log::error("Failed to extract sheet ID", [
                    'input' => $request->sheet_id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid sheet ID format. Please provide a valid Google Sheet URL or Sheet ID.',
                ], 400);
            }

            // Validate sheet name
            $sheetName = trim($request->sheet_name);
            if (empty($sheetName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sheet name cannot be empty',
                ], 400);
            }

            Log::info("Auto-detecting columns", [
                'sheet_id' => $sheetId,
                'sheet_name' => $sheetName,
                'has_api_key' => !empty($request->api_key),
                'has_service_account' => !empty($request->service_account_json_path),
            ]);

            // Fetch headers (row 1)
            $headers = $this->sheetsService->fetchSheetData(
                $sheetId,
                $sheetName,
                'A:Z',
                $request->api_key,
                $request->service_account_json_path,
                1
            );

            if (empty($headers) || !isset($headers[0])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No headers found in the sheet',
                ], 404);
            }

            $headerRow = $headers[0];
            $columns = [];

            foreach ($headerRow as $index => $headerText) {
                $columnLetter = $this->indexToColumnLetter($index);
                $columns[] = [
                    'position' => $columnLetter,
                    'header' => trim($headerText ?? ''),
                    'index' => $index,
                ];
            }

            return response()->json([
                'success' => true,
                'columns' => $columns,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to auto-detect columns", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch columns: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store Step 3: Save field mappings
     */
    public function storeStep3(Request $request, $id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.sheet_column' => 'required|string',
            'mappings.*.lead_field_key' => 'required|string',
            'mappings.*.field_label' => 'nullable|string',
            'mappings.*.is_required' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Delete existing mappings
            GoogleSheetsColumnMapping::where('google_sheets_config_id', $config->id)->delete();

            // Create new mappings
            $displayOrder = 1;
            foreach ($request->mappings as $mapping) {
                GoogleSheetsColumnMapping::create([
                    'google_sheets_config_id' => $config->id,
                    'sheet_column' => $mapping['sheet_column'],
                    'lead_field_key' => $mapping['lead_field_key'],
                    'field_label' => $mapping['field_label'] ?? $mapping['sheet_column'],
                    'field_type' => 'standard',
                    'is_required' => $mapping['is_required'] ?? false,
                    'display_order' => $displayOrder++,
                ]);
            }

            DB::commit();

            return redirect()->route('integrations.form-integration.step4', $config->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to save field mappings", [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to save mappings: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show Step 4: CRM Status Columns
     */
    public function step4($id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        return view('integrations.google-sheets-form-setup', [
            'step' => 4,
            'config' => $config,
        ]);
    }

    /**
     * Store Step 4: Save CRM status columns
     */
    public function storeStep4(Request $request, $id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'crm_status_columns' => 'required|array',
            'crm_status_columns.crm_sent_status' => 'nullable|string',
            'crm_status_columns.crm_lead_id' => 'nullable|string',
            'crm_status_columns.crm_assigned_user' => 'nullable|string',
            'crm_status_columns.crm_call_date' => 'nullable|string',
            'crm_status_columns.crm_call_status' => 'nullable|string',
        ]);

        $config->update([
            'crm_status_columns_json' => $request->crm_status_columns,
        ]);

        return redirect()->route('integrations.form-integration.step5', $config->id);
    }

    /**
     * Show Step 5: Google Apps Script Setup
     */
    public function step5($id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        // Generate API endpoint URL
        $apiEndpointUrl = url('/api/google-sheets/leads');

        return view('integrations.google-sheets-form-setup', [
            'step' => 5,
            'config' => $config,
            'apiEndpointUrl' => $apiEndpointUrl,
        ]);
    }

    /**
     * Generate Google Apps Script
     */
    public function generateScript($id)
    {
        $config = GoogleSheetsConfig::with('columnMappings')->findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        $apiEndpointUrl = url('/api/google-sheets/leads');
        $sheetId = $config->sheet_id;

        // Generate script with mappings
        $script = $this->generateGoogleAppsScript($config, $apiEndpointUrl);

        return response()->json([
            'success' => true,
            'script' => $script,
        ]);
    }

    /**
     * Store Step 5: Complete setup
     */
    public function storeStep5(Request $request, $id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        $config->update([
            'is_active' => true,
            'api_endpoint_url' => url('/api/google-sheets/leads'),
        ]);

        return redirect()->route('integrations.form-integration.step6', $config->id);
    }

    /**
     * Show Step 6: Test & Complete
     */
    public function step6($id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        return view('integrations.google-sheets-form-setup', [
            'step' => 6,
            'config' => $config,
        ]);
    }

    /**
     * 1-Click Test Integration
     */
    public function test($id)
    {
        $config = GoogleSheetsConfig::with('columnMappings')->findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        try {
            // Create test lead data
            $testData = [
                'sheet_id' => $config->sheet_id,
                'sheet_row_number' => 999, // Test row number
                'name' => 'Test Lead ' . now()->format('Y-m-d H:i:s'),
                'phone' => '9999999999',
                'email' => 'test@example.com',
            ];

            // Add mapped fields
            foreach ($config->columnMappings as $mapping) {
                if ($mapping->lead_field_key !== 'name' && $mapping->lead_field_key !== 'phone') {
                    $testData[$mapping->field_label] = 'Test ' . $mapping->field_label;
                }
            }

            // Make API call
            $response = \Http::post(url('/api/google-sheets/leads'), $testData);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'Test successful! Lead created in CRM.',
                    'lead_id' => $result['lead_id'] ?? null,
                    'assigned_to' => $result['assigned_to'] ?? null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Test failed: ' . ($response->json()['message'] ?? 'Unknown error'),
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error("Integration test failed", [
                'config_id' => $config->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get form template
     */
    public function getFormTemplate(Request $request)
    {
        $type = $request->get('type');
        $template = $this->fieldMappingService->getFormTemplate($type);

        return response()->json([
            'success' => true,
            'template' => $template,
        ]);
    }

    /**
     * Enable/Disable integration
     */
    public function toggle($id)
    {
        $config = GoogleSheetsConfig::findOrFail($id);
        if ($config->created_by !== auth()->id()) {
            abort(403);
        }

        $config->update([
            'is_active' => !$config->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $config->is_active,
        ]);
    }

    /**
     * Helper: Convert index to column letter
     */
    private function indexToColumnLetter(int $index): string
    {
        $result = '';
        $index++;
        while ($index > 0) {
            $index--;
            $result = chr(65 + ($index % 26)) . $result;
            $index = intval($index / 26);
        }
        return $result;
    }

    /**
     * Generate Google Apps Script code
     */
    private function generateGoogleAppsScript(GoogleSheetsConfig $config, string $apiEndpointUrl): string
    {
        $sheetId = $config->sheet_id;
        $sheetName = $config->sheet_name;

        // Build field mappings for script
        $mappings = [];
        foreach ($config->columnMappings as $mapping) {
            $mappings[] = "    '{$mapping->field_label}' => '{$mapping->lead_field_key}'";
        }
        $mappingsStr = implode(",\n", $mappings);

        $script = <<<SCRIPT
// Google Apps Script for CRM Integration
// Sheet Type: {$config->sheet_type}
// API Endpoint: {$apiEndpointUrl}

const API_ENDPOINT = '{$apiEndpointUrl}';
const SHEET_ID = '{$sheetId}';
const SHEET_NAME = '{$sheetName}';

// Field mappings
const FIELD_MAPPINGS = {
{$mappingsStr}
};

// CRM Status columns
const CRM_STATUS_COLUMNS = {
  sent_status: 'CRM Sent Status',
  lead_id: 'CRM Lead ID',
  assigned_user: 'CRM Assigned User',
  call_date: 'CRM Call Date',
  call_status: 'CRM Call Status'
};

/**
 * Main function - triggered when new row is added
 */
function onEdit(e) {
  const sheet = e.source.getActiveSheet();
  const range = e.range;
  
  // Only process if editing the data sheet
  if (sheet.getName() !== SHEET_NAME) {
    return;
  }
  
  // Get the row that was edited
  const row = range.getRow();
  
  // Skip header row
  if (row === 1) {
    return;
  }
  
  // Check if this row was already processed
  const sentStatusCell = sheet.getRange(row, getColumnIndex(CRM_STATUS_COLUMNS.sent_status));
  if (sentStatusCell.getValue() === 'Sent to CRM') {
    return; // Already sent
  }
  
  // Get row data
  const rowData = getRowData(sheet, row);
  
  // Send to CRM
  sendToCRM(rowData, row);
}

/**
 * Get row data as object
 */
function getRowData(sheet, row) {
  const data = {};
  const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
  
  headers.forEach((header, index) => {
    const cellValue = sheet.getRange(row, index + 1).getValue();
    if (cellValue) {
      data[header] = cellValue;
    }
  });
  
  return data;
}

/**
 * Send data to CRM
 */
function sendToCRM(rowData, rowNumber) {
  try {
    // Map fields using configuration
    const payload = {
      sheet_id: SHEET_ID,
      sheet_row_number: rowNumber,
      sheet_type: '{$config->sheet_type}'
    };
    
    // Map each field
    Object.keys(FIELD_MAPPINGS).forEach(sheetField => {
      const crmField = FIELD_MAPPINGS[sheetField];
      if (rowData[sheetField]) {
        payload[crmField] = rowData[sheetField];
      }
    });
    
    // Make API call
    const response = UrlFetchApp.fetch(API_ENDPOINT, {
      method: 'post',
      contentType: 'application/json',
      payload: JSON.stringify(payload)
    });
    
    const result = JSON.parse(response.getContentText());
    
    // Update CRM status columns
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAME);
    
    if (result.status === 'ok') {
      updateCell(sheet, rowNumber, CRM_STATUS_COLUMNS.sent_status, 'Sent to CRM');
      updateCell(sheet, rowNumber, CRM_STATUS_COLUMNS.lead_id, result.lead_id || '');
      
      if (result.assigned_to) {
        updateCell(sheet, rowNumber, CRM_STATUS_COLUMNS.assigned_user, result.assigned_to.name || '');
      }
    } else {
      updateCell(sheet, rowNumber, CRM_STATUS_COLUMNS.sent_status, 'Error: ' + (result.message || 'Unknown error'));
    }
    
  } catch (error) {
    Logger.log('Error sending to CRM: ' + error.toString());
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAME);
    updateCell(sheet, rowNumber, CRM_STATUS_COLUMNS.sent_status, 'Error: ' + error.toString());
  }
}

/**
 * Update cell value
 */
function updateCell(sheet, row, columnName, value) {
  try {
    const colIndex = getColumnIndex(columnName);
    if (colIndex > 0) {
      sheet.getRange(row, colIndex).setValue(value);
    }
  } catch (e) {
    Logger.log('Error updating cell: ' + e.toString());
  }
}

/**
 * Get column index by name
 */
function getColumnIndex(columnName) {
  const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAME);
  const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
  return headers.indexOf(columnName) + 1;
}

/**
 * Setup trigger (run once)
 * Must use .forSpreadsheet() before .onEdit() - TriggerBuilder does not have .onEdit() directly.
 */
function setupTrigger() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  ScriptApp.newTrigger('onEdit')
    .forSpreadsheet(ss)
    .onEdit()
    .create();
  Logger.log('Trigger setup complete!');
}
SCRIPT;

        return $script;
    }
}
