<?php

namespace App\Http\Controllers;

use App\Services\DynamicFormService;
use Illuminate\Http\Request;
use App\Models\SalesManagerProfile;
use App\Models\User;

class SalesManagerController extends Controller
{
    private function defaultDashboardVisibility(): array
    {
        return [
            'today_focus_panel' => true,
            'today_focus_fresh_leads' => true,
            'today_focus_overdue' => true,
            'today_focus_meetings' => true,
            'today_focus_site_visits' => true,
            'today_focus_follow_ups' => true,
            'favorites_panel' => true,
            'stat_leads_received' => true,
            'stat_todays_prospects' => true,
            'stat_pending_verifications' => true,
            'stat_overdue_tasks' => true,
            'stat_team_members' => true,
            'stat_pending_tasks' => true,
            'stat_no_response_yet' => true,
            'no_response_section' => true,
            'manager_targets_section' => true,
            'team_targets_section' => true,
            'team_members_cards_section' => true,
            'incentives_section' => true,
            'chatbot_widget' => false,
        ];
    }

    private function isAsmChatbotEnabledForUser(User $user): bool
    {
        if (!$user->isAssistantSalesManager()) {
            return true;
        }

        $visibility = $this->getAsmDashboardVisibilityForUser($user);
        return (bool) ($visibility['chatbot_widget'] ?? false);
    }

    private function defaultSectionViewPreferences(): array
    {
        return [
            'leads' => 'list',
            'prospects' => 'card',
            'meetings' => 'card',
            'site_visits' => 'card',
            'tasks' => 'card',
        ];
    }

    private function getAsmDashboardVisibilityForUser(User $user): array
    {
        $defaults = $this->defaultDashboardVisibility();

        if (!$user->isAssistantSalesManager()) {
            return $defaults;
        }

        $profile = SalesManagerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['preferences' => []]
        );

        $saved = is_array($profile->preferences['dashboard_visibility'] ?? null)
            ? $profile->preferences['dashboard_visibility']
            : [];

