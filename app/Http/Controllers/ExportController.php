<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Prospect;
use App\Models\Meeting;
use App\Models\SiteVisit;
use App\Models\User;
use App\Models\Role;
use App\Models\LeadAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,crm,sales_manager,sales_head');
    }

    /**
     * Display export page with templates and custom options
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get filters data for dropdowns
        $statuses = ['new', 'connected', 'verified_prospect', 'meeting_scheduled', 'meeting_completed', 
                     'visit_scheduled', 'visit_done', 'revisited_scheduled', 'revisited_completed', 
                     'closed', 'dead', 'on_hold'];
        
        $users = User::where('is_active', true)
            ->whereHas('role', function($q) {
                $q->whereIn('slug', [Role::SALES_MANAGER, Role::ASSISTANT_SALES_MANAGER, Role::SALES_EXECUTIVE]);
            })
            ->orderBy('name')
            ->get();
        
        $projects = \App\Models\Project::where('is_active', true)->orderBy('name')->get();
        
        $interestedProjectNames = \App\Models\InterestedProjectName::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('export.index', compact('statuses', 'users', 'projects', 'interestedProjectNames'));
    }

    /**
     * Export leads with custom filters
     */
    public function exportLeads(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'fields' => 'required|array|min:1',
            'interested_projects' => 'nullable|array',
            'interested_projects.*' => 'exists:interested_project_names,id',
        ]);

        $user = auth()->user();
        $query = $this->buildLeadQuery($user, $request);

        $leads = $query->get();

        if ($leads->isEmpty()) {
            return back()->with('error', 'No leads found matching the selected filters.');
        }

        // Limit to 10000 records
        if ($leads->count() > 10000) {
            return back()->with('error', 'Export limit exceeded. Please refine your filters. Maximum 10,000 records allowed.');
        }

        if ($request->format === 'csv') {
            return $this->exportLeadsToCsv($leads, $request->fields);
        } else {
            return $this->exportLeadsToPdf($leads, $request->fields);
        }
    }

    /**
     * Quick export prospects
     */
    public function exportProspects(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'status' => 'nullable|array',
        ]);

        $user = auth()->user();
        $query = Prospect::with(['telecaller', 'lead', 'assignedManager', 'createdBy']);

        // Apply role-based filtering
        if ($user->isSalesManager() || $user->isSalesHead()) {
            $teamMemberIds = $user->isSalesHead() 
                ? $user->getAllTeamMemberIds()
                : $user->teamMembers()->pluck('id')->toArray();
            
            if (!empty($teamMemberIds)) {
                $query->whereIn('telecaller_id', $teamMemberIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->whereIn('verification_status', $request->status);
        }

        // Apply date range
        if ($request->has('date_range') && $request->date_range !== 'all_time') {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
        }

        $prospects = $query->get();

        if ($prospects->isEmpty()) {
            return back()->with('error', 'No prospects found matching the selected filters.');
        }

        if ($request->format === 'csv') {
            return $this->exportProspectsToCsv($prospects);
        } else {
            return $this->exportProspectsToPdf($prospects);
        }
    }

    /**
     * Quick export meetings
     */
    public function exportMeetings(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'status' => 'nullable|array',
        ]);

        $user = auth()->user();
        $query = Meeting::with(['lead', 'assignedTo', 'creator'])
            ->where('is_converted', false); // Exclude converted meetings

        // Apply role-based filtering
        if ($user->isSalesManager() || $user->isSalesHead()) {
            $teamMemberIds = $user->isSalesHead() 
                ? $user->getAllTeamMemberIds()
                : $user->teamMembers()->pluck('id')->toArray();
            
            if (!empty($teamMemberIds)) {
                $query->whereIn('assigned_to', $teamMemberIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->whereIn('status', $request->status);
        }

        // Apply date range
        if ($request->has('date_range') && $request->date_range !== 'all_time') {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('scheduled_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
        }

        $meetings = $query->get();

        if ($meetings->isEmpty()) {
            return back()->with('error', 'No meetings found matching the selected filters.');
        }

        if ($request->format === 'csv') {
            return $this->exportMeetingsToCsv($meetings);
        } else {
            return $this->exportMeetingsToPdf($meetings);
        }
    }

    /**
     * Quick export site visits
     */
    public function exportSiteVisits(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'visit_type' => 'nullable|array',
            'status' => 'nullable|array',
        ]);

        $user = auth()->user();
        $query = SiteVisit::with(['lead', 'assignedTo', 'creator']);

        // Apply role-based filtering
        if ($user->isSalesManager() || $user->isSalesHead()) {
            $teamMemberIds = $user->isSalesHead() 
                ? $user->getAllTeamMemberIds()
                : $user->teamMembers()->pluck('id')->toArray();
            
            if (!empty($teamMemberIds)) {
                $query->whereIn('assigned_to', $teamMemberIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply visit type filter
        if ($request->has('visit_type') && !empty($request->visit_type)) {
            $query->whereIn('lead_type', $request->visit_type);
        }

        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->whereIn('status', $request->status);
        }

        // Apply date range
        if ($request->has('date_range') && $request->date_range !== 'all_time') {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('scheduled_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
        }

        $siteVisits = $query->get();

        if ($siteVisits->isEmpty()) {
            return back()->with('error', 'No site visits found matching the selected filters.');
        }

        if ($request->format === 'csv') {
            return $this->exportSiteVisitsToCsv($siteVisits);
        } else {
            return $this->exportSiteVisitsToPdf($siteVisits);
        }
    }

    /**
     * Quick export closed leads
     */
    public function exportClosedLeads(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
        ]);

        $user = auth()->user();
        $query = Lead::with(['creator', 'activeAssignments.assignedTo', 'siteVisits'])
            ->where('status', 'closed');

        // Apply role-based filtering
        if ($user->isSalesManager() || $user->isSalesHead()) {
            $teamMemberIds = $user->isSalesHead() 
                ? $user->getAllTeamMemberIds()
                : $user->teamMembers()->pluck('id')->toArray();
            
            if (!empty($teamMemberIds)) {
                $query->whereHas('activeAssignments', function($q) use ($teamMemberIds) {
                    $q->whereIn('assigned_to', $teamMemberIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply date range
        if ($request->has('date_range') && $request->date_range !== 'all_time') {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('updated_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
        }

        $leads = $query->get();

        if ($leads->isEmpty()) {
            return back()->with('error', 'No closed leads found.');
        }

        $fields = ['name', 'phone', 'email', 'status', 'budget', 'created_at', 'updated_at', 'assigned_to'];
        
        if ($request->format === 'csv') {
            return $this->exportLeadsToCsv($leads, $fields);
        } else {
            return $this->exportLeadsToPdf($leads, $fields);
        }
    }

    /**
     * Quick export dead leads
     */
    public function exportDeadLeads(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
        ]);

        $user = auth()->user();
        $query = Lead::with(['creator', 'activeAssignments.assignedTo'])
            ->where(function($q) {
                $q->where('status', 'dead')
                  ->orWhere('is_dead', true);
            });

        // Apply role-based filtering
        if ($user->isSalesManager() || $user->isSalesHead()) {
            $teamMemberIds = $user->isSalesHead() 
                ? $user->getAllTeamMemberIds()
                : $user->teamMembers()->pluck('id')->toArray();
            
            if (!empty($teamMemberIds)) {
                $query->whereHas('activeAssignments', function($q) use ($teamMemberIds) {
                    $q->whereIn('assigned_to', $teamMemberIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply date range
        if ($request->has('date_range') && $request->date_range !== 'all_time') {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('marked_dead_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
        }

        $leads = $query->get();

        if ($leads->isEmpty()) {
            return back()->with('error', 'No dead leads found.');
        }

        $fields = ['name', 'phone', 'email', 'status', 'dead_reason', 'dead_at_stage', 'marked_dead_at', 'marked_dead_by'];
        
        if ($request->format === 'csv') {
            return $this->exportLeadsToCsv($leads, $fields);
        } else {
            return $this->exportLeadsToPdf($leads, $fields);
        }
    }

    /**
     * Build lead query with filters
     */
    private function buildLeadQuery($user, Request $request)
    {
        $query = Lead::with(['creator', 'activeAssignments.assignedTo', 'prospects.interestedProjects', 'meetings', 'siteVisits']);

        // Apply role-based filtering
        if ($user->isSalesHead()) {
            $teamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($teamMemberIds)) {
                $query->whereHas('activeAssignments', function ($q) use ($teamMemberIds) {
                    $q->whereIn('assigned_to', $teamMemberIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id')->toArray();
            if (!empty($teamMemberIds)) {
                $query->whereHas('activeAssignments', function ($q) use ($teamMemberIds) {
                    $q->whereIn('assigned_to', $teamMemberIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply filters from request
        if ($request->has('status') && !empty($request->status)) {
            $query->whereIn('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->whereHas('activeAssignments', function($q) use ($request) {
                $q->where('assigned_to', $request->user_id);
            });
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_range') && $request->date_range !== 'all_time') {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
        }

        // Lead type filters (multiple selection support)
        if ($request->has('lead_type') && !empty($request->lead_type)) {
            $types = is_array($request->lead_type) ? $request->lead_type : [$request->lead_type];
            
            $query->where(function($q) use ($types) {
                foreach ($types as $type) {
                    $q->orWhere(function($subQ) use ($type) {
                        if ($type === 'prospect') {
                            $subQ->where('status', 'verified_prospect');
                        } elseif ($type === 'visit') {
                            $subQ->whereIn('status', ['visit_scheduled', 'visit_done'])
                                  ->whereHas('siteVisits', function($visitQ) {
                                      $visitQ->where('lead_type', 'New Visit');
                                  });
                        } elseif ($type === 'revisit') {
                            $subQ->whereIn('status', ['revisited_scheduled', 'revisited_completed'])
                                  ->whereHas('siteVisits', function($visitQ) {
                                      $visitQ->where('lead_type', 'Revisited');
                                  });
                        } elseif ($type === 'meeting') {
                            $subQ->where(function($meetingQ) {
                                $meetingQ->whereIn('status', ['meeting_scheduled', 'meeting_completed'])
                                         ->orWhereHas('meetings');
                            });
                        } elseif ($type === 'closer') {
                            $subQ->whereHas('siteVisits', function($closerQ) {
                                $closerQ->where(function($cQ) {
                                    $cQ->where('closer_status', 'pending')
                                       ->orWhereNotNull('closer_status');
                                });
                            });
                        }
                    });
                }
            });
        }

        // Interested projects filter
        if ($request->has('interested_projects') && !empty($request->interested_projects)) {
            $projectIds = is_array($request->interested_projects) ? $request->interested_projects : [$request->interested_projects];
            $query->whereHas('prospects.interestedProjects', function($q) use ($projectIds) {
                $q->whereIn('interested_project_names.id', $projectIds);
            });
        }

        return $query;
    }

    /**
     * Export leads to CSV
     */
    private function exportLeadsToCsv($leads, $fields)
    {
        $headers = [];
        $fieldLabels = $this->getLeadFieldLabels();
        
        foreach ($fields as $field) {
            if (isset($fieldLabels[$field])) {
                $headers[] = $fieldLabels[$field];
            }
        }

        $filename = 'leads_export_' . date('Y-m-d_His') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($leads as $lead) {
            $row = [];
            foreach ($fields as $field) {
                $row[] = $this->getLeadFieldValue($lead, $field);
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Export leads to PDF
     */
    private function exportLeadsToPdf($leads, $fields)
    {
        $fieldLabels = $this->getLeadFieldLabels();
        $headers = [];
        foreach ($fields as $field) {
            if (isset($fieldLabels[$field])) {
                $headers[] = $fieldLabels[$field];
            }
        }

        $html = view('export.pdf.leads', compact('leads', 'headers', 'fields'))->render();
        
        // Check if dompdf is available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $filename = 'leads_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } elseif (class_exists('PDF')) {
            $pdf = \PDF::loadHTML($html);
            $filename = 'leads_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } else {
            // Fallback: Return HTML for browser print
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'inline; filename="leads_export_' . date('Y-m-d_His') . '.html"');
        }
    }

    /**
     * Export prospects to CSV
     */
    private function exportProspectsToCsv($prospects)
    {
        $headers = ['Customer Name', 'Phone', 'Budget', 'Location', 'Purpose', 'Status', 'Created Date', 'Telecaller', 'Manager'];
        
        $filename = 'prospects_export_' . date('Y-m-d_His') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($prospects as $prospect) {
            fputcsv($handle, [
                $prospect->customer_name,
                $prospect->phone,
                $prospect->budget ?? 'N/A',
                $prospect->preferred_location ?? 'N/A',
                $prospect->purpose ?? 'N/A',
                ucfirst(str_replace('_', ' ', $prospect->verification_status)),
                $prospect->created_at->format('Y-m-d H:i'),
                $prospect->telecaller->name ?? 'N/A',
                $prospect->assignedManager->name ?? 'N/A',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export prospects to PDF
     */
    private function exportProspectsToPdf($prospects)
    {
        $html = view('export.pdf.prospects', compact('prospects'))->render();
        
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $filename = 'prospects_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } elseif (class_exists('PDF')) {
            $pdf = \PDF::loadHTML($html);
            $filename = 'prospects_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } else {
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'inline; filename="prospects_export_' . date('Y-m-d_His') . '.html"');
        }
    }

    /**
     * Export meetings to CSV
     */
    private function exportMeetingsToCsv($meetings)
    {
        $headers = ['Customer Name', 'Phone', 'Meeting Date', 'Status', 'Employee', 'Team Leader', 'Location', 'Remarks'];
        
        $filename = 'meetings_export_' . date('Y-m-d_His') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($meetings as $meeting) {
            fputcsv($handle, [
                $meeting->lead->name ?? $meeting->customer_name ?? 'N/A',
                $meeting->lead->phone ?? $meeting->phone ?? 'N/A',
                $meeting->scheduled_at ? $meeting->scheduled_at->format('Y-m-d H:i') : 'N/A',
                ucfirst($meeting->status),
                $meeting->assignedTo->name ?? $meeting->employee ?? 'N/A',
                $meeting->team_leader ?? 'N/A',
                $meeting->property_address ?? 'N/A',
                $meeting->meeting_notes ?? $meeting->feedback ?? 'N/A',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export meetings to PDF
     */
    private function exportMeetingsToPdf($meetings)
    {
        $html = view('export.pdf.meetings', compact('meetings'))->render();
        
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $filename = 'meetings_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } elseif (class_exists('PDF')) {
            $pdf = \PDF::loadHTML($html);
            $filename = 'meetings_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } else {
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'inline; filename="meetings_export_' . date('Y-m-d_His') . '.html"');
        }
    }

    /**
     * Export site visits to CSV
     */
    private function exportSiteVisitsToCsv($siteVisits)
    {
        $headers = ['Customer Name', 'Phone', 'Visit Date', 'Visit Type', 'Status', 'Assigned To', 'Closer Status', 'Location'];
        
        $filename = 'site_visits_export_' . date('Y-m-d_His') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($siteVisits as $visit) {
            fputcsv($handle, [
                $visit->lead->name ?? $visit->customer_name ?? 'N/A',
                $visit->lead->phone ?? $visit->phone ?? 'N/A',
                $visit->scheduled_at ? $visit->scheduled_at->format('Y-m-d H:i') : 'N/A',
                $visit->lead_type ?? 'N/A',
                ucfirst($visit->status),
                $visit->assignedTo->name ?? 'N/A',
                ucfirst($visit->closer_status ?? 'N/A'),
                $visit->property_address ?? $visit->property_name ?? 'N/A',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export site visits to PDF
     */
    private function exportSiteVisitsToPdf($siteVisits)
    {
        $html = view('export.pdf.site-visits', compact('siteVisits'))->render();
        
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $filename = 'site_visits_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } elseif (class_exists('PDF')) {
            $pdf = \PDF::loadHTML($html);
            $filename = 'site_visits_export_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } else {
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'inline; filename="site_visits_export_' . date('Y-m-d_His') . '.html"');
        }
    }

    /**
     * Get lead field labels
     */
    private function getLeadFieldLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Customer Name',
            'phone' => 'Phone Number',
            'email' => 'Email',
            'status' => 'Status',
            'budget' => 'Budget',
            'preferred_location' => 'Location',
            'source' => 'Lead Source',
            'assigned_to' => 'Assigned To',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'last_contacted_at' => 'Last Contacted',
            'notes' => 'Notes',
            'employee_remark' => 'Employee Remark',
            'manager_remark' => 'Manager Remark',
            'interested_projects' => 'Interested Projects',
            'dead_reason' => 'Dead Reason',
            'dead_at_stage' => 'Dead At Stage',
            'marked_dead_at' => 'Marked Dead Date',
            'marked_dead_by' => 'Marked Dead By',
        ];
    }

    /**
     * Get lead field value
     */
    private function getLeadFieldValue($lead, $field)
    {
        switch ($field) {
            case 'assigned_to':
                return $lead->activeAssignments->first()?->assignedTo->name ?? 'Unassigned';
            case 'status':
                return ucfirst(str_replace('_', ' ', $lead->status));
            case 'created_at':
            case 'updated_at':
            case 'last_contacted_at':
            case 'marked_dead_at':
                return $lead->$field ? $lead->$field->format('Y-m-d H:i') : 'N/A';
            case 'marked_dead_by':
                $user = User::find($lead->marked_dead_by);
                return $user ? $user->name : 'N/A';
            case 'interested_projects':
                // Get all interested projects from all prospects of this lead
                $allProjects = collect();
                foreach ($lead->prospects as $prospect) {
                    if ($prospect->interestedProjects) {
                        $allProjects = $allProjects->merge($prospect->interestedProjects);
                    }
                }
                $uniqueProjects = $allProjects->unique('id')->pluck('name');
                return $uniqueProjects->isNotEmpty() ? $uniqueProjects->implode(', ') : 'No Projects';
            default:
                return $lead->$field ?? 'N/A';
        }
    }

    /**
     * Get date range from filter
     */
    private function getDateRange($range)
    {
        $today = Carbon::today();
        
        switch ($range) {
            case 'today':
                return [
                    'start_date' => $today->startOfDay(),
                    'end_date' => $today->copy()->endOfDay(),
                ];
            case 'this_week':
                return [
                    'start_date' => $today->copy()->startOfWeek(),
                    'end_date' => $today->copy()->endOfWeek(),
                ];
            case 'this_month':
                return [
                    'start_date' => $today->copy()->startOfMonth(),
                    'end_date' => $today->copy()->endOfMonth(),
                ];
            case 'this_year':
                return [
                    'start_date' => $today->copy()->startOfYear(),
                    'end_date' => $today->copy()->endOfYear(),
                ];
            case 'till_date':
            case 'all_time':
            default:
                return null;
        }
    }

    /**
     * Export leads by interested projects
     */
    public function exportByProject(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'interested_projects' => 'required|array|min:1',
            'interested_projects.*' => 'exists:interested_project_names,id',
            'date_range' => 'nullable|string',
        ]);

        $user = auth()->user();
        $projectIds = $request->input('interested_projects');

        // Query leads that have prospects with selected interested projects
        $query = Lead::with([
            'creator',
            'activeAssignments.assignedTo',
            'prospects.interestedProjects'
        ])->whereHas('prospects.interestedProjects', function($q) use ($projectIds) {
            $q->whereIn('interested_project_names.id', $projectIds);
        });

        // Apply role-based filtering
        if ($user->isSalesManager() || $user->isSalesHead()) {
            $teamMemberIds = $user->isSalesHead() 
                ? $user->getAllTeamMemberIds()
                : $user->teamMembers()->pluck('id')->toArray();
            
            if (!empty($teamMemberIds)) {
                $query->whereHas('activeAssignments', function($q) use ($teamMemberIds) {
                    $q->whereIn('assigned_to', $teamMemberIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply date range
        if ($request->has('date_range') && $request->date_range !== 'all_time') {
            $dateRange = $this->getDateRange($request->date_range);
            if ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
        }

        $leads = $query->get();

        if ($leads->isEmpty()) {
            return back()->with('error', 'No leads found matching the selected interested projects.');
        }

        // Limit to 10000 records
        if ($leads->count() > 10000) {
            return back()->with('error', 'Export limit exceeded. Please refine your filters. Maximum 10,000 records allowed.');
        }

        if ($request->format === 'csv') {
            return $this->exportByProjectToCsv($leads);
        } else {
            return $this->exportByProjectToExcel($leads);
        }
    }

    /**
     * Export leads by project to CSV
     */
    private function exportByProjectToCsv($leads)
    {
        $headers = [
            'Lead ID',
            'Customer Name',
            'Phone',
            'Email',
            'Status',
            'Budget',
            'Preferred Location',
            'Source',
            'Assigned To',
            'Interested Projects',
            'Created Date',
            'Updated Date',
        ];

        $filename = 'leads_by_project_export_' . date('Y-m-d_His') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($leads as $lead) {
            // Get all interested projects from all prospects of this lead
            $allProjects = collect();
            foreach ($lead->prospects as $prospect) {
                if ($prospect->interestedProjects) {
                    $allProjects = $allProjects->merge($prospect->interestedProjects);
                }
            }
            $uniqueProjects = $allProjects->unique('id')->pluck('name')->implode(', ');

            $row = [
                $lead->id,
                $lead->name ?? 'N/A',
                $lead->phone ?? 'N/A',
                $lead->email ?? 'N/A',
                ucfirst(str_replace('_', ' ', $lead->status ?? 'N/A')),
                $lead->budget ?? 'N/A',
                $lead->preferred_location ?? 'N/A',
                ucfirst(str_replace('_', ' ', $lead->source ?? 'N/A')),
                $lead->activeAssignments->first()?->assignedTo->name ?? 'Unassigned',
                $uniqueProjects ?: 'No Projects',
                $lead->created_at ? $lead->created_at->format('Y-m-d H:i') : 'N/A',
                $lead->updated_at ? $lead->updated_at->format('Y-m-d H:i') : 'N/A',
            ];
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Export leads by project to Excel
     */
    private function exportByProjectToExcel($leads)
    {
        // For Excel export, we'll create a CSV with .xlsx extension
        // In production, you can use Maatwebsite/Excel package for proper Excel export
        // For now, we'll return CSV with .xlsx extension as a workaround
        
        $headers = [
            'Lead ID',
            'Customer Name',
            'Phone',
            'Email',
            'Status',
            'Budget',
            'Preferred Location',
            'Source',
            'Assigned To',
            'Interested Projects',
            'Created Date',
            'Updated Date',
        ];

        $filename = 'leads_by_project_export_' . date('Y-m-d_His') . '.xlsx';
        
        // Create CSV content (Excel can open CSV files)
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($leads as $lead) {
            // Get all interested projects from all prospects of this lead
            $allProjects = collect();
            foreach ($lead->prospects as $prospect) {
                if ($prospect->interestedProjects) {
                    $allProjects = $allProjects->merge($prospect->interestedProjects);
                }
            }
            $uniqueProjects = $allProjects->unique('id')->pluck('name')->implode(', ');

            $row = [
                $lead->id,
                $lead->name ?? 'N/A',
                $lead->phone ?? 'N/A',
                $lead->email ?? 'N/A',
                ucfirst(str_replace('_', ' ', $lead->status ?? 'N/A')),
                $lead->budget ?? 'N/A',
                $lead->preferred_location ?? 'N/A',
                ucfirst(str_replace('_', ' ', $lead->source ?? 'N/A')),
                $lead->activeAssignments->first()?->assignedTo->name ?? 'Unassigned',
                $uniqueProjects ?: 'No Projects',
                $lead->created_at ? $lead->created_at->format('Y-m-d H:i') : 'N/A',
                $lead->updated_at ? $lead->updated_at->format('Y-m-d H:i') : 'N/A',
            ];
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Return as Excel file (CSV format that Excel can open)
        return response($csv)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }
}

