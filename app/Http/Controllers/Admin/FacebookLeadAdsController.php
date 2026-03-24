<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FbForm;
use App\Models\FbLead;
use App\Models\FbLeadAdsSettings;
use App\Models\FbPage;
use App\Models\FbCustomMappingField;
use App\Services\FacebookGraphService;
use App\Services\FacebookLeadMappingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacebookLeadAdsController extends Controller
{
    /**
     * Landing: link to settings and list of configured forms (standalone section).
     */
    public function index()
    {
        $settings = FbLeadAdsSettings::getSettings();
        $addedPages = FbPage::whereNotNull('page_access_token')->orderBy('page_name')->get();
        $pagesWithToken = $addedPages->pluck('id');
        $forms = FbForm::with('page')
            ->whereIn('fb_page_id', $pagesWithToken)
            ->orderBy('form_name')
            ->get();
        $hasToken = $addedPages->isNotEmpty();
        $webhookUrl = url('/api/webhooks/facebook/leads');

        $recentLeads = FbLead::with('form')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $webhookEvents = \App\Models\FbWebhookEvent::orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('integrations.facebook-lead-ads.index', compact('settings', 'hasToken', 'forms', 'webhookUrl', 'recentLeads', 'addedPages', 'webhookEvents'));
    }

    /**
     * Settings form (token, graph version, page select after test).
     */
    public function settings()
    {
        $settings = FbLeadAdsSettings::getSettings();
        $pageName = $settings->page_id ? optional(FbPage::where('page_id', $settings->page_id)->first())->page_name : '';
        $addedPages = FbPage::whereNotNull('page_access_token')->orderBy('page_name')->get();

        return view('integrations.facebook-lead-ads.settings', compact('settings', 'pageName', 'addedPages'));
    }

    /**
     * Save settings (token, graph_version, page_id, webhook_verify_token, etc.)
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'page_access_token' => 'nullable|string',
            'graph_version' => 'nullable|string|max:20',
            'page_id' => 'nullable|string|max:50',
            'page_name' => 'nullable|string|max:255',
            'webhook_verify_token' => 'nullable|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'signature_verification_enabled' => 'boolean',
        ]);

        $settings = FbLeadAdsSettings::getSettings();
        $settings->fill($request->only([
            'page_access_token', 'graph_version', 'page_id', 'webhook_verify_token',
            'app_secret', 'signature_verification_enabled',
        ]));
        $settings->signature_verification_enabled = $request->boolean('signature_verification_enabled');
        $settings->save();

        if ($request->filled('page_id')) {
            FbPage::updateOrCreate(
                ['page_id' => $request->page_id],
                ['page_name' => $request->page_name ?? null]
            );
        }

        return response()->json(['success' => true, 'message' => 'Settings saved.']);
    }

    /**
     * Add a page (from Test connection result). Stores page_id, page_name, page_access_token in fb_pages.
     */
    public function addPage(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string|max:50',
            'page_name' => 'required|string|max:255',
            'page_access_token' => 'required|string',
        ]);

        FbPage::updateOrCreate(
            ['page_id' => $request->page_id],
            [
                'page_name' => $request->page_name,
                'page_access_token' => $request->page_access_token,
            ]
        );

        $addedPages = FbPage::whereNotNull('page_access_token')->orderBy('page_name')->get(['id', 'page_id', 'page_name']);
        return response()->json([
            'success' => true,
            'message' => 'Page added.',
            'added_pages' => $addedPages,
        ]);
    }

    /**
     * Remove page: clear token so it no longer appears in Select Form. Keeps row for existing forms.
     */
    public function removePage(Request $request)
    {
        $request->validate(['page_id' => 'required|string|max:50']);
        $page = FbPage::where('page_id', $request->page_id)->first();
        if ($page) {
            $page->update(['page_access_token' => null]);
        }
        return response()->json(['success' => true, 'message' => 'Page removed.']);
    }

    /**
     * Test connection: call Graph API, return pages list.
     */
    public function testConnection(Request $request)
    {
        $request->validate(['page_access_token' => 'required|string']);
        $settings = FbLeadAdsSettings::getSettings();
        $settings->page_access_token = $request->page_access_token;
        $settings->graph_version = $request->input('graph_version', $settings->graph_version ?? 'v18.0');
        $settings->save();

        $client = FacebookGraphService::fromSettings($settings);
        $result = $client->testConnection();

        return response()->json($result);
    }

    /**
     * Form selector: with page_id show forms for that page; without page_id show "Choose a page" list.
     */
    public function forms(Request $request)
    {
        $pageId = $request->query('page_id');
        $settings = FbLeadAdsSettings::getSettings();

        if ($pageId) {
            $page = FbPage::where('page_id', $pageId)->first();
            if (!$page || empty($page->page_access_token)) {
                return redirect()->route('integrations.facebook-lead-ads.forms')
                    ->with('error', 'Page not found or token missing. Re-add the page from Settings.');
            }
            $client = FacebookGraphService::fromToken($page->page_access_token, $settings->graph_version ?? 'v18.0');
            $result = $client->getLeadgenForms($pageId);
            if (!$result['success']) {
                return redirect()->route('integrations.facebook-lead-ads.index')
                    ->with('error', $result['error'] ?? 'Failed to fetch forms.');
            }
            $forms = $result['forms'];
            $existingFormIds = FbForm::whereIn('form_id', array_column($forms, 'id'))->pluck('form_id', 'id')->toArray();
            return view('integrations.facebook-lead-ads.forms', [
                'forms' => $forms,
                'existingFormIds' => $existingFormIds,
                'page' => $page,
                'pages' => null,
            ]);
        }

        $pages = FbPage::whereNotNull('page_access_token')->orderBy('page_name')->get();
        if ($pages->isEmpty()) {
            return redirect()->route('integrations.facebook-lead-ads.settings')
                ->with('warning', 'Add at least one page (Test connection then Add page) first.');
        }
        return view('integrations.facebook-lead-ads.forms', [
            'forms' => [],
            'existingFormIds' => [],
            'page' => null,
            'pages' => $pages,
        ]);
    }

    /**
     * Mapping UI for a form (by Meta form_id). Create FbForm on first visit if needed.
     */
    public function mapping(Request $request, string $formId)
    {
        $settings = FbLeadAdsSettings::getSettings();
        $pageId = $request->query('page_id');
        $page = null;
        if ($pageId) {
            $page = FbPage::where('page_id', $pageId)->first();
        }
        if (!$page) {
            $fbFormExisting = FbForm::where('form_id', $formId)->with('page')->first();
            $page = $fbFormExisting?->page;
        }
        if (!$page) {
            return redirect()->route('integrations.facebook-lead-ads.forms')
                ->with('error', 'Select a page first, then choose a form.');
        }
        if (empty($page->page_access_token)) {
            return redirect()->route('integrations.facebook-lead-ads.settings')
                ->with('error', 'Page token missing. Re-add this page from Settings (Test connection → Add page).');
        }

        $formName = $request->input('form_name', 'Form ' . $formId);
        $fbForm = FbForm::firstOrCreate(
            ['form_id' => $formId],
            ['fb_page_id' => $page->id, 'form_name' => $formName]
        );
        $fbForm->load('page');

        $client = FacebookGraphService::fromToken($page->page_access_token, $settings->graph_version ?? 'v18.0');
        $fieldsResult = $client->getFormFieldsSample($formId);
        $fieldNames = $fieldsResult['fields'] ? array_column($fieldsResult['fields'], 'name') : [];
        if (empty($fieldNames)) {
            $fieldNames = ['full_name', 'email', 'phone_number', 'city', 'state', 'zip_code'];
        }
        $suggestedMapping = FacebookLeadMappingService::suggestMapping($fieldNames);
        $crmKeys = FacebookLeadMappingService::getCrmFieldKeys();
        $currentMapping = $fbForm->mapping?->mapping_json ?? $suggestedMapping;

        return view('integrations.facebook-lead-ads.mapping', [
            'fbForm' => $fbForm,
            'fieldNames' => $fieldNames,
            'suggestedMapping' => $suggestedMapping,
            'currentMapping' => $currentMapping,
            'crmKeys' => $crmKeys,
        ]);
    }

    /**
     * Save mapping and enable form.
     */
    public function saveMapping(Request $request)
    {
        $request->validate([
            'fb_form_id' => 'required|exists:fb_forms,id',
            'mapping' => 'required|array',
            'mapping.*' => 'nullable|string|max:50',
        ]);

        $fbForm = FbForm::findOrFail($request->fb_form_id);

        DB::transaction(function () use ($fbForm, $request) {
            $fbForm->mappings()->create([
                'mapping_json' => $request->mapping,
                'created_by' => auth()->id(),
            ]);
            $fbForm->update(['is_enabled' => true]);
        });

        return response()->json(['success' => true, 'message' => 'Mapping saved and form enabled.', 'redirect' => route('integrations.facebook-lead-ads.index')]);
    }

    /**
     * Create a custom mapping field (for CRM field dropdown on mapping page).
     */
    public function storeCustomField(Request $request)
    {
        $request->validate([
            'field_key' => 'required|string|max:50|alpha_dash|unique:fb_custom_mapping_fields,field_key',
            'label' => 'nullable|string|max:100',
        ]);

        $standardKeys = ['name', 'email', 'phone', 'address', 'city', 'state', 'pincode', 'requirements', 'notes', 'meta'];
        $key = strtolower(trim($request->field_key));
        if (in_array($key, $standardKeys, true)) {
            return response()->json(['success' => false, 'message' => 'This field key already exists as a standard CRM field.']);
        }

        FbCustomMappingField::create([
            'field_key' => $key,
            'label' => $request->filled('label') ? $request->label : $key,
        ]);

        $crmKeys = \App\Services\FacebookLeadMappingService::getCrmFieldKeys();
        return response()->json(['success' => true, 'message' => 'Custom field added.', 'crm_keys' => $crmKeys]);
    }
}