        return array_merge($defaults, $saved);
    }

    private function getAsmSectionViewPreferencesForUser(User $user): array
    {
        $defaults = $this->defaultSectionViewPreferences();

        if (!$user->isAssistantSalesManager()) {
            return $defaults;
        }

        $profile = SalesManagerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['preferences' => []]
        );

        $saved = is_array($profile->preferences['section_view_preferences'] ?? null)
            ? $profile->preferences['section_view_preferences']
            : [];

        $normalized = [];
        foreach ($defaults as $key => $default) {
            $value = $saved[$key] ?? $default;
            $normalized[$key] = $value === 'list' ? 'list' : 'card';
        }

        return $normalized;
    }

    private function persistAsmDashboardVisibility(User $user, array $submitted): array
    {
        $defaults = $this->defaultDashboardVisibility();
        $allowedKeys = array_keys($defaults);

        $profile = SalesManagerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['preferences' => []]
        );

        $filtered = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $submitted)) {
                $filtered[$key] = filter_var($submitted[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $filtered[$key] = $filtered[$key] === null ? (bool) $submitted[$key] : $filtered[$key];
            }
        }

        $preferences = is_array($profile->preferences) ? $profile->preferences : [];
        $preferences['dashboard_visibility'] = array_merge(
            $defaults,
            $preferences['dashboard_visibility'] ?? [],
            $filtered
        );

        $profile->preferences = $preferences;
        $profile->save();

        return $this->getAsmDashboardVisibilityForUser($user);
    }

    private function persistAsmSectionViewPreferences(User $user, array $submitted): array
    {
        $defaults = $this->defaultSectionViewPreferences();
        $allowedKeys = array_keys($defaults);

        $profile = SalesManagerProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['preferences' => []]
        );

        $filtered = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $submitted)) {
                $filtered[$key] = $submitted[$key] === 'list' ? 'list' : 'card';
            }
        }

        $preferences = is_array($profile->preferences) ? $profile->preferences : [];
        $preferences['section_view_preferences'] = array_merge(
            $defaults,
            $preferences['section_view_preferences'] ?? [],
            $filtered
        );

        $profile->preferences = $preferences;
        $profile->save();

        return $this->getAsmSectionViewPreferencesForUser($user);
    }

    public function __construct()
    {
        $this->middleware('auth');
        
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            if (!$user) {
                return redirect()->route('login');
            }
            
            // Ensure role is loaded
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }

            view()->share('asmChatbotEnabled', $this->isAsmChatbotEnabledForUser($user));
            
            // Allow Admin, CRM, Sales Head, and Senior Manager to access
            // Only redirect Sales Head if they're trying to access sales-manager dashboard specifically
            if ($user->isSalesHead() && $request->routeIs('sales-manager.dashboard')) {
                return redirect()->route('sales-head.dashboard')->with('info', 'Redirected to Sales Head Dashboard');
            }
            
            return $next($request);
        });
    }

    /**
     * Show sales manager dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Double check - if user is Sales Head, redirect to Sales Head dashboard
        if ($user->isSalesHead()) {
            return redirect()->route('sales-head.dashboard')->with('info', 'Redirected to Sales Head Dashboard');
        }
        
        // Allow Senior Manager, Manager (senior_manager), and Assistant Sales Manager
        if (!$user->isSalesManager() && !$user->isSeniorManager() && !$user->isAssistantSalesManager()) {
            abort(403, 'Unauthorized. Only Senior Managers, Managers, or Assistant Sales Managers can access this page.');
        }
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        return view('sales-manager.dashboard', [
            'api_token' => $token,
            'dashboardVisibility' => $this->getAsmDashboardVisibilityForUser($user),
        ]);
    }

    /**
     * Show team page
     */
    public function team()
    {
        $user = auth()->user();
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        return view('sales-manager.team', ['api_token' => $token]);
    }

    /**
     * Show leads page
     */
    public function leads()
    {
        $user = auth()->user();
        $user->load('manager'); // Load manager relationship for team leader auto-fill
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        return view('sales-manager.leads', [
            'api_token' => $token,
            'sectionViewPreferences' => $this->getAsmSectionViewPreferencesForUser($user),
        ]);
    }

    /**
     * Show prospects page
     */
    public function prospects(DynamicFormService $dynamicFormService)
    {
        $user = auth()->user();
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        // Check for dynamic form for prospect verification
        $dynamicForm = $dynamicFormService->getPublishedFormByLocation('prospects.verify');
        
        // Check if route is unified route (prospects.index) or sales-manager route
        if (request()->routeIs('prospects.index')) {
            return view('prospects.index', ['api_token' => $token, 'dynamicForm' => $dynamicForm]);
        }
        
        return view('sales-manager.prospects', [
            'api_token' => $token,
            'dynamicForm' => $dynamicForm,
            'sectionViewPreferences' => $this->getAsmSectionViewPreferencesForUser($user),
        ]);
    }

    /**
     * Show prospect details page
     */
    public function showProspect($id)
    {
        $user = auth()->user();
        
        $prospect = \App\Models\Prospect::with([
            'telecaller',
            'manager',
            'lead',
            'createdBy',
            'verifiedBy',
            'interestedProjects'
        ])->findOrFail($id);
        
        // If prospect is verified and has a lead, redirect to lead detail page
        if ($prospect->lead_id) {
            return redirect()->route('leads.show', $prospect->lead_id);
        }
        
        // Generate API token for the session (only needed if showing prospect-details)
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        return view('sales-manager.prospect-details', [
            'prospect' => $prospect,
            'api_token' => $token
        ]);
    }

    /**
     * Show reports page
     */
    public function reports()
    {
        return view('sales-manager.reports');
    }

    /**
     * Show profile page
     */
    public function profile()
    {
        return view('sales-manager.sections.profile');
    }

    /**
     * Show dashboard settings page for Assistant Sales Manager.
     */
    public function settings()
    {
        $user = auth()->user();

        if (!$user->isAssistantSalesManager()) {
            abort(403, 'Only Assistant Sales Managers can access dashboard settings.');
        }

        return view('sales-manager.settings', [
            'dashboardVisibility' => $this->getAsmDashboardVisibilityForUser($user),
            'sectionViewPreferences' => $this->getAsmSectionViewPreferencesForUser($user),
        ]);
    }

    public function updateDashboardSettings(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAssistantSalesManager()) {
            abort(403, 'Only Assistant Sales Managers can update dashboard settings.');
        }

        $validated = $request->validate([
            'dashboard_visibility' => ['nullable', 'array'],
            'section_view_preferences' => ['nullable', 'array'],
        ]);

        $dashboardVisibility = $this->getAsmDashboardVisibilityForUser($user);
        $sectionViewPreferences = $this->getAsmSectionViewPreferencesForUser($user);

        if (array_key_exists('dashboard_visibility', $validated)) {
            $dashboardVisibility = $this->persistAsmDashboardVisibility(
                $user,
                $validated['dashboard_visibility']
            );
        }

        if (array_key_exists('section_view_preferences', $validated)) {
            $sectionViewPreferences = $this->persistAsmSectionViewPreferences(
                $user,
                $validated['section_view_preferences']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Dashboard settings updated successfully.',
            'dashboard_visibility' => $dashboardVisibility,
            'section_view_preferences' => $sectionViewPreferences,
        ]);
    }

    /**
     * Show meetings page
     */
    public function meetings()
    {
        $user = auth()->user();
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        // Check if route is unified route (meetings.index) or sales-manager route
        if (request()->routeIs('meetings.index')) {
            return view('meetings.index', ['api_token' => $token]);
        }
        
        return view('sales-manager.meetings', [
            'api_token' => $token,
            'sectionViewPreferences' => $this->getAsmSectionViewPreferencesForUser($user),
        ]);
    }

    /**
     * Show create meeting form
     */
    public function createMeeting(DynamicFormService $dynamicFormService)
    {
        $dynamicForm = $dynamicFormService->getPublishedFormByLocation('meetings.create');
        return view('sales-manager.create-meeting', ['dynamicForm' => $dynamicForm]);
    }

    /**
     * Show create site visit form
     */
    public function createSiteVisit(DynamicFormService $dynamicFormService)
    {
        $dynamicForm = $dynamicFormService->getPublishedFormByLocation('site-visits.create');
        return view('sales-manager.create-site-visit', ['dynamicForm' => $dynamicForm]);
    }

    /**
     * Show site visits page
     */
    public function siteVisits()
    {
        $user = auth()->user();
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        // Check if route is unified route (site-visits.index) or sales-manager route
        if (request()->routeIs('site-visits.index')) {
            return view('site-visits.index', ['api_token' => $token]);
        }
        
        return view('sales-manager.site-visits', [
            'api_token' => $token,
            'sectionViewPreferences' => $this->getAsmSectionViewPreferencesForUser($user),
        ]);
    }

    /**
     * Show closed leads page
     */
    public function closedLeads()
    {
        $user = auth()->user();

        $token = $user->createToken('sales-manager-web-token')->plainTextToken;

        return view('sales-manager.closed', ['api_token' => $token]);
    }

    /**
     * Show tasks page
     */
    public function tasks()
    {
        $user = auth()->user();
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        return view('sales-manager.tasks', [
            'api_token' => $token,
            'sectionViewPreferences' => $this->getAsmSectionViewPreferencesForUser($user),
        ]);
    }

    public function editProspect($id)
    {
        $prospect = \App\Models\Prospect::findOrFail($id);
        return view('prospects.edit', compact('prospect'));
    }

    public function updateProspect(Request $request, $id)
    {
        $prospect = \App\Models\Prospect::findOrFail($id);
        $prospect->update($request->only([
            'customer_name','phone','email','budget',
            'preferred_location','property_type','notes',
            'project','lead_type'
        ]));
        return redirect()->route('prospects')->with('success', 'Prospect updated!');
    }

    public function destroyProspect($id)
    {
        $prospect = \App\Models\Prospect::findOrFail($id);
        $prospect->delete();
        return response()->json(['success' => true, 'message' => 'Prospect deleted!']);
    }
}
