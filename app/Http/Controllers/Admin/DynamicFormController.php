<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DynamicForm;
use App\Models\DynamicFormField;
use App\Services\FormDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DynamicFormController extends Controller
{
    protected $formDetectionService;

    public function __construct(FormDetectionService $formDetectionService)
    {
        $this->formDetectionService = $formDetectionService;
    }

    /**
     * Preview fields of an existing system form
     */
    public function previewExistingForm(string $formPath)
    {
        $fields = $this->getExistingFormFields($formPath);
        $formName = $this->getExistingFormName($formPath);

        if (empty($fields)) {
            abort(404, 'Form not found');
        }

        return view('admin.forms.existing-preview', compact('fields', 'formName', 'formPath'));
    }

    private function getExistingFormName(string $formPath): string
    {
        $names = [
            'crm.automation.leads.create' => 'Lead Creation Form',
            'leads.create'                => 'Lead Form (Standard)',
            'leads.edit'                  => 'Lead Edit Form',
            'meetings.create'             => 'Meeting Form',
            'site-visits.create'          => 'Site Visit Form',
            'calls.create'                => 'Call Log Form',
            'projects.create'             => 'Project Form',
            'closers.index'               => 'Closer Submit Form',
            'finance-manager.incentives'  => 'Incentive Submit Form',
        ];

        return $names[$formPath] ?? 'Form Preview';
    }

    private function getExistingFormFields(string $formPath): array
    {
        $definitions = [
            'crm.automation.leads.create' => [
                ['label' => 'Customer Name',   'type' => 'text',     'name' => 'name',     'required' => true],
                ['label' => 'Phone Number',    'type' => 'tel',      'name' => 'phone',    'required' => true],
                ['label' => 'Email Address',   'type' => 'email',    'name' => 'email',    'required' => false],
                ['label' => 'Lead Source',     'type' => 'select',   'name' => 'source',   'required' => false,
                 'options' => ['Facebook', 'Google', 'Walk-in', 'Referral', 'Other']],
                ['label' => 'Project Interest','type' => 'text',     'name' => 'project',  'required' => false],
                ['label' => 'Budget Range',    'type' => 'text',     'name' => 'budget',   'required' => false],
                ['label' => 'Notes',           'type' => 'textarea', 'name' => 'notes',    'required' => false],
            ],
            'leads.create' => [
                ['label' => 'Customer Name',   'type' => 'text',     'name' => 'name',     'required' => true],
                ['label' => 'Phone Number',    'type' => 'tel',      'name' => 'phone',    'required' => true],
                ['label' => 'Email Address',   'type' => 'email',    'name' => 'email',    'required' => false],
                ['label' => 'Lead Source',     'type' => 'select',   'name' => 'source',   'required' => false,
                 'options' => ['Facebook', 'Google', 'Walk-in', 'Referral', 'Pabbly', 'Other']],
                ['label' => 'Status',          'type' => 'select',   'name' => 'status',   'required' => false,
                 'options' => ['New', 'Contacted', 'Interested', 'Not Interested']],
                ['label' => 'Budget',          'type' => 'text',     'name' => 'budget',   'required' => false],
                ['label' => 'Notes',           'type' => 'textarea', 'name' => 'notes',    'required' => false],
            ],
            'leads.edit' => [
                ['label' => 'Customer Name',   'type' => 'text',     'name' => 'name',     'required' => true],
                ['label' => 'Phone Number',    'type' => 'tel',      'name' => 'phone',    'required' => true],
                ['label' => 'Email Address',   'type' => 'email',    'name' => 'email',    'required' => false],
                ['label' => 'Lead Source',     'type' => 'select',   'name' => 'source',   'required' => false,
                 'options' => ['Facebook', 'Google', 'Walk-in', 'Referral', 'Other']],
                ['label' => 'Status',          'type' => 'select',   'name' => 'status',   'required' => false,
                 'options' => ['New', 'Contacted', 'Interested', 'Not Interested', 'Converted']],
                ['label' => 'Budget',          'type' => 'text',     'name' => 'budget',   'required' => false],
                ['label' => 'Notes',           'type' => 'textarea', 'name' => 'notes',    'required' => false],
            ],
            'meetings.create' => [
                ['label' => 'Customer Name',   'type' => 'text',     'name' => 'customer_name',  'required' => true],
                ['label' => 'Phone Number',    'type' => 'tel',      'name' => 'phone',           'required' => true],
                ['label' => 'Employee',        'type' => 'text',     'name' => 'employee',        'required' => false, 'readonly' => true],
                ['label' => 'Occupation',      'type' => 'text',     'name' => 'occupation',      'required' => false],
                ['label' => 'Date of Visit',   'type' => 'date',     'name' => 'date_of_visit',   'required' => true],
                ['label' => 'Project',         'type' => 'select',   'name' => 'project_id',      'required' => false,
                 'options' => ['Select Project...']],
                ['label' => 'Meeting Notes',   'type' => 'textarea', 'name' => 'notes',           'required' => false],
            ],
            'site-visits.create' => [
                ['label' => 'Customer Name',   'type' => 'text',     'name' => 'customer_name',  'required' => true],
                ['label' => 'Phone Number',    'type' => 'tel',      'name' => 'phone',           'required' => true],
                ['label' => 'Date of Visit',   'type' => 'date',     'name' => 'date_of_visit',   'required' => true],
                ['label' => 'Time of Visit',   'type' => 'time',     'name' => 'time_of_visit',   'required' => false],
                ['label' => 'Project',         'type' => 'select',   'name' => 'project_id',      'required' => false,
                 'options' => ['Select Project...']],
                ['label' => 'Employee',        'type' => 'text',     'name' => 'employee',        'required' => false, 'readonly' => true],
                ['label' => 'Visit Notes',     'type' => 'textarea', 'name' => 'notes',           'required' => false],
            ],
            'calls.create' => [
                ['label' => 'Customer Name',   'type' => 'text',     'name' => 'customer_name',  'required' => true],
                ['label' => 'Phone Number',    'type' => 'tel',      'name' => 'phone',           'required' => true],
                ['label' => 'Call Date & Time','type' => 'datetime-local', 'name' => 'called_at', 'required' => true],
                ['label' => 'Duration (mins)', 'type' => 'number',   'name' => 'duration',        'required' => false],
                ['label' => 'Call Outcome',    'type' => 'select',   'name' => 'outcome',         'required' => false,
                 'options' => ['Connected', 'Not Answered', 'Busy', 'Wrong Number', 'Callback Requested']],
                ['label' => 'Notes',           'type' => 'textarea', 'name' => 'notes',           'required' => false],
            ],
            'projects.create' => [
                ['label' => 'Project Name',    'type' => 'text',     'name' => 'name',       'required' => true],
                ['label' => 'Location',        'type' => 'text',     'name' => 'location',   'required' => false],
                ['label' => 'Project Type',    'type' => 'select',   'name' => 'type',       'required' => false,
                 'options' => ['Residential', 'Commercial', 'Mixed Use', 'Plot']],
                ['label' => 'Price Range',     'type' => 'text',     'name' => 'price_range','required' => false],
                ['label' => 'Description',     'type' => 'textarea', 'name' => 'description','required' => false],
            ],
            'closers.index' => [
                ['label' => 'Customer Name',   'type' => 'text',     'name' => 'customer_name', 'required' => true],
                ['label' => 'Phone Number',    'type' => 'tel',      'name' => 'phone',          'required' => true],
                ['label' => 'Project',         'type' => 'select',   'name' => 'project_id',     'required' => true,
                 'options' => ['Select Project...']],
                ['label' => 'Closer Amount',   'type' => 'number',   'name' => 'amount',         'required' => true],
                ['label' => 'Closing Date',    'type' => 'date',     'name' => 'closing_date',   'required' => true],
                ['label' => 'Status',          'type' => 'select',   'name' => 'status',         'required' => false,
                 'options' => ['Pending', 'Verified', 'Rejected']],
                ['label' => 'Remarks',         'type' => 'textarea', 'name' => 'remarks',        'required' => false],
            ],
            'finance-manager.incentives' => [
                ['label' => 'Employee',        'type' => 'select',   'name' => 'user_id',        'required' => true,
                 'options' => ['Select Employee...']],
                ['label' => 'Incentive Type',  'type' => 'select',   'name' => 'type',           'required' => true,
                 'options' => ['Performance', 'Referral', 'Closing Bonus', 'Festival Bonus', 'Other']],
                ['label' => 'Month',           'type' => 'month',    'name' => 'month',          'required' => true],
                ['label' => 'Amount (₹)',      'type' => 'number',   'name' => 'amount',         'required' => true],
                ['label' => 'Remarks',         'type' => 'textarea', 'name' => 'remarks',        'required' => false],
            ],
        ];

        return $definitions[$formPath] ?? [];
    }

    /**
     * Display a listing of forms (existing + custom)
     */
    public function index(Request $request)
    {
        $query = DynamicForm::with(['fields', 'creator', 'replacedForm']);
        
        // Apply filter if provided
        $filter = $request->input('filter', 'all');
        if ($filter === 'drafts') {
            $query->drafts();
        } elseif ($filter === 'published') {
            $query->published();
        }
        
        $customForms = $query->orderBy('created_at', 'desc')->get();

        // Get existing forms in the system
        $existingForms = $this->getExistingForms();

        return view('admin.forms.index', compact('customForms', 'existingForms', 'filter'));
    }

    /**
     * Show form builder
     */
    public function create(Request $request)
    {
        $existingForm = null;
        $detectedFields = [];
        
        // If creating from existing form
        if ($request->has('from_existing')) {
            $formType = $request->input('type', 'custom');
            $locationPath = $request->input('path', '');
            
            $existingForm = [
                'name' => $request->input('name', 'New Form'),
                'location_path' => $locationPath,
                'form_type' => $formType,
                'description' => 'Dynamic version of existing form: ' . $request->input('name', ''),
            ];
            
            // Detect fields from existing form
            $detectedFields = $this->formDetectionService->getFieldDefinitions($formType, $locationPath);
        }
        
        return view('admin.forms.builder', [
            'form' => null, 
            'existingForm' => $existingForm,
            'detectedFields' => $detectedFields
        ]);
    }

    /**
     * Show form builder for editing
     */
    public function edit(DynamicForm $dynamicForm)
    {
        $dynamicForm->load(['fields' => function($query) {
            $query->orderBy('order');
        }]);

        // #region agent log
        \Log::info('DynamicFormController:edit - Loading form for editing', [
            'form_id' => $dynamicForm->id,
            'fields_count' => $dynamicForm->fields->count(),
            'fields' => $dynamicForm->fields->map(function($f) {
                return ['id' => $f->id, 'key' => $f->field_key, 'type' => $f->field_type];
            })->toArray(),
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'H4'
        ]);
        // #endregion

        return view('admin.forms.builder', ['form' => $dynamicForm]);
    }

    /**
     * Store a newly created form
     */
    public function store(Request $request)
    {
        // Log to verify we're in store method
        \Log::info('DynamicFormController:store - Method called', [
            'request_method' => $request->method(),
            'has_method_field' => $request->has('_method'),
            'method_field_value' => $request->input('_method'),
            'form_name' => $request->input('name'),
        ]);
        
        // Decode JSON fields if sent as string
        $fieldsData = $request->input('fields');
        if (is_string($fieldsData)) {
            $fieldsData = json_decode($fieldsData, true);
            $request->merge(['fields' => $fieldsData]);
        }

        // Decode JSON settings if sent as string
        $settingsData = $request->input('settings');
        if (is_string($settingsData)) {
            $settingsData = json_decode($settingsData, true);
            $request->merge(['settings' => $settingsData ?? []]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:dynamic_forms,slug',
            'description' => 'nullable|string',
            'location_path' => 'required|string|max:255',
            'form_type' => 'required|string|max:50',
            'status' => 'nullable|in:draft,published',
            'settings' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.field_key' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|max:50',
            'fields.*.label' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $status = $validated['status'] ?? 'draft';
            $locationPath = $validated['location_path'];
            
            // If publishing, check for existing published form at same location
            $replacesFormId = null;
            if ($status === 'published') {
                $existingForm = DynamicForm::where('location_path', $locationPath)
                    ->where('status', 'published')
                    ->where('is_active', true)
                    ->first();
                
                if ($existingForm) {
                    // Mark old form as replaced
                    $existingForm->update([
                        'status' => 'draft',
                        'is_active' => false,
                    ]);
                    $replacesFormId = $existingForm->id;
                }
            }
            
            $form = DynamicForm::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'location_path' => $locationPath,
                'form_type' => $validated['form_type'],
                'status' => $status,
                'settings' => $validated['settings'] ?? [],
                'replaces_form_id' => $replacesFormId,
                'created_by' => auth()->id(),
            ]);

            // Create form fields
            foreach ($validated['fields'] as $index => $fieldData) {
                DynamicFormField::create([
                    'form_id' => $form->id,
                    'field_key' => $fieldData['field_key'],
                    'field_type' => $fieldData['field_type'],
                    'label' => $fieldData['label'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'options' => is_array($fieldData['options'] ?? null) ? $fieldData['options'] : null,
                    'validation' => is_array($fieldData['validation'] ?? null) ? $fieldData['validation'] : null,
                    'required' => isset($fieldData['required']) && $fieldData['required'],
                    'order' => $index,
                    'section' => $fieldData['section'] ?? 'default',
                    'styles' => is_array($fieldData['styles'] ?? null) ? $fieldData['styles'] : null,
                    'default_value' => $fieldData['default_value'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.forms.index')
                ->with('success', 'Form created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to create form: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update the specified form
     */
    public function update(Request $request, DynamicForm $dynamicForm)
    {
        // Log to verify we're in update method, not store
        \Log::info('DynamicFormController:update - Method called', [
            'form_id' => $dynamicForm->id,
            'form_name' => $dynamicForm->name,
            'request_method' => $request->method(),
            'has_method_field' => $request->has('_method'),
            'method_field_value' => $request->input('_method'),
        ]);
        
        // Decode JSON fields if sent as string
        $fieldsData = $request->input('fields');
        if (is_string($fieldsData)) {
            $fieldsData = json_decode($fieldsData, true);
            $request->merge(['fields' => $fieldsData]);
        }

        // Decode JSON settings if sent as string
        $settingsData = $request->input('settings');
        if (is_string($settingsData)) {
            $settingsData = json_decode($settingsData, true);
            $request->merge(['settings' => $settingsData ?? []]);
        }

        // #region agent log
        \Log::info('DynamicFormController:update - Raw request data', [
            'raw_fields' => $request->input('fields'),
            'fields_data_type' => gettype($request->input('fields')),
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'H3'
        ]);
        // #endregion
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:dynamic_forms,slug,' . $dynamicForm->id,
            'description' => 'nullable|string',
            'location_path' => 'required|string|max:255',
            'form_type' => 'required|string|max:50',
            'status' => 'nullable|in:draft,published',
            'settings' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.field_key' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string|max:50',
            'fields.*.label' => 'required|string|max:255',
        ]);
        
        // #region agent log
        \Log::info('DynamicFormController:update - After validation', [
            'validated_fields_count' => count($validated['fields']),
            'validated_fields' => array_map(function($f) {
                return ['key' => $f['field_key'] ?? 'N/A', 'type' => $f['field_type'] ?? 'N/A'];
            }, $validated['fields']),
            'sessionId' => 'debug-session',
            'runId' => 'run1',
            'hypothesisId' => 'H3'
        ]);
        // #endregion

        DB::beginTransaction();
        try {
            $status = $validated['status'] ?? $dynamicForm->status ?? 'draft';
            $locationPath = $validated['location_path'];
            $isPublishing = ($status === 'published' && $dynamicForm->status !== 'published');
            
            // If changing from draft to published, handle replacement
            $replacesFormId = $dynamicForm->replaces_form_id;
            if ($isPublishing) {
                $existingForm = DynamicForm::where('location_path', $locationPath)
                    ->where('status', 'published')
                    ->where('is_active', true)
                    ->where('id', '!=', $dynamicForm->id)
                    ->first();
                
                if ($existingForm) {
                    // Mark old form as replaced
                    $existingForm->update([
                        'status' => 'draft',
                        'is_active' => false,
                    ]);
                    $replacesFormId = $existingForm->id;
                }
            }
            
            $dynamicForm->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? $dynamicForm->slug,
                'description' => $validated['description'] ?? null,
                'location_path' => $locationPath,
                'form_type' => $validated['form_type'],
                'status' => $status,
                'settings' => $validated['settings'] ?? [],
                'replaces_form_id' => $replacesFormId,
            ]);

            // Delete existing fields
            $dynamicForm->fields()->delete();

            // #region agent log
            \Log::info('DynamicFormController:update - Received fields', [
                'fields_count' => count($validated['fields']),
                'fields' => array_map(function($f) {
                    return ['key' => $f['field_key'] ?? 'N/A', 'type' => $f['field_type'] ?? 'N/A', 'raw_type' => $f['field_type'] ?? null];
                }, $validated['fields']),
                'form_id' => $dynamicForm->id,
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'H3'
            ]);
            // #endregion

            // Create updated form fields
            foreach ($validated['fields'] as $index => $fieldData) {
                // #region agent log
                \Log::info('DynamicFormController:update - Creating field', [
                    'index' => $index,
                    'field_key' => $fieldData['field_key'] ?? 'N/A',
                    'field_type' => $fieldData['field_type'] ?? 'N/A',
                    'field_type_type' => gettype($fieldData['field_type'] ?? null),
                    'label' => $fieldData['label'] ?? 'N/A',
                    'form_id' => $dynamicForm->id,
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'H3'
                ]);
                // #endregion
                
                $fieldTypeToSave = $fieldData['field_type'] ?? 'text';
                
                $createdField = DynamicFormField::create([
                    'form_id' => $dynamicForm->id,
                    'field_key' => $fieldData['field_key'],
                    'field_type' => $fieldTypeToSave, // Explicitly use the field type
                    'label' => $fieldData['label'],
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'help_text' => $fieldData['help_text'] ?? null,
                    'options' => is_array($fieldData['options'] ?? null) ? $fieldData['options'] : null,
                    'validation' => is_array($fieldData['validation'] ?? null) ? $fieldData['validation'] : null,
                    'required' => isset($fieldData['required']) && $fieldData['required'],
                    'order' => $index,
                    'section' => $fieldData['section'] ?? 'default',
                    'styles' => is_array($fieldData['styles'] ?? null) ? $fieldData['styles'] : null,
                    'default_value' => $fieldData['default_value'] ?? null,
                ]);
                
                // #region agent log
                \Log::info('DynamicFormController:update - Field created in DB', [
                    'field_id' => $createdField->id,
                    'saved_field_type' => $createdField->field_type,
                    'saved_field_key' => $createdField->field_key,
                    'fresh_from_db' => $createdField->fresh()->field_type, // Re-fetch from DB to verify
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'H3'
                ]);
                // #endregion
            }
            
            // #region agent log
            // After all fields are created, reload and verify
            $dynamicForm->refresh();
            $dynamicForm->load('fields');
            \Log::info('DynamicFormController:update - After save, reloaded form fields', [
                'form_id' => $dynamicForm->id,
                'fields_count' => $dynamicForm->fields->count(),
                'fields_from_db' => $dynamicForm->fields->map(function($f) {
                    return ['id' => $f->id, 'key' => $f->field_key, 'type' => $f->field_type];
                })->toArray(),
                'sessionId' => 'debug-session',
                'runId' => 'run1',
                'hypothesisId' => 'H3'
            ]);
            // #endregion

            DB::commit();

            return redirect()
                ->route('admin.forms.index')
                ->with('success', 'Form updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Failed to update form: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified form
     */
    public function destroy(DynamicForm $dynamicForm)
    {
        try {
            $dynamicForm->delete();
            return redirect()
                ->route('admin.forms.index')
                ->with('success', 'Form deleted successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete form: ' . $e->getMessage()]);
        }
    }

    /**
     * Get existing forms in the system
     */
    private function getExistingForms(): array
    {
        $forms = [];

        $addIfRouteExists = function(string $routeName, array $data) use (&$forms) {
            try {
                $data['route'] = route($routeName);
                $forms[] = $data;
            } catch (\Exception $e) {
                // Route doesn't exist, skip
            }
        };

        // 1. Lead Creation Form (CRM Automation)
        $addIfRouteExists('crm.automation.leads.create', [
            'name'     => 'Lead Creation Form',
            'location' => 'CRM > Automation > Create Lead',
            'path'     => 'crm.automation.leads.create',
            'type'     => 'lead',
        ]);

        // 2. Lead Form (Standard - Leads module)
        $addIfRouteExists('leads.create', [
            'name'     => 'Lead Form (Standard)',
            'location' => 'Leads > Create',
            'path'     => 'leads.create',
            'type'     => 'lead',
        ]);

        // 3. Lead Edit Form (uses leads.index as preview since edit needs an ID)
        try {
            $forms[] = [
                'name'     => 'Lead Edit Form',
                'location' => 'Leads > Edit',
                'path'     => 'leads.edit',
                'type'     => 'lead',
                'route'    => route('leads.index'),
            ];
        } catch (\Exception $e) {
            // Skip
        }

        // 4. Meeting Form
        $addIfRouteExists('meetings.create', [
            'name'     => 'Meeting Form',
            'location' => 'Senior Manager > Create Meeting',
            'path'     => 'meetings.create',
            'type'     => 'meeting',
        ]);

        // 5. Site Visit Form
        $addIfRouteExists('site-visits.create', [
            'name'     => 'Site Visit Form',
            'location' => 'Senior Manager > Create Site Visit',
            'path'     => 'site-visits.create',
            'type'     => 'site_visit',
        ]);

        // 6. Call Log Form
        $addIfRouteExists('calls.create', [
            'name'     => 'Call Log Form',
            'location' => 'Calls > Create Call Log',
            'path'     => 'calls.create',
            'type'     => 'call',
        ]);

        // 7. Project Form
        $addIfRouteExists('projects.create', [
            'name'     => 'Project Form',
            'location' => 'Projects > Create',
            'path'     => 'projects.create',
            'type'     => 'project',
        ]);

        // 8. Closer Submit Form
        $addIfRouteExists('closers.index', [
            'name'     => 'Closer Submit Form',
            'location' => 'Closers > Submit Closer',
            'path'     => 'closers.index',
            'type'     => 'closer',
        ]);

        // 9. Incentive Submit Form
        $addIfRouteExists('finance-manager.incentives', [
            'name'     => 'Incentive Submit Form',
            'location' => 'Finance Manager > Incentives',
            'path'     => 'finance-manager.incentives',
            'type'     => 'incentive',
        ]);

        return $forms;
    }
}
