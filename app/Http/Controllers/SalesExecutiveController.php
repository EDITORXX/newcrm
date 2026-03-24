<?php

namespace App\Http\Controllers;

use App\Services\TelecallerDashboardService;
use Illuminate\Http\Request;

class SalesExecutiveController extends Controller
{
    /**
     * Show sales executive dashboard
     */
    public function dashboard(Request $request, TelecallerDashboardService $service)
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please login to access the dashboard.');
        }
        
        $dateRange = $request->get('date_range', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $targetMonth = $request->get('target_month', now()->format('Y-m')); // Default to current month
        $targetFilter = $request->get('target_filter', 'today'); // Default to today
        
        $data = $service->getDashboardData($userId, $dateRange, $startDate, $endDate);
        $cardStats = $service->getDashboardCardStats($userId, $dateRange, $startDate, $endDate, $targetMonth, $targetFilter);
        
        return view('sales-executive.sections.dashboard', compact('data', 'cardStats', 'dateRange', 'startDate', 'endDate', 'targetMonth', 'targetFilter'));
    }

    /**
     * Show sales executive tasks page
     */
    public function tasks()
    {
        return view('sales-executive.sections.tasks');
    }

    /**
     * Show sales executive leads page
     */
    public function leads()
    {
        return view('sales-executive.sections.leads');
    }

    /**
     * Show sales executive reports page
     */
    public function reports()
    {
        return view('sales-executive.sections.reports');
    }

    /**
     * Show sales executive verification pending page
     */
    public function verificationPending()
    {
        return view('sales-executive.sections.verification-pending');
    }

    /**
     * Show sales executive profile page
     */
    public function profile()
    {
        return view('sales-executive.sections.profile');
    }
}
