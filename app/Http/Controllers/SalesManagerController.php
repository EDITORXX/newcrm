<?php

namespace App\Http\Controllers;

use App\Services\DynamicFormService;
use Illuminate\Http\Request;
use App\Models\User;

class SalesManagerController extends Controller
{
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
        
        return view('sales-manager.dashboard', ['api_token' => $token]);
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
        
        return view('sales-manager.leads', ['api_token' => $token]);
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
        
        return view('sales-manager.prospects', ['api_token' => $token, 'dynamicForm' => $dynamicForm]);
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
        
        return view('sales-manager.meetings', ['api_token' => $token]);
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
        
        return view('sales-manager.site-visits', ['api_token' => $token]);
    }

    /**
     * Show tasks page
     */
    public function tasks()
    {
        $user = auth()->user();
        
        // Generate API token for the session
        $token = $user->createToken('sales-manager-web-token')->plainTextToken;
        
        return view('sales-manager.tasks', ['api_token' => $token]);
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
