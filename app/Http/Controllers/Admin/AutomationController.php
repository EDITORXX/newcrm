<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FbForm;
use App\Models\GoogleSheetsConfig;
use App\Models\Role;
use App\Models\SourceAutomationRule;
use App\Models\SourceAutomationRuleUser;
use App\Models\User;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index()
    {
        $rules = SourceAutomationRule::with(['users.user', 'fbForm', 'singleUser', 'fallbackUser'])
            ->latest()
            ->get();

        return view('admin.automation.index', compact('rules'));
    }

    public function create()
    {
        $fbForms      = FbForm::with('page')->where('is_enabled', true)->get();
        $googleSheets = GoogleSheetsConfig::where('is_active', true)->where('is_draft', false)->orderBy('sheet_name')->get();
        $salesUsers   = $this->getSalesUsers();

        return view('admin.automation.form', compact('fbForms', 'googleSheets', 'salesUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'source'                  => 'required|in:facebook_lead_ads,pabbly,mcube,google_sheets,csv,all',
            'fb_form_id'              => 'nullable|exists:fb_forms,id',
            'google_sheet_config_id'  => 'nullable|exists:google_sheets_config,id',
            'assignment_method'       => 'required|in:round_robin,first_available,percentage,single_user',
            'single_user_id'          => 'nullable|required_if:assignment_method,single_user|exists:users,id',
            'auto_create_task'        => 'boolean',
            'daily_limit'             => 'nullable|integer|min:1',
            'fallback_user_id'        => 'nullable|exists:users,id',
            'is_active'               => 'boolean',
            'users'                   => 'nullable|array',
            'users.*.user_id'         => 'required|exists:users,id',
            'users.*.percentage'      => 'nullable|numeric|min:0|max:100',
        ]);

        $rule = SourceAutomationRule::create([
            'name'                   => $data['name'],
            'source'                 => $data['source'],
            'fb_form_id'             => $data['source'] === 'facebook_lead_ads' ? ($data['fb_form_id'] ?? null) : null,
            'google_sheet_config_id' => $data['source'] === 'google_sheets' ? ($data['google_sheet_config_id'] ?? null) : null,
            'assignment_method'      => $data['assignment_method'],
            'single_user_id'    => $data['single_user_id'] ?? null,
            'auto_create_task'  => $request->boolean('auto_create_task', true),
            'daily_limit'       => $data['daily_limit'] ?? null,
            'fallback_user_id'  => $data['fallback_user_id'] ?? null,
            'is_active'         => $request->boolean('is_active', true),
            'created_by'        => auth()->id(),
        ]);

        if (!empty($data['users']) && $data['assignment_method'] !== 'single_user') {
            foreach ($data['users'] as $u) {
                SourceAutomationRuleUser::create([
                    'rule_id'    => $rule->id,
                    'user_id'    => $u['user_id'],
                    'percentage' => $u['percentage'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.automation.index')
            ->with('success', 'Automation rule created successfully.');
    }

    public function edit(SourceAutomationRule $rule)
    {
        $rule->load('users.user', 'fbForm', 'googleSheetConfig', 'singleUser', 'fallbackUser');
        $fbForms      = FbForm::with('page')->where('is_enabled', true)->get();
        $googleSheets = GoogleSheetsConfig::where('is_active', true)->where('is_draft', false)->orderBy('sheet_name')->get();
        $salesUsers   = $this->getSalesUsers();

        return view('admin.automation.form', compact('rule', 'fbForms', 'googleSheets', 'salesUsers'));
    }

    public function update(Request $request, SourceAutomationRule $rule)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'source'                 => 'required|in:facebook_lead_ads,pabbly,mcube,google_sheets,csv,all',
            'fb_form_id'             => 'nullable|exists:fb_forms,id',
            'google_sheet_config_id' => 'nullable|exists:google_sheets_config,id',
            'assignment_method'      => 'required|in:round_robin,first_available,percentage,single_user',
            'single_user_id'         => 'nullable|required_if:assignment_method,single_user|exists:users,id',
            'auto_create_task'       => 'boolean',
            'daily_limit'            => 'nullable|integer|min:1',
            'fallback_user_id'       => 'nullable|exists:users,id',
            'is_active'              => 'boolean',
            'users'                  => 'nullable|array',
            'users.*.user_id'        => 'required|exists:users,id',
            'users.*.percentage'     => 'nullable|numeric|min:0|max:100',
        ]);

        $rule->update([
            'name'                   => $data['name'],
            'source'                 => $data['source'],
            'fb_form_id'             => $data['source'] === 'facebook_lead_ads' ? ($data['fb_form_id'] ?? null) : null,
            'google_sheet_config_id' => $data['source'] === 'google_sheets' ? ($data['google_sheet_config_id'] ?? null) : null,
            'assignment_method'      => $data['assignment_method'],
            'single_user_id'         => $data['single_user_id'] ?? null,
            'auto_create_task'       => $request->boolean('auto_create_task', true),
            'daily_limit'            => $data['daily_limit'] ?? null,
            'fallback_user_id'       => $data['fallback_user_id'] ?? null,
            'is_active'              => $request->boolean('is_active', true),
        ]);

        // Sync users
        $rule->users()->delete();
        if (!empty($data['users']) && $data['assignment_method'] !== 'single_user') {
            foreach ($data['users'] as $u) {
                SourceAutomationRuleUser::create([
                    'rule_id'    => $rule->id,
                    'user_id'    => $u['user_id'],
                    'percentage' => $u['percentage'] ?? null,
                ]);
            }
        }

        return redirect()->route('admin.automation.index')
            ->with('success', 'Automation rule updated successfully.');
    }

    public function destroy(SourceAutomationRule $rule)
    {
        $rule->users()->delete();
        $rule->delete();

        return redirect()->route('admin.automation.index')
            ->with('success', 'Automation rule deleted.');
    }

    public function toggle(SourceAutomationRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);

        return response()->json(['is_active' => $rule->is_active]);
    }

    protected function getSalesUsers()
    {
        return User::with('role')
            ->where('is_active', true)
            ->whereHas('role', fn($q) => $q->whereIn('slug', [
                Role::SALES_EXECUTIVE,
                Role::SALES_MANAGER,
                Role::ASSISTANT_SALES_MANAGER,
                Role::SENIOR_MANAGER,
            ]))
            ->orderBy('name')
            ->get();
    }

    public function history(\App\Models\SourceAutomationRule $rule)
    {
        // Rule ke users ki IDs
        $ruleUserIds = \App\Models\SourceAutomationRuleUser::where('rule_id', $rule->id)
            ->pluck('user_id')->toArray();

        // Base query - rule ke method aur users se match karo
        $baseQuery = \App\Models\LeadAssignment::where('assignment_method', $rule->assignment_method)
            ->whereIn('assigned_to', $ruleUserIds);

        // Source filter - facebook leads ya all
        if ($rule->source !== 'all') {
            $baseQuery->whereHas('lead', function($q) use ($rule) {
                $q->where('source', $rule->source);
            });
        }

        // Search filters
        $search = request('search');
        $assignedToFilter = request('assigned_to');
        $dateFilter = request('date_filter');

        $filteredQuery = clone $baseQuery;

        if ($search) {
            $filteredQuery->where(function($q) use ($search) {
                $q->whereHas('lead', function($lq) use ($search) {
                    $lq->where('name', 'like', "%$search%")
                       ->orWhere('phone', 'like', "%$search%");
                })->orWhereHas('assignedTo', function($uq) use ($search) {
                    $uq->where('name', 'like', "%$search%");
                });
            });
        }

        if ($assignedToFilter) {
            $filteredQuery->where('assigned_to', $assignedToFilter);
        }

        if ($dateFilter === 'today') {
            $filteredQuery->whereDate('assigned_at', today());
        } elseif ($dateFilter === 'week') {
            $filteredQuery->whereBetween('assigned_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter === 'month') {
            $filteredQuery->whereMonth('assigned_at', now()->month)->whereYear('assigned_at', now()->year);
        }

        $assignments = $filteredQuery
            ->with(['lead:id,name,phone,source', 'assignedTo:id,name', 'assignedBy:id,name'])
            ->latest('assigned_at')
            ->paginate(50)
            ->withQueryString();

        $totalAssignments = $baseQuery->count();
        $todayAssignments = (clone $baseQuery)->whereDate('assigned_at', today())->count();
        $thisWeek = (clone $baseQuery)->whereBetween('assigned_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $uniqueUsers = (clone $baseQuery)->distinct('assigned_to')->count('assigned_to');

        // Rule users for filter dropdown
        $ruleUsers = \App\Models\User::whereIn('id', $ruleUserIds)->get(['id','name']);

        return view('admin.automation.history', compact('rule','assignments','totalAssignments','todayAssignments','thisWeek','uniqueUsers','ruleUsers'));
    }
}
