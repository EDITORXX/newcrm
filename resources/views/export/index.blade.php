@extends('layouts.app')

@section('title', 'Export Leads - Base CRM')
@section('page-title', 'Export Leads')

@push('styles')
<style>
    .export-template-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .export-template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .export-template-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        cursor: pointer;
    }
    
    .export-template-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #063A1C;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .export-template-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }
    
    .export-template-options {
        display: none;
        padding-top: 1rem;
        border-top: 1px solid #E5DED4;
        margin-top: 1rem;
    }
    
    .export-template-options.active {
        display: block;
    }
    
    .export-format-options {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .export-format-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .export-format-option label {
        color: #063A1C;
        font-weight: 500;
        cursor: pointer;
    }
    
    .export-format-option input[type="radio"] {
        width: 18px;
        height: 18px;
    }
    
    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .checkbox-group::-webkit-scrollbar {
        width: 8px;
    }
    
    .checkbox-group::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .checkbox-group::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .checkbox-group::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 8px 4px;
        border-radius: 6px;
        transition: background-color 0.2s;
    }
    
    .checkbox-item:hover {
        background-color: #f9fafb;
    }
    
    .checkbox-item input[type="checkbox"] {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        min-height: 20px !important;
        max-width: 20px !important;
        max-height: 20px !important;
        cursor: pointer;
        accent-color: #16a34a;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        position: relative;
        flex-shrink: 0;
        margin: 0;
        padding: 0;
    }
    
    .checkbox-item input[type="checkbox"]:checked {
        background-color: #16a34a;
        border-color: #16a34a;
    }
    
    .checkbox-item input[type="checkbox"]:checked::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
        line-height: 1;
    }
    
    .checkbox-item input[type="checkbox"]:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    
    .checkbox-item label {
        cursor: pointer;
        user-select: none;
        color: #063A1C;
        font-weight: 500;
        font-size: 14px;
        flex: 1;
        margin: 0;
    }
    
    .btn-export-now {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s;
    }
    
    .btn-export-now:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .custom-export-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .info-box {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
        color: #1565c0;
    }
    
    .filter-section {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    @media (max-width: 1024px) {
        .filter-section {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .filter-section {
            grid-template-columns: 1fr;
        }
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 600;
        font-size: 14px;
        color: #063A1C;
    }
    
    .form-group-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .form-group-header label {
        margin-bottom: 0;
        font-weight: 600;
        font-size: 14px;
        color: #063A1C;
    }
    
    .filter-action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-filter-action {
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-filter-action.select-all {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
    }
    
    .btn-filter-action.select-all:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-filter-action.deselect-all {
        background: #6c757d;
        color: white;
    }
    
    .btn-filter-action.deselect-all:hover {
        background: #5a6268;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-group select:not([multiple]),
    .form-group input {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid #E5DED4;
        border-radius: 8px;
        font-size: 14px;
        background-color: white;
        color: #063A1C;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23063A1C' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 12px;
        padding-right: 36px;
    }
    
    .form-group select:not([multiple]):hover {
        border-color: #16a34a;
    }
    
    .form-group select:not([multiple]):focus,
    .form-group input:focus {
        outline: none;
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    
    .form-group select option {
        background-color: white;
        color: #063A1C;
        padding: 10px;
    }
    
    /* Ensure all selects have proper styling */
    select {
        background-color: white !important;
        color: #063A1C !important;
    }
    
    select option {
        background-color: white !important;
        color: #063A1C !important;
        padding: 8px;
    }
    
    /* Fix for multiple select dropdowns */
    select[multiple] {
        background-color: white !important;
        color: #063A1C !important;
    }
    
    select[multiple] option {
        background-color: white !important;
        color: #063A1C !important;
        padding: 8px;
    }
    
    select[multiple] option:checked {
        background-color: #e3f2fd !important;
        color: #063A1C !important;
    }
    
    .checkbox-group-container {
        border: 2px solid #E5DED4;
        border-radius: 8px;
        padding: 14px;
        background: white;
        max-height: 220px;
        overflow-y: auto;
    }
    
    .checkbox-group-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .checkbox-group-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .checkbox-group-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .checkbox-group-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .multi-select-wrapper {
        position: relative;
    }
    
    .multi-select-wrapper select[multiple] {
        min-height: 140px;
        padding: 12px;
        border: 2px solid #E5DED4;
        border-radius: 8px;
        background-color: white;
        color: #063A1C;
        font-size: 14px;
        line-height: 1.8;
    }
    
    .multi-select-wrapper select[multiple]:focus {
        outline: none;
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    
    .multi-select-hint {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
        display: block;
        font-style: italic;
    }
    
    .filter-group-title {
        font-size: 16px;
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #E5DED4;
    }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('error'))
        <div class="alert alert-danger" style="background: #fee; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33;">
            {{ session('error') }}
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success" style="background: #efe; color: #3c3; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #3c3;">
            {{ session('success') }}
        </div>
    @endif

    <h2 style="font-size: 24px; font-weight: 700; color: #063A1C; margin-bottom: 2rem;">Export Leads</h2>

    <!-- Quick Export Templates -->
    <div class="mb-5">
        <h3 style="font-size: 20px; font-weight: 600; color: #063A1C; margin-bottom: 1.5rem;">Quick Export Templates</h3>
        
        <!-- Export Prospects -->
        <div class="export-template-card">
            <div class="export-template-header" onclick="toggleTemplate('prospects')">
                <div class="export-template-title">
                    <div class="export-template-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <span>Export All Prospects</span>
                </div>
                <i class="fas fa-chevron-down" id="prospects-chevron"></i>
            </div>
            <div class="export-template-options" id="prospects-options">
                <form action="{{ route('export.prospects') }}" method="POST" id="export-prospects-form">
                    @csrf
                    <div class="export-format-options">
                        <div class="export-format-option">
                            <input type="radio" name="format" value="csv" id="prospects-csv" checked>
                            <label for="prospects-csv" style="color: #063A1C;">CSV</label>
                        </div>
                        <div class="export-format-option">
                            <input type="radio" name="format" value="pdf" id="prospects-pdf">
                            <label for="prospects-pdf" style="color: #063A1C;">PDF</label>
                        </div>
                    </div>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="pending_verification" id="prospect-pending">
                            <label for="prospect-pending">Pending Verification</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="verified" id="prospect-verified">
                            <label for="prospect-verified">Verified</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="approved" id="prospect-approved">
                            <label for="prospect-approved">Approved</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="rejected" id="prospect-rejected">
                            <label for="prospect-rejected">Rejected</label>
                        </div>
                    </div>
                    <div class="filter-section">
                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range">
                                <option value="all_time">All Time</option>
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="this_year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-export-now">Export Now</button>
                </form>
            </div>
        </div>

        <!-- Export Meetings -->
        <div class="export-template-card">
            <div class="export-template-header" onclick="toggleTemplate('meetings')">
                <div class="export-template-title">
                    <div class="export-template-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span>Export All Meetings</span>
                </div>
                <i class="fas fa-chevron-down" id="meetings-chevron"></i>
            </div>
            <div class="export-template-options" id="meetings-options">
                <form action="{{ route('export.meetings') }}" method="POST" id="export-meetings-form">
                    @csrf
                    <div class="export-format-options">
                        <div class="export-format-option">
                            <input type="radio" name="format" value="csv" id="meetings-csv" checked>
                            <label for="meetings-csv">CSV</label>
                        </div>
                        <div class="export-format-option">
                            <input type="radio" name="format" value="pdf" id="meetings-pdf">
                            <label for="meetings-pdf">PDF</label>
                        </div>
                    </div>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="scheduled" id="meeting-scheduled">
                            <label for="meeting-scheduled">Scheduled</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="completed" id="meeting-completed">
                            <label for="meeting-completed">Completed</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="rescheduled" id="meeting-rescheduled">
                            <label for="meeting-rescheduled">Rescheduled</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="cancelled" id="meeting-cancelled">
                            <label for="meeting-cancelled">Cancelled</label>
                        </div>
                    </div>
                    <div class="filter-section">
                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range">
                                <option value="all_time">All Time</option>
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="this_year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-export-now">Export Now</button>
                </form>
            </div>
        </div>

        <!-- Export Site Visits -->
        <div class="export-template-card">
            <div class="export-template-header" onclick="toggleTemplate('visits')">
                <div class="export-template-title">
                    <div class="export-template-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <span>Export All Site Visits</span>
                </div>
                <i class="fas fa-chevron-down" id="visits-chevron"></i>
            </div>
            <div class="export-template-options" id="visits-options">
                <form action="{{ route('export.site-visits') }}" method="POST" id="export-visits-form">
                    @csrf
                    <div class="export-format-options">
                        <div class="export-format-option">
                            <input type="radio" name="format" value="csv" id="visits-csv" checked>
                            <label for="visits-csv">CSV</label>
                        </div>
                        <div class="export-format-option">
                            <input type="radio" name="format" value="pdf" id="visits-pdf">
                            <label for="visits-pdf">PDF</label>
                        </div>
                    </div>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="visit_type[]" value="New Visit" id="visit-new">
                            <label for="visit-new">New Visit</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="visit_type[]" value="Revisited" id="visit-revisit">
                            <label for="visit-revisit">Revisit</label>
                        </div>
                    </div>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="scheduled" id="visit-scheduled">
                            <label for="visit-scheduled">Scheduled</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" name="status[]" value="completed" id="visit-completed">
                            <label for="visit-completed">Completed</label>
                        </div>
                    </div>
                    <div class="filter-section">
                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range">
                                <option value="all_time">All Time</option>
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="this_year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-export-now">Export Now</button>
                </form>
            </div>
        </div>

        <!-- Export Closed Leads -->
        <div class="export-template-card">
            <div class="export-template-header" onclick="toggleTemplate('closed')">
                <div class="export-template-title">
                    <div class="export-template-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <span>Export Closed Leads</span>
                </div>
                <i class="fas fa-chevron-down" id="closed-chevron"></i>
            </div>
            <div class="export-template-options" id="closed-options">
                <form action="{{ route('export.closed-leads') }}" method="POST" id="export-closed-form">
                    @csrf
                    <div class="export-format-options">
                        <div class="export-format-option">
                            <input type="radio" name="format" value="csv" id="closed-csv" checked>
                            <label for="closed-csv">CSV</label>
                        </div>
                        <div class="export-format-option">
                            <input type="radio" name="format" value="pdf" id="closed-pdf">
                            <label for="closed-pdf">PDF</label>
                        </div>
                    </div>
                    <div class="filter-section">
                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range">
                                <option value="all_time">All Time</option>
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="this_year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-export-now">Export Now</button>
                </form>
            </div>
        </div>

        <!-- Export Dead Leads -->
        <div class="export-template-card">
            <div class="export-template-header" onclick="toggleTemplate('dead')">
                <div class="export-template-title">
                    <div class="export-template-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <span>Export Dead Leads</span>
                </div>
                <i class="fas fa-chevron-down" id="dead-chevron"></i>
            </div>
            <div class="export-template-options" id="dead-options">
                <form action="{{ route('export.dead-leads') }}" method="POST" id="export-dead-form">
                    @csrf
                    <div class="export-format-options">
                        <div class="export-format-option">
                            <input type="radio" name="format" value="csv" id="dead-csv" checked>
                            <label for="dead-csv">CSV</label>
                        </div>
                        <div class="export-format-option">
                            <input type="radio" name="format" value="pdf" id="dead-pdf">
                            <label for="dead-pdf">PDF</label>
                        </div>
                    </div>
                    <div class="filter-section">
                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range">
                                <option value="all_time">All Time</option>
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="this_year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-export-now">Export Now</button>
                </form>
            </div>
        </div>

        <!-- Export By Project -->
        <div class="export-template-card">
            <div class="export-template-header" onclick="toggleTemplate('by-project')">
                <div class="export-template-title">
                    <div class="export-template-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <span>By Project</span>
                </div>
                <i class="fas fa-chevron-down" id="by-project-chevron"></i>
            </div>
            <div class="export-template-options" id="by-project-options">
                <form action="{{ route('export.by-project') }}" method="POST" id="export-by-project-form">
                    @csrf
                    <div class="export-format-options">
                        <div class="export-format-option">
                            <input type="radio" name="format" value="csv" id="by-project-csv" checked>
                            <label for="by-project-csv" style="color: #063A1C;">CSV</label>
                        </div>
                        <div class="export-format-option">
                            <input type="radio" name="format" value="xlsx" id="by-project-xlsx">
                            <label for="by-project-xlsx" style="color: #063A1C;">Excel (XLSX)</label>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #063A1C;">Select Interested Projects <span style="color: red;">*</span></label>
                        <select name="interested_projects[]" multiple required style="width: 100%; min-height: 150px; padding: 10px 12px; border: 2px solid #E5DED4; border-radius: 8px; background-color: white; color: #063A1C;">
                            @foreach($interestedProjectNames as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                        <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Hold Ctrl/Cmd to select multiple projects</small>
                    </div>
                    <div class="filter-section">
                        <div class="form-group">
                            <label>Date Range</label>
                            <select name="date_range">
                                <option value="all_time">All Time</option>
                                <option value="today">Today</option>
                                <option value="this_week">This Week</option>
                                <option value="this_month" selected>This Month</option>
                                <option value="this_year">This Year</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-export-now">Export Now</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Export Section -->
    <div class="custom-export-section">
        <h3 style="font-size: 20px; font-weight: 600; color: #063A1C; margin-bottom: 1.5rem;">Custom Export</h3>
        
        <div class="info-box">
            <strong>Note:</strong> Export will use current filters (Status, Date Range, User, Type). Make sure to apply filters before exporting.
        </div>

        <form action="{{ route('export.leads') }}" method="POST" id="custom-export-form">
            @csrf
            
            <!-- Format Selection -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #063A1C;">Export Format:</label>
                <div class="export-format-options">
                    <div class="export-format-option">
                        <input type="radio" name="format" value="csv" id="custom-csv" checked>
                        <label for="custom-csv">CSV</label>
                    </div>
                    <div class="export-format-option">
                        <input type="radio" name="format" value="pdf" id="custom-pdf">
                        <label for="custom-pdf">PDF</label>
                    </div>
                </div>
            </div>

            <!-- Field Selection -->
            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <label style="font-weight: 500; color: #063A1C;">Select Fields to Export:</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn-export-now" onclick="selectAllFields()" style="padding: 8px 16px; font-size: 14px;">Select All</button>
                        <button type="button" class="btn-export-now" onclick="deselectAllFields()" style="padding: 8px 16px; font-size: 14px; background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);">Deselect All</button>
                    </div>
                </div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="id" id="field-id">
                        <label for="field-id">ID</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="name" id="field-name" checked>
                        <label for="field-name">Customer Name</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="phone" id="field-phone" checked>
                        <label for="field-phone">Phone Number</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="email" id="field-email">
                        <label for="field-email">Email</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="status" id="field-status">
                        <label for="field-status">Status</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="budget" id="field-budget">
                        <label for="field-budget">Budget</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="preferred_location" id="field-location">
                        <label for="field-location">Location</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="source" id="field-source">
                        <label for="field-source">Lead Source</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="assigned_to" id="field-assigned">
                        <label for="field-assigned">Assigned To</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="created_at" id="field-created" checked>
                        <label for="field-created">Created Date</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="updated_at" id="field-updated">
                        <label for="field-updated">Updated Date</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="last_contacted_at" id="field-contacted">
                        <label for="field-contacted">Last Contacted</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="notes" id="field-notes">
                        <label for="field-notes">Notes</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="employee_remark" id="field-employee-remark">
                        <label for="field-employee-remark">Employee Remark</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="manager_remark" id="field-manager-remark">
                        <label for="field-manager-remark">Manager Remark</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" name="fields[]" value="interested_projects" id="field-interested-projects">
                        <label for="field-interested-projects">Interested Projects</label>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div style="margin-bottom: 2rem;">
                <h4 class="filter-group-title">
                    <i class="fas fa-filter" style="margin-right: 8px;"></i>Filter Options
                </h4>
                
                <!-- Dropdown Filters Row -->
                <div class="filter-section" style="margin-bottom: 2rem;">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-calendar-alt" style="margin-right: 6px; color: #16a34a;"></i>Date Range
                        </label>
                        <select name="date_range">
                            <option value="all_time">All Time</option>
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user-tie" style="margin-right: 6px; color: #16a34a;"></i>Assigned To
                        </label>
                        <select name="user_id">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Checkbox Filters - Vertical Stack -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Status -->
                    <div class="form-group">
                        <div class="form-group-header">
                            <label>
                                <i class="fas fa-info-circle" style="margin-right: 6px; color: #16a34a;"></i>Status
                            </label>
                            <div class="filter-action-buttons">
                                <button type="button" onclick="selectAllStatus()" class="btn-filter-action select-all">Select All</button>
                                <button type="button" onclick="deselectAllStatus()" class="btn-filter-action deselect-all">Deselect All</button>
                            </div>
                        </div>
                        <div class="checkbox-group">
                            @foreach($statuses as $status)
                                <div class="checkbox-item">
                                    <input type="checkbox" name="status[]" value="{{ $status }}" id="status-{{ $status }}">
                                    <label for="status-{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Interested Projects -->
                    <div class="form-group">
                        <div class="form-group-header">
                            <label>
                                <i class="fas fa-building" style="margin-right: 6px; color: #16a34a;"></i>Interested Projects
                            </label>
                            <div class="filter-action-buttons">
                                <button type="button" onclick="selectAllInterestedProjects()" class="btn-filter-action select-all">Select All</button>
                                <button type="button" onclick="deselectAllInterestedProjects()" class="btn-filter-action deselect-all">Deselect All</button>
                            </div>
                        </div>
                        <div class="checkbox-group">
                            @foreach($interestedProjectNames as $project)
                                <div class="checkbox-item">
                                    <input type="checkbox" name="interested_projects[]" value="{{ $project->id }}" id="interested-project-{{ $project->id }}">
                                    <label for="interested-project-{{ $project->id }}">{{ $project->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Lead Type -->
                    <div class="form-group">
                        <div class="form-group-header">
                            <label>
                                <i class="fas fa-tags" style="margin-right: 6px; color: #16a34a;"></i>Lead Type
                            </label>
                            <div class="filter-action-buttons">
                                <button type="button" onclick="selectAllLeadType()" class="btn-filter-action select-all">Select All</button>
                                <button type="button" onclick="deselectAllLeadType()" class="btn-filter-action deselect-all">Deselect All</button>
                            </div>
                        </div>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" name="lead_type[]" value="prospect" id="lead-type-prospect">
                                <label for="lead-type-prospect">Prospect</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="lead_type[]" value="meeting" id="lead-type-meeting">
                                <label for="lead-type-meeting">Meeting</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="lead_type[]" value="visit" id="lead-type-visit">
                                <label for="lead-type-visit">Site Visit</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="lead_type[]" value="revisit" id="lead-type-revisit">
                                <label for="lead-type-revisit">Revisit</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="lead_type[]" value="closer" id="lead-type-closer">
                                <label for="lead-type-closer">Closer</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('leads.index') }}" style="padding: 10px 24px; background: #6c757d; color: white; border-radius: 8px; text-decoration: none; display: inline-block;">Cancel</a>
                <button type="submit" class="btn-export-now">Export</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleTemplate(templateId) {
        const options = document.getElementById(templateId + '-options');
        const chevron = document.getElementById(templateId + '-chevron');
        
        if (options.classList.contains('active')) {
            options.classList.remove('active');
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        } else {
            // Close all other templates
            document.querySelectorAll('.export-template-options').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.export-template-header i.fa-chevron-up').forEach(el => {
                el.classList.remove('fa-chevron-up');
                el.classList.add('fa-chevron-down');
            });
            
            options.classList.add('active');
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        }
    }

    function selectAllFields() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="fields[]"]').forEach(cb => {
            cb.checked = true;
        });
    }

    function deselectAllFields() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="fields[]"]').forEach(cb => {
            cb.checked = false;
        });
    }

    function selectAllStatus() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="status[]"]').forEach(cb => {
            cb.checked = true;
        });
    }

    function deselectAllStatus() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="status[]"]').forEach(cb => {
            cb.checked = false;
        });
    }

    function selectAllLeadType() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="lead_type[]"]').forEach(cb => {
            cb.checked = true;
        });
    }

    function deselectAllLeadType() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="lead_type[]"]').forEach(cb => {
            cb.checked = false;
        });
    }

    function selectAllInterestedProjects() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="interested_projects[]"]').forEach(cb => {
            cb.checked = true;
        });
    }

    function deselectAllInterestedProjects() {
        document.querySelectorAll('#custom-export-form input[type="checkbox"][name="interested_projects[]"]').forEach(cb => {
            cb.checked = false;
        });
    }

    // Validate form before submit
    document.getElementById('custom-export-form')?.addEventListener('submit', function(e) {
        const checkedFields = document.querySelectorAll('#custom-export-form input[type="checkbox"][name="fields[]"]:checked');
        if (checkedFields.length === 0) {
            e.preventDefault();
            alert('Please select at least one field to export.');
            return false;
        }
    });
</script>
@endpush

