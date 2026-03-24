@extends('sales-manager.layout')

@section('title', 'Tasks - Senior Manager')
@section('page-title', 'Tasks')

@push('styles')
<style>
    .tasks-container {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        width: 100%;
        box-sizing: border-box;
        max-width: 100%;
        overflow-x: hidden;
    }
    .filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filter-btn {
        padding: 10px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        background: white;
        color: #063A1C;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .filter-btn:hover {
        border-color: #205A44;
        color: #205A44;
    }
    .filter-btn.active {
        background: #205A44;
        color: white;
        border-color: #205A44;
    }
    .btn-remove-overdue {
        padding: 10px 20px;
        border: 2px solid #ef4444;
        border-radius: 8px;
        background: #ef4444;
        color: white;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-remove-overdue:hover {
        background: #dc2626;
        border-color: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    .btn-remove-overdue:disabled {
        background: #d1d5db;
        border-color: #d1d5db;
        cursor: not-allowed;
        transform: none;
    }
    .tasks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    @media (max-width: 768px) {
        .tasks-container {
            padding: 12px !important;
        }
        
        .tasks-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px !important;
        }
        
        .task-card {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
            padding: 12px !important;
            border-radius: 10px !important;
            margin-bottom: 0 !important;
        }
        
        /* Improve card appearance - less dense */
        .task-card.overdue {
            border-width: 2px !important;
            border-color: #fca5a5 !important;
            background: #fff5f5 !important;
        }
        
        /* Task header - more compact */
        .task-header {
            margin-bottom: 10px !important;
            padding-bottom: 10px !important;
        }
        
        .task-avatar {
            width: 40px !important;
            height: 40px !important;
            font-size: 16px !important;
            margin-right: 8px !important;
        }
        
        .task-name {
            font-size: 14px !important;
            font-weight: 600 !important;
            line-height: 1.3 !important;
        }
        
        /* Badges - smaller and cleaner */
        .overdue-badge {
            padding: 4px 8px !important;
            font-size: 9px !important;
            margin-bottom: 4px !important;
            display: inline-block !important;
        }
        
        .status-badge {
            padding: 3px 8px !important;
            font-size: 9px !important;
            margin-top: 4px !important;
        }
        
        /* Task info - more compact */
        .task-info {
            margin-bottom: 10px !important;
        }
        
        .task-info-row {
            font-size: 11px !important;
            margin-bottom: 6px !important;
            gap: 6px !important;
        }
        
        .task-info-row i {
            width: 12px !important;
            font-size: 11px !important;
        }
        
        /* Action buttons - larger and more touch-friendly */
        .task-actions {
            margin-top: 10px !important;
            padding-top: 10px !important;
            gap: 6px !important;
            flex-direction: column !important;
        }
        
        .task-action-btn {
            width: 100% !important;
            padding: 10px 8px !important;
            font-size: 11px !important;
            border-radius: 6px !important;
            min-height: 36px !important;
        }
        
        .task-action-btn i {
            font-size: 12px !important;
        }
        
        .tasks-container {
            padding: 12px !important;
        }
        .filter-bar {
            flex-direction: row;
            gap: 8px;
            align-items: center;
        }
        
        /* Hide desktop buttons on mobile */
        .filter-buttons-desktop {
            display: none !important;
        }
        
        /* Show dropdowns on mobile - 50% each */
        .filter-dropdowns-mobile {
            display: flex !important;
            flex: 1;
            gap: 8px;
            min-width: 0;
            flex-wrap: wrap;
        }

        .task-type-filter-mobile {
            display: flex !important;
        }
        
        .task-filter-select,
        .date-filter-select {
            flex: 1;
            width: 50%;
            padding: 10px 16px;
            border: 2px solid #205A44;
            border-radius: 8px;
            background: white;
            color: #063A1C;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23063A1C' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }
        
        .task-filter-select:focus,
        .date-filter-select:focus {
            outline: none;
            border-color: #063A1C;
            box-shadow: 0 0 0 3px rgba(6, 58, 28, 0.1);
        }
        
        /* Custom date picker on mobile */
        #customDatePicker {
            width: 100%;
            margin-top: 8px;
        }
        
        .filter-btn {
            display: none;
        }
        
        .btn-remove-overdue {
            width: auto;
            margin-left: 8px;
            padding: 10px 16px;
            font-size: 12px;
            justify-content: center;
        }
    }
    
    /* Desktop: Show buttons, hide mobile dropdowns */
    @media (min-width: 769px) {
        .filter-buttons-desktop {
            display: flex !important;
        }
        .filter-dropdowns-mobile {
            display: none !important;
        }
        
        /* Desktop date filter styles */
        .date-filter-desktop {
            display: block;
        }
        
        .date-filter-select {
            padding: 10px 16px;
            border: 2px solid #205A44;
            border-radius: 8px;
            background: white;
            color: #063A1C;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23063A1C' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
            min-width: 150px;
        }
        
        .date-filter-select:focus {
            outline: none;
            border-color: #063A1C;
            box-shadow: 0 0 0 3px rgba(6, 58, 28, 0.1);
        }
        
        #customDatePicker {
            margin-left: 8px;
            padding: 8px 12px;
            border: 2px solid #205A44;
            border-radius: 8px;
            font-size: 14px;
        }
    }
    
    .task-card {
        padding: 16px;
    }
        .task-card h3 {
            font-size: 16px !important;
        }
        .task-card p {
            font-size: 13px !important;
        }
        /* Modal responsive */
        .modal-content {
            width: 95% !important;
            max-width: 95% !important;
            margin: 10px auto !important;
            padding: 16px !important;
        }
        .modal-header h2 {
            font-size: 18px !important;
        }
        /* Form inputs responsive */
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            font-size: 14px;
            margin-bottom: 6px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
        }
        /* Form buttons responsive */
        .form-actions {
            flex-direction: column;
            gap: 10px;
        }
        .form-actions button {
            width: 100%;
            padding: 12px;
        }
    }
    .task-card {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
    }
    .task-card:hover {
        border-color: #205A44;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    .task-card.overdue {
        border-color: #ef4444;
        border-width: 3px;
        background: #fef2f2;
    }
    .task-actions {
        display: flex;
        gap: 8px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #f0f0f0;
    }
    .task-action-btn {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .task-action-btn i {
        font-size: 14px;
    }
    .btn-call {
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        color: white;
    }
    .btn-call:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(32, 90, 68, 0.3);
    }
    .btn-whatsapp {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: white;
    }
    .btn-whatsapp:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    }
    .btn-view-detail {
        background: #e0e0e0;
        color: #063A1C;
    }
    .btn-view-detail:hover {
        background: #d0d0d0;
        transform: translateY(-1px);
    }
    .task-header {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .task-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: 700;
        margin-right: 12px;
    }
    .task-name {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin: 0;
    }
    .task-info {
        margin-bottom: 12px;
    }
    .task-info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 14px;
        color: #063A1C;
    }
    .task-info-row i {
        color: #205A44;
        width: 16px;
    }
    .overdue-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #ef4444;
        color: white;
        margin-top: 8px;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        margin-top: 8px;
    }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-in_progress { background: #dbeafe; color: #1e40af; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .loading-state, .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #B3B5B4;
    }
    
    /* Hide empty states on mobile */
    @media (max-width: 767px) {
        .empty-state {
            display: none !important;
        }
    }
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }
    .modal.active {
        display: flex;
    }
    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #205A44;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        display: inline-block;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        margin: auto;
        position: relative;
    }
    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            padding: 20px;
            margin: 20px auto;
        }
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 24px;
        color: #063A1C;
    }
    .close-modal {
        background: none;
        border: none;
        font-size: 28px;
        color: #B3B5B4;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .close-modal:hover {
        color: #063A1C;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #063A1C;
    }
    .form-group label .required {
        color: #ef4444;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        background: #ffffff;
        color: #063A1C;
        box-sizing: border-box;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #205A44;
    }
    .form-footer {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 2px solid #f0f0f0;
    }
    .btn-save {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    .btn-cancel {
        flex: 1;
        padding: 12px;
        background: #e0e0e0;
        color: #063A1C;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-cancel:hover {
        background: #d0d0d0;
    }
    .btn-verify {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-verify:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-verify:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-reject {
        flex: 1;
        padding: 12px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-reject:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    .btn-reject:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-cnp {
        background: #f59e0b;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-cnp:hover {
        background: #d97706;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
    }
    .btn-cnp:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .time-option-btn {
        transition: all 0.2s ease;
    }
    .time-option-btn:hover {
        border-color: #f59e0b !important;
        background: #fff9e6 !important;
        transform: translateY(-1px);
    }
    .time-option-btn.selected {
        border-color: #f59e0b !important;
        background: #f59e0b !important;
        color: white !important;
        font-weight: 600;
    }
    .form-group input[readonly],
    .form-group select[readonly],
    .form-group textarea[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    /* Project Tags Styling (YouTube-style) */
    .project-tags-wrapper {
        margin-top: 8px;
    }
    .project-tags-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background: #fafafa;
        min-height: 60px;
    }
    .project-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        transition: all 0.2s ease;
        user-select: none;
        position: relative;
    }
    .project-tag:hover {
        border-color: #205A44;
        background: #f0f7f4;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(32, 90, 68, 0.1);
    }
    .project-tag.selected {
        background: #205A44;
        border-color: #205A44;
        color: white;
    }
    .project-tag.selected:hover {
        background: #15803d;
        border-color: #15803d;
    }
    .project-tag-text {
        white-space: nowrap;
    }
    .project-tag-check {
        display: none;
        font-size: 12px;
    }
    .project-tag.selected .project-tag-check {
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="tasks-container">
    <!-- Filter Bar -->
    <div class="filter-bar" style="display: flex; align-items: center; justify-content: space-between;">
        <!-- Desktop: Button Filters -->
        <div class="filter-buttons-desktop" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <button class="filter-btn active" data-status="all">All Tasks</button>
            <button class="filter-btn" data-status="pending">Pending</button>
            <button class="filter-btn" data-status="overdue">Overdue</button>
            <button class="filter-btn" data-status="rescheduled">Rescheduled</button>
            <button class="filter-btn" data-status="completed">Completed</button>

            <div class="task-type-filter-desktop" style="margin-left: 12px;">
                <select id="taskTypeFilterDesktop" class="date-filter-select">
                    <option value="all">All Types</option>
                    <option value="meeting">Meeting</option>
                    <option value="site_visit">Site Visit</option>
                    <option value="prospect">Prospect</option>
                    <option value="closer">Closer</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <!-- Desktop Date Filter -->
            <div class="date-filter-desktop" style="margin-left: 12px;">
                <select id="dateFilterDropdownDesktop" class="date-filter-select">
                    <option value="all">All Dates</option>
                    <option value="today">Today</option>
                    <option value="tomorrow">Tomorrow</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="custom">Custom Date</option>
                </select>
            </div>
        </div>
        
        <!-- Mobile: Dropdown Filters (50% each) -->
        <div class="filter-dropdowns-mobile" style="display: none; flex: 1; gap: 8px; width: 100%;">
            <select id="taskFilterDropdown" class="task-filter-select" style="flex: 1; width: 50%;">
                <option value="all">All Tasks</option>
                <option value="pending">Pending</option>
                <option value="overdue">Overdue</option>
                <option value="rescheduled">Rescheduled</option>
                <option value="completed">Completed</option>
            </select>
            <select id="dateFilterDropdown" class="date-filter-select" style="flex: 1;">
                <option value="all">All Dates</option>
                <option value="today">Today</option>
                <option value="tomorrow">Tomorrow</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="custom">Custom Date</option>
            </select>
        </div>

        <div class="task-type-filter-mobile" style="display: none; flex: 1; width: 100%; margin-top: 8px;">
            <select id="taskTypeFilterMobile" class="task-filter-select" style="width: 100%;">
                <option value="all">All Types</option>
                <option value="meeting">Meeting</option>
                <option value="site_visit">Site Visit</option>
                <option value="prospect">Prospect</option>
                <option value="closer">Closer</option>
                <option value="other">Other</option>
            </select>
        </div>
        
        <!-- Custom Date Picker (hidden by default) -->
        <input type="date" id="customDatePicker" style="display: none; margin-left: 8px; padding: 8px 12px; border: 2px solid #205A44; border-radius: 8px; font-size: 14px;">
        
        <button id="removeAllOverdueBtn" class="btn-remove-overdue" onclick="removeAllOverdueTasks()" style="display: none;">
            <i class="fas fa-trash-alt"></i>
            Remove All Overdue
        </button>
    </div>

    <!-- Tasks Grid -->
    <div id="tasksGrid" class="tasks-grid">
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading tasks...</p>
        </div>
    </div>
</div>

<!-- Step 1: Verify/Reject Prompt Modal -->
<div id="verifyRejectPromptModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Verify, Reject, or CNP Prospect</h3>
            <button class="close-modal" onclick="cancelVerifyRejectPrompt()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="padding: 20px; text-align: center;">
                <p style="font-size: 16px; color: #333; margin-bottom: 30px;">
                    Please choose an action for this prospect:
                </p>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                    <button type="button" class="btn-verify" onclick="proceedToVerifyForm()" style="padding: 12px 24px; background: #205A44; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>Verify
                    </button>
                    <button type="button" class="btn-reject" onclick="proceedToReject()" style="padding: 12px 24px; background: #dc3545; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-times-circle" style="margin-right: 8px;"></i>Reject
                    </button>
                    <button type="button" class="btn-cnp" onclick="proceedToCNP()" style="padding: 12px 24px; background: #f59e0b; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-phone-slash" style="margin-right: 8px;"></i>CNP
                    </button>
                </div>
                <p style="font-size: 12px; color: #666; margin-top: 16px; font-style: italic;">
                    CNP: Call Not Picked - Will create a retry task for 2 hours later
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Full Lead Requirement Form Modal (shown after Verify clicked) -->
<div id="managerLeadRequirementFormModal" class="modal">
    <div class="modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3>Lead Requirement Form - Verify Prospect</h3>
            <button class="close-btn" onclick="cancelManagerLeadRequirementForm()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="managerLeadFormContainer">
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner" style="display: inline-block;"></div>
                    <p style="margin-top: 15px; color: #666;">Loading form...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal (shown after Reject clicked) -->
<div id="rejectReasonModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Reject Prospect</h3>
            <button class="close-modal" onclick="cancelRejectReasonModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="rejectReasonInput">Rejection Reason <span class="required">*</span></label>
                <textarea id="rejectReasonInput" rows="4" required placeholder="Enter reason for rejection..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></textarea>
            </div>
            <div class="form-footer" style="margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn-cancel" onclick="cancelRejectReasonModal()">Cancel</button>
                <button type="button" class="btn-reject" onclick="submitRejectProspect()">Confirm Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- CNP Time Selection Modal -->
<div id="cnpTimeSelectionModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Select Retry Time for CNP</h3>
            <button class="close-modal" onclick="cancelCnpTimeSelection()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="padding: 20px;">
                <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
                    Choose when to retry this call:
                </p>
                
                <!-- Quick Time Options -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
                    <button type="button" class="time-option-btn" onclick="selectCnpTime(15, event)" data-minutes="15" style="padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #333; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                        15 Minutes
                    </button>
                    <button type="button" class="time-option-btn" onclick="selectCnpTime(30, event)" data-minutes="30" style="padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #333; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                        30 Minutes
                    </button>
                    <button type="button" class="time-option-btn" onclick="selectCnpTime(60, event)" data-minutes="60" style="padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #333; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                        1 Hour
                    </button>
                    <button type="button" class="time-option-btn" onclick="selectCnpTime(120, event)" data-minutes="120" style="padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #333; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                        2 Hours
                    </button>
                </div>
                
                <!-- Custom Option -->
                <div style="margin-bottom: 20px;">
                    <button type="button" class="time-option-btn" onclick="showCustomTimePicker()" id="customTimeOptionBtn" style="width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; background: white; color: #333; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                        Custom Date & Time
                    </button>
                </div>
                
                <!-- Custom Date-Time Picker (hidden by default) -->
                <div id="customTimePickerContainer" style="display: none; padding: 16px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                            <strong>Date</strong> <span style="color: #d32f2f;">*</span>
                        </label>
                        <input type="date" 
                               id="cnpCustomDate" 
                               min=""
                               style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                            <strong>Time</strong> <span style="color: #d32f2f;">*</span>
                        </label>
                        <input type="time" 
                               id="cnpCustomTime"
                               style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>
                    <small style="display: block; margin-top: 8px; color: #666; font-size: 12px;">
                        Select a future date and time for the retry call
                    </small>
                </div>
                
                <!-- Selected Time Display -->
                <div id="selectedTimeDisplay" style="padding: 12px; background: #e8f5e9; border-radius: 6px; margin-bottom: 20px; display: none;">
                    <p style="font-size: 14px; color: #2e7d32; margin: 0;">
                        <strong>Selected:</strong> <span id="selectedTimeText"></span>
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="display: flex; gap: 12px; justify-content: flex-end; padding: 16px 20px; border-top: 1px solid #e0e0e0;">
            <button type="button" onclick="cancelCnpTimeSelection()" style="padding: 10px 20px; border: 1px solid #ddd; border-radius: 6px; background: white; color: #333; cursor: pointer; font-size: 14px; font-weight: 500;">
                Cancel
            </button>
            <button type="button" onclick="confirmCnpTimeSelection()" style="padding: 10px 20px; background: #f59e0b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- View Detail Modal (for viewing only) -->
<div id="prospectDetailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">View Prospect Details</h3>
            <button class="close-modal" onclick="closeProspectDetailModal()">&times;</button>
        </div>
        <div id="prospectDetailContent" style="padding: 24px;">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Global variables and functions for onclick handlers
    // Use relative path to avoid APP_URL misconfig issues
    const API_BASE_URL = '/api/sales-manager';
    const API_TOKEN = '{{ $api_token ?? session("api_token") ?? "" }}';
    
    // Store token in localStorage if available
    if (API_TOKEN) {
        localStorage.setItem('sales_manager_token', API_TOKEN);
    }
    
    // Initialize currentStatus from localStorage or default to 'all'
    let savedFilter = 'all';
    try {
        const saved = localStorage.getItem('salesManagerTasksFilter');
        if (saved && ['all', 'pending', 'overdue', 'rescheduled', 'completed'].includes(saved)) {
            savedFilter = saved;
        }
    } catch (e) {
        console.error('Failed to read filter from localStorage:', e);
    }

    let savedCategory = 'all';
    try {
        const saved = localStorage.getItem('salesManagerTaskCategory');
        if (saved && ['all', 'meeting', 'site_visit', 'prospect', 'closer', 'other'].includes(saved)) {
            savedCategory = saved;
        }
    } catch (e) {
        console.error('Failed to read task category from localStorage:', e);
    }

    let currentStatus = savedFilter;
    let currentCategory = savedCategory;
    let currentTaskId = null;
    
    // Attach to window for global access
    window.currentStatus = currentStatus;
    window.currentCategory = currentCategory;
    window.currentTaskId = currentTaskId;

    function getAuthHeaders() {
        const token = API_TOKEN || localStorage.getItem('sales_manager_token') || '';
        return {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    async function apiCall(endpoint, options = {}) {
        try {
            const url = `${API_BASE_URL}${endpoint}`;
            console.log(`API Call: ${url}`);
            console.log('Request options:', { method: options.method || 'GET', headers: getAuthHeaders() });
            
            const response = await fetch(url, {
                method: options.method || 'GET',
                headers: {
                    ...getAuthHeaders(),
                    ...(options.headers || {})
                },
                body: options.body || undefined,
                credentials: 'same-origin'
            });

            console.log(`API Response Status: ${response.status} for ${endpoint}`);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));

            if (response.status === 401) {
                console.error('Unauthorized - Token may be invalid');
                localStorage.removeItem('sales_manager_token');
                showAlert('Session expired. Redirecting to login...', 'error');
                setTimeout(() => {
                    window.location.href = '{{ route("login") }}';
                }, 2000);
                return { success: false, message: 'Unauthorized' };
            }

            if (!response.ok) {
                const errorText = await response.text();
                console.error(`API Error (${response.status}):`, errorText);
                console.error('Error response headers:', Object.fromEntries(response.headers.entries()));
                try {
                    const errorJson = JSON.parse(errorText);
                    return { success: false, ...errorJson };
                } catch (e) {
                    return { success: false, message: errorText || `HTTP ${response.status}: ${response.statusText}` };
                }
            }

            const responseText = await response.text();
            console.log('Raw API response text:', responseText.substring(0, 500));
            
            let data;
            try {
                data = JSON.parse(responseText);
                console.log(`API Success for ${endpoint}:`, {
                    success: data.success,
                    data_length: data.data ? (Array.isArray(data.data) ? data.data.length : 'not array') : 'no data',
                    total: data.total,
                    current_page: data.current_page
                });
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response text:', responseText);
                return { success: false, message: 'Invalid JSON response from server' };
            }
            
            return data;
        } catch (error) {
            console.error('API Call Error:', error);
            console.error('Error details:', error.message, error.stack);
            console.error('Error name:', error.name);
            showAlert('Network error: ' + error.message, 'error');
            return { success: false, message: error.message || 'Network error occurred' };
        }
    }

    function showAlert(message, type = 'info', duration = 3000) {
        const notification = document.getElementById('customNotification');
        const messageEl = document.getElementById('notificationMessage');
        const overlay = document.getElementById('notificationOverlay');
        
        if (notification && messageEl && overlay) {
            messageEl.textContent = message;
            overlay.style.display = 'flex';
            notification.classList.remove('hide');
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
                notification.classList.add('hide');
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }, duration);
        } else {
            alert(message);
        }
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('en-IN', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Filter tasks function - must be globally accessible for onclick handlers
    function filterTasks(status, dateFilter = null, customDate = null, category = null) {
        console.log('filterTasks called with status:', status, 'dateFilter:', dateFilter, 'customDate:', customDate, 'category:', category);
        currentStatus = status;
        
        // Get date filter from dropdown if not provided
        if (dateFilter === null) {
            const dateDropdown = document.getElementById('dateFilterDropdown') || document.getElementById('dateFilterDropdownDesktop');
            dateFilter = dateDropdown ? dateDropdown.value : 'all';
        }

        if (category === null) {
            const categoryDropdown = document.getElementById('taskTypeFilterDesktop') || document.getElementById('taskTypeFilterMobile');
            category = categoryDropdown ? categoryDropdown.value : (currentCategory || 'all');
        }
        currentCategory = category;
        
        // Save to localStorage
        try {
            localStorage.setItem('salesManagerTasksFilter', status);
            localStorage.setItem('salesManagerDateFilter', dateFilter);
            localStorage.setItem('salesManagerTaskCategory', category || 'all');
            if (customDate) {
                localStorage.setItem('salesManagerCustomDate', customDate);
            } else {
                localStorage.removeItem('salesManagerCustomDate');
            }
        } catch (e) {
            console.error('Failed to save filter to localStorage:', e);
        }
        
        // Update button states
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-status') === status) {
                btn.classList.add('active');
            }
        });
        
        // Update status dropdown value
        const filterDropdown = document.getElementById('taskFilterDropdown');
        if (filterDropdown) {
            filterDropdown.value = status;
        }
        
        // Update date dropdown values
        const dateDropdownMobile = document.getElementById('dateFilterDropdown');
        const dateDropdownDesktop = document.getElementById('dateFilterDropdownDesktop');
        if (dateDropdownMobile) {
            dateDropdownMobile.value = dateFilter;
        }
        if (dateDropdownDesktop) {
            dateDropdownDesktop.value = dateFilter;
        }

        const categoryDropdownDesktop = document.getElementById('taskTypeFilterDesktop');
        const categoryDropdownMobile = document.getElementById('taskTypeFilterMobile');
        if (categoryDropdownDesktop) {
            categoryDropdownDesktop.value = category;
        }
        if (categoryDropdownMobile) {
            categoryDropdownMobile.value = category;
        }
        
        // Show/hide custom date picker
        const customDatePicker = document.getElementById('customDatePicker');
        if (customDatePicker) {
            if (dateFilter === 'custom') {
                customDatePicker.style.display = 'block';
                if (customDate) {
                    customDatePicker.value = customDate;
                } else {
                    // Get saved custom date or use today
                    const savedCustomDate = localStorage.getItem('salesManagerCustomDate');
                    if (savedCustomDate) {
                        customDatePicker.value = savedCustomDate;
                    } else {
                        customDatePicker.value = new Date().toISOString().split('T')[0];
                    }
                }
            } else {
                customDatePicker.style.display = 'none';
            }
        }

        // Show/hide remove all overdue button
        const removeAllOverdueBtn = document.getElementById('removeAllOverdueBtn');
        if (removeAllOverdueBtn) {
            if (status === 'overdue') {
                removeAllOverdueBtn.style.display = 'flex';
            } else {
                removeAllOverdueBtn.style.display = 'none';
            }
        }

        loadTasks(status, dateFilter, customDate || (dateFilter === 'custom' && customDatePicker ? customDatePicker.value : null), category);
    }
    
    // Attach to window for global access (critical for onclick handlers)
    window.filterTasks = filterTasks;
    
    console.log('filterTasks function defined and attached to window:', typeof window.filterTasks);

    async function loadTasks(status = null, dateFilter = null, customDate = null, category = null) {
        console.log('=== loadTasks() CALLED ===', { status, dateFilter, customDate, category });
        const tasksGrid = document.getElementById('tasksGrid');
        if (!tasksGrid) {
            console.error('ERROR: Tasks grid element not found!');
            return;
        }
        
        // Get current filters if not provided
        if (status === null) {
            status = window.currentStatus || currentStatus || 'all';
        }
        if (dateFilter === null) {
            const dateDropdown = document.getElementById('dateFilterDropdown') || document.getElementById('dateFilterDropdownDesktop');
            dateFilter = dateDropdown ? dateDropdown.value : 'all';
            // Try to get from localStorage if dropdown not found
            if (dateFilter === 'all') {
                try {
                    const saved = localStorage.getItem('salesManagerDateFilter');
                    if (saved && ['all', 'today', 'tomorrow', 'this_week', 'this_month', 'custom'].includes(saved)) {
                        dateFilter = saved;
                    }
                } catch (e) {
                    console.error('Error reading saved date filter:', e);
                }
            }
        }
        if (dateFilter === 'custom' && customDate === null) {
            const customDatePicker = document.getElementById('customDatePicker');
            customDate = customDatePicker && customDatePicker.value ? customDatePicker.value : null;
            // Try to get from localStorage if picker not found
            if (!customDate) {
                try {
                    customDate = localStorage.getItem('salesManagerCustomDate');
                } catch (e) {
                    console.error('Error reading saved custom date:', e);
                }
            }
        }

        if (category === null) {
            const categoryDropdown = document.getElementById('taskTypeFilterDesktop') || document.getElementById('taskTypeFilterMobile');
            category = categoryDropdown ? categoryDropdown.value : (currentCategory || 'all');
            if (category === 'all') {
                try {
                    const savedCategory = localStorage.getItem('salesManagerTaskCategory');
                    if (savedCategory && ['all', 'meeting', 'site_visit', 'prospect', 'closer', 'other'].includes(savedCategory)) {
                        category = savedCategory;
                    }
                } catch (e) {
                    console.error('Error reading saved category filter:', e);
                }
            }
        }
        
        console.log('Setting loading state...');
        tasksGrid.innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i><p>Loading tasks...</p></div>';

        // Set a timeout to show error if API call takes too long
        let timeoutId = setTimeout(() => {
            console.error('API call timeout after 30 seconds');
            const currentGrid = document.getElementById('tasksGrid');
            if (currentGrid && currentGrid.innerHTML.includes('Loading tasks')) {
                currentGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Request Timeout</h3><p>The request is taking too long. Please check your connection and try again.</p><button onclick="if(window.filterTasks) { const status = window.currentStatus || \'all\'; const dateFilter = document.getElementById(\'dateFilterDropdown\')?.value || document.getElementById(\'dateFilterDropdownDesktop\')?.value || \'all\'; const customDate = document.getElementById(\'customDatePicker\')?.value || null; window.filterTasks(status, dateFilter, customDate); } else { window.location.reload(); }" style="margin-top: 12px; padding: 8px 16px; background: #205A44; color: white; border: none; border-radius: 6px; cursor: pointer;">Retry</button></div>';
            }
        }, 30000);

        try {
            console.log('=== STARTING API CALL ===');
            console.log('Current status filter:', currentStatus);
            console.log('API_BASE_URL:', API_BASE_URL);
            console.log('API_TOKEN:', API_TOKEN ? 'Present (' + API_TOKEN.substring(0, 20) + '...)' : 'Missing');
            
            const params = new URLSearchParams();
            if (status && status !== 'all') {
                params.append('status', status);
            }
            
            // Add date filter parameters
            if (dateFilter && dateFilter !== 'all') {
                params.append('date_filter', dateFilter);
                if (dateFilter === 'custom' && customDate) {
                    params.append('custom_date', customDate);
                }
            }

            if (category && category !== 'all') {
                params.append('category', category);
            }
            
            const endpoint = `/tasks${params.toString() ? '?' + params.toString() : ''}`;
            const fullUrl = `${API_BASE_URL}${endpoint}`;
            console.log('Full API URL:', fullUrl);
            console.log('Calling API endpoint:', endpoint);
            console.log('Filters:', { status, dateFilter, customDate, category });
            
            const result = await apiCall(endpoint);
            clearTimeout(timeoutId); // Clear timeout on success
            
            console.log('=== API CALL COMPLETED ===');
            console.log('Tasks API response:', result);
            console.log('Response type:', typeof result);
            console.log('Response keys:', result ? Object.keys(result) : 'null');
            
            // Check if result exists and has the expected structure
            if (!result) {
                console.error('API returned null or undefined');
                tasksGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading tasks</h3><p>No response from server. Please refresh the page.</p></div>';
                return;
            }
            
            // Handle error response
            if (result.success === false) {
                console.error('API returned error:', result.message || result.error);
                const errorMsg = result.message || result.error || 'Unknown error occurred';
                tasksGrid.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading tasks</h3><p>${errorMsg}</p><button onclick="if(window.filterTasks) { const status = window.currentStatus || 'all'; const dateFilter = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all'; const customDate = document.getElementById('customDatePicker')?.value || null; window.filterTasks(status, dateFilter, customDate); } else { window.location.reload(); }" style="margin-top: 12px; padding: 8px 16px; background: #205A44; color: white; border: none; border-radius: 6px; cursor: pointer;">Retry</button></div>`;
                return;
            }
            
            // Check if data exists and is an array
            console.log('Checking response data...');
            console.log('result.success:', result.success);
            console.log('result.data exists:', result.data !== undefined);
            console.log('result.data type:', typeof result.data);
            console.log('result.data is array:', Array.isArray(result.data));
            
            if (result.data !== undefined) {
                if (Array.isArray(result.data)) {
                    console.log(`Data is array with ${result.data.length} items`);
                    if (result.data.length > 0) {
                        console.log(`Found ${result.data.length} tasks out of ${result.total || result.data.length} total`);
                        console.log('First task sample:', result.data[0]);
                        renderTasks(result.data);
                    } else {
                        console.log('No tasks found (empty array)');
                        // Hide empty state on mobile - show nothing
                        tasksGrid.innerHTML = '';
                    }
                } else {
                    console.error('Invalid response format - data is not an array:', result);
                    console.error('Data type:', typeof result.data, 'Value:', result.data);
                    console.error('Full result:', JSON.stringify(result, null, 2));
                    tasksGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading tasks</h3><p>Invalid response format from server. Data is not an array. Check console for details.</p></div>';
                }
            } else {
                console.error('Response missing data field:', result);
                console.error('Full result object:', JSON.stringify(result, null, 2));
                tasksGrid.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading tasks</h3><p>Response missing data field from server. Check console for details.</p></div>';
            }
        } catch (error) {
            clearTimeout(timeoutId); // Clear timeout on error
            console.error('=== ERROR IN loadTasks() ===');
            console.error('Error loading tasks:', error);
            console.error('Error name:', error.name);
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            const errorMsg = error.message || 'Unknown error occurred';
            tasksGrid.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Error loading tasks</h3><p>${errorMsg}. Please refresh the page.</p><button onclick="if(window.filterTasks) { const status = window.currentStatus || 'all'; const dateFilter = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all'; const customDate = document.getElementById('customDatePicker')?.value || null; window.filterTasks(status, dateFilter, customDate); } else { window.location.reload(); }" style="margin-top: 12px; padding: 8px 16px; background: #205A44; color: white; border: none; border-radius: 6px; cursor: pointer;">Retry</button></div>`;
        }
    }
    
    // Make loadTasks globally accessible (critical for onclick handlers in error messages)
    window.loadTasks = loadTasks;
    console.log('loadTasks function defined and attached to window:', typeof window.loadTasks);

    function renderTasks(tasks) {
        const tasksGrid = document.getElementById('tasksGrid');
        
        if (tasks.length === 0) {
            // Hide empty state on mobile - show nothing
            tasksGrid.innerHTML = '';
            return;
        }

        tasksGrid.innerHTML = tasks.map(task => {
            const lead = task.lead || {};
            // Extract lead name from title if lead name not available
            let leadName = lead.name || 'Prospect';
            if (task.title) {
                const titleMatch = task.title.match(/prospect verification:\s*(.+)/i);
                if (titleMatch) {
                    leadName = titleMatch[1].trim();
                } else if (!lead.name) {
                    leadName = task.title.replace('Call for prospect verification: ', '').trim() || 'Prospect';
                }
            }
            const leadPhone = lead.phone || task.lead_phone || 'N/A';
            const initial = leadName.charAt(0).toUpperCase();
            const isOverdue = task.is_overdue === true || (task.scheduled_at_formatted && new Date(task.scheduled_at_formatted) < new Date() && task.status === 'pending');
            const scheduledDate = formatDate(task.scheduled_at_formatted || task.scheduled_at);
            const overdueClass = isOverdue ? 'overdue' : '';
            const statusClass = `status-${task.status || 'pending'}`;
            
            // Check if task is related to meeting or visit
            const isMeetingTask = task.type === 'meeting';
            const isVisitTask = task.type === 'site_visit';
            const isPendingMeeting = task.status === 'pending' && isMeetingTask;
            const isPendingVisit = task.status === 'pending' && isVisitTask;
            
            // Check if lead has prospect (from telecaller) - needs verification
            const hasProspect = task.has_prospect === true && 
                               task.prospect && 
                               task.prospect.is_pending_verification === true;
            
            // Check if task is related to prospect (has prospect OR prospect verification related)
            const isProspectTask = task.has_prospect === true || 
                                  (task.prospect && task.prospect.is_pending_verification === true) ||
                                  (task.title && task.title.toLowerCase().includes('prospect verification'));
            
            // Check if task is a site visit reminder
            const isSiteVisitReminder = (task.notes && task.notes.includes('Site Visit Reminder')) || 
                                       (task.title && task.title.includes('Reminder: Site Visit'));

            const cleanPhone = String(leadPhone).replace(/[^0-9]/g, '');
            return `
                <div id="task-card-${task.id}" class="task-card ${overdueClass}">
                    <div class="task-header">
                        <div class="task-avatar">${initial}</div>
                        <div>
                            <h3 class="task-name">${leadName}</h3>
                            ${isOverdue ? '<span class="overdue-badge">OVERDUE</span>' : ''}
                            ${isPendingMeeting ? '<span class="meeting-tag" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 24px; margin-left: 8px;">M</span>' : ''}
                            ${isPendingVisit ? '<span class="visit-tag" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 24px; margin-left: 8px;">V</span>' : ''}
                            ${isProspectTask ? '<span class="prospect-tag" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 24px; margin-left: 8px;">P</span>' : ''}
                            ${isSiteVisitReminder ? '<span class="site-visit-tag" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 24px; margin-left: 8px;">S</span>' : ''}
                            <span class="status-badge ${statusClass}">${task.status.replace('_', ' ').toUpperCase()}</span>
                        </div>
                    </div>
                    <div class="task-info">
                        <div class="task-info-row">
                            <i class="fas fa-phone"></i>
                            <span>${leadPhone}</span>
                        </div>
                        <div class="task-info-row">
                            <i class="fas fa-calendar"></i>
                            <span>${scheduledDate}</span>
                        </div>
                        ${task.notes && task.notes.includes('Pre-meeting reminder') ? '<div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;"><span class="meeting-badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;"><i class="fas fa-calendar-check" style="font-size: 10px;"></i> Meeting</span></div>' : ''}
                    </div>
                    <div class="task-actions">
                        <button class="task-action-btn btn-call" onclick="handleManagerCallClick(${task.id}, '${leadPhone}', ${hasProspect})" title="Call">
                            <i class="fas fa-phone"></i>
                            <span>Call</span>
                        </button>
                        <button class="task-action-btn btn-whatsapp" onclick="openWhatsApp('${cleanPhone}')" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                            <span>WhatsApp</span>
                        </button>
                        <button class="task-action-btn btn-view-detail" onclick="openProspectDetailModal(${task.id}, true)" title="View Detail">
                            <i class="fas fa-eye"></i>
                            <span>Detail</span>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function openWhatsApp(phone) {
        if (!phone || phone === 'N/A') {
            showAlert('Phone number not available', 'warning');
            return;
        }
        // Remove all non-numeric characters and ensure it starts with country code
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        if (cleanPhone.length < 10) {
            showAlert('Invalid phone number', 'warning');
            return;
        }
        // If phone doesn't start with country code, assume it's Indian (+91)
        const phoneWithCountryCode = cleanPhone.startsWith('91') && cleanPhone.length === 12 
            ? cleanPhone 
            : (cleanPhone.length === 10 ? '91' + cleanPhone : cleanPhone);
        window.open(`https://wa.me/${phoneWithCountryCode}`, '_blank');
    }

    async function loadInterestedProjects() {
        try {
            const response = await fetch('/api/interested-project-names', {
                headers: getAuthHeaders(),
            });
            const result = await response.json();
            
            if (result && result.success && result.data) {
                const projectSelect = document.getElementById('interestedProjects');
                projectSelect.innerHTML = ''; // Clear existing options
                
                result.data.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.id;
                    option.textContent = project.name;
                    projectSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading interested projects:', error);
        }
    }

    // Handle manager call click - Check if lead has prospect or is direct assignment
    async function handleManagerCallClick(taskId, phoneNumber, hasProspect = null) {
        currentTaskId = taskId;
        
        // First, check if this is a meeting task
        let isMeetingTask = false;
        let meetingId = null;
        
        try {
            const taskResponse = await apiCall(`/tasks/${taskId}`);
            if (taskResponse && taskResponse.success && taskResponse.data) {
                const taskData = taskResponse.data;
                // Check if task is related to meeting
                isMeetingTask = taskData.type === 'meeting' || 
                               (taskData.notes && taskData.notes.includes('Pre-meeting reminder'));
                
                // Extract meeting ID from notes if available
                if (isMeetingTask && taskData.notes) {
                    const meetingIdMatch = taskData.notes.match(/Meeting ID:\s*(\d+)/i);
                    if (meetingIdMatch) {
                        meetingId = parseInt(meetingIdMatch[1]);
                    }
                }
            }
        } catch (error) {
            console.error('Error fetching task data:', error);
        }
        
        // If this is a meeting task, show meeting popup after call
        if (isMeetingTask && meetingId) {
            if (phoneNumber && phoneNumber !== 'N/A' && phoneNumber !== '') {
                const cleanPhone = String(phoneNumber).replace(/[^0-9]/g, '');
                if (cleanPhone.length >= 10) {
                    // Open phone dialer
                    window.location.href = `tel:${cleanPhone}`;
                    // Small delay to allow dialer to open, then show meeting popup
                    setTimeout(() => {
                        if (typeof showPostCallPopup === 'function') {
                            showPostCallPopup(meetingId, null, taskId, 'Task');
                        } else {
                            showAlert('Meeting popup not available', 'error');
                        }
                    }, 500);
                } else {
                    showAlert('Phone number not available', 'warning');
                    if (typeof showPostCallPopup === 'function') {
                        showPostCallPopup(meetingId, null, taskId, 'Task');
                    }
                }
            } else {
                showAlert('Phone number not available', 'warning');
                if (typeof showPostCallPopup === 'function') {
                    showPostCallPopup(meetingId, null, taskId, 'Task');
                }
            }
            return; // Exit early for meeting tasks
        }
        
        // If hasProspect not provided, fetch task data to check
        if (hasProspect === null) {
            try {
                const tasksResponse = await apiCall('/tasks');
                if (tasksResponse && tasksResponse.success && tasksResponse.data) {
                    const taskData = tasksResponse.data.find(t => t.id === taskId);
                    hasProspect = taskData?.has_prospect === true && 
                                 taskData?.prospect?.is_pending_verification === true;
                }
            } catch (error) {
                console.error('Error fetching task data:', error);
                // Safer fallback: treat as direct lead to avoid wrong prospect actions.
                hasProspect = false;
            }
        }
        
        // If lead has prospect from telecaller → show verification popup
        // If no prospect (direct assignment) → open Lead Requirement Form directly
        if (hasProspect) {
            // Lead from telecaller - show verification popup (current behavior)
            if (phoneNumber && phoneNumber !== 'N/A' && phoneNumber !== '') {
                const cleanPhone = String(phoneNumber).replace(/[^0-9]/g, '');
                if (cleanPhone.length >= 10) {
                    // Open phone dialer
                    window.location.href = `tel:${cleanPhone}`;
                    // Small delay to allow dialer to open, then show verification modal
                    setTimeout(() => {
                        showVerifyRejectPrompt();
                    }, 500);
                } else {
                    showAlert('Phone number not available', 'warning');
                    showVerifyRejectPrompt();
                }
            } else {
                showAlert('Phone number not available', 'warning');
                showVerifyRejectPrompt();
            }
        } else {
            // Directly assigned lead (no prospect) - open Lead Requirement Form directly
            // Skip verification popup
            if (phoneNumber && phoneNumber !== 'N/A' && phoneNumber !== '') {
                const cleanPhone = String(phoneNumber).replace(/[^0-9]/g, '');
                if (cleanPhone.length >= 10) {
                    // Open phone dialer
                    window.location.href = `tel:${cleanPhone}`;
                    // Small delay to allow dialer to open, then open form directly
                    setTimeout(() => {
                        openManagerLeadRequirementFormModal(taskId);
                    }, 500);
                } else {
                    // No phone, but still open form directly
                    openManagerLeadRequirementFormModal(taskId);
                }
            } else {
                // No phone, open form directly
                openManagerLeadRequirementFormModal(taskId);
            }
        }
    }
    
    function showVerifyRejectPrompt() {
        // Show verify/reject prompt modal (Step 1)
        const promptModal = document.getElementById('verifyRejectPromptModal');
        promptModal.classList.add('active');
    }

    function closeVerifyRejectPromptModal() {
        const modal = document.getElementById('verifyRejectPromptModal');
        modal.classList.remove('active');
        // Don't reset currentTaskId here - it's needed for proceedToVerifyForm() and proceedToReject()
        // Only reset when form is submitted successfully or user explicitly cancels
    }
    
    function cancelVerifyRejectPrompt() {
        // User clicked close/cancel button - reset task ID
        closeVerifyRejectPromptModal();
        currentTaskId = null;
    }

    // Step 2a: Proceed to Verify - Load full form
    async function proceedToVerifyForm() {
        // Don't close modal here - just hide it, keep currentTaskId
        const promptModal = document.getElementById('verifyRejectPromptModal');
        promptModal.classList.remove('active');
        
        if (!currentTaskId) {
            showAlert('Task ID not found', 'error');
            return;
        }
        
        // Open full form modal
        await openManagerLeadRequirementFormModal(currentTaskId);
    }

    // Step 2b: Proceed to Reject - Show reject reason modal
    function proceedToReject() {
        // Don't close modal here - just hide it, keep currentTaskId
        const promptModal = document.getElementById('verifyRejectPromptModal');
        promptModal.classList.remove('active');
        
        if (!currentTaskId) {
            showAlert('Task ID not found', 'error');
            return;
        }
        
        // Show reject reason modal
        const rejectModal = document.getElementById('rejectReasonModal');
        rejectModal.classList.add('active');
        document.getElementById('rejectReasonInput').value = '';
    }

    // Step 2c: Proceed to CNP - Open time selection modal
    function proceedToCNP() {
        // Hide prompt modal, keep currentTaskId
        const promptModal = document.getElementById('verifyRejectPromptModal');
        promptModal.classList.remove('active');
        
        if (!currentTaskId) {
            showAlert('Task ID not found', 'error');
            return;
        }
        
        // Open CNP time selection modal
        openCnpTimeSelectionModal();
    }
    
    // CNP Time Selection Variables
    let selectedCnpMinutes = null;
    let selectedCnpCustomDateTime = null;
    let isCustomTimeSelected = false;
    
    // Open CNP time selection modal
    function openCnpTimeSelectionModal() {
        const modal = document.getElementById('cnpTimeSelectionModal');
        if (modal) {
            modal.classList.add('active');
            // Reset selections
            selectedCnpMinutes = null;
            selectedCnpCustomDateTime = null;
            isCustomTimeSelected = false;
            document.getElementById('customTimePickerContainer').style.display = 'none';
            document.getElementById('selectedTimeDisplay').style.display = 'none';
            // Clear button selections
            document.querySelectorAll('.time-option-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('cnpCustomDate');
            if (dateInput) {
                dateInput.min = today;
                dateInput.value = '';
            }
            const timeInput = document.getElementById('cnpCustomTime');
            if (timeInput) {
                timeInput.value = '';
            }
        }
    }
    
    // Select quick time option (15 min, 30 min, 1 hr, 2 hr)
    function selectCnpTime(minutes, event) {
        selectedCnpMinutes = minutes;
        selectedCnpCustomDateTime = null;
        isCustomTimeSelected = false;
        
        // Hide custom picker
        document.getElementById('customTimePickerContainer').style.display = 'none';
        
        // Clear custom inputs
        document.getElementById('cnpCustomDate').value = '';
        document.getElementById('cnpCustomTime').value = '';
        
        // Remove selected class from all buttons
        document.querySelectorAll('.time-option-btn').forEach(btn => {
            btn.classList.remove('selected');
        });
        
        // Add selected class to clicked button
        if (event && event.target) {
            event.target.classList.add('selected');
        }
        
        // Calculate and display selected time
        const now = new Date();
        const retryTime = new Date(now.getTime() + minutes * 60000);
        const formattedTime = retryTime.toLocaleString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
        
        document.getElementById('selectedTimeText').textContent = `Retry call in ${minutes} minutes (${formattedTime})`;
        document.getElementById('selectedTimeDisplay').style.display = 'block';
    }
    
    // Show custom date-time picker
    function showCustomTimePicker() {
        isCustomTimeSelected = true;
        selectedCnpMinutes = null;
        
        // Remove selected class from quick option buttons
        document.querySelectorAll('.time-option-btn[data-minutes]').forEach(btn => {
            btn.classList.remove('selected');
        });
        
        // Show custom picker container
        const customContainer = document.getElementById('customTimePickerContainer');
        customContainer.style.display = 'block';
        
        // Hide selected time display initially
        document.getElementById('selectedTimeDisplay').style.display = 'none';
        
        // Set minimum date to today and default time to next hour
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('cnpCustomDate');
        const timeInput = document.getElementById('cnpCustomTime');
        
        if (dateInput) {
            dateInput.min = today;
            if (!dateInput.value) {
                dateInput.value = today;
            }
        }
        
        if (timeInput && !timeInput.value) {
            const nextHour = new Date();
            nextHour.setHours(nextHour.getHours() + 1);
            nextHour.setMinutes(0);
            const timeStr = nextHour.toTimeString().slice(0, 5); // HH:MM format
            timeInput.value = timeStr;
        }
        
        // Add change listeners to update selected time display
        if (dateInput && timeInput) {
            const updateCustomTimeDisplay = () => {
                const date = dateInput.value;
                const time = timeInput.value;
                if (date && time) {
                    const dateTime = new Date(`${date}T${time}`);
                    if (dateTime > new Date()) {
                        const formattedTime = dateTime.toLocaleString('en-IN', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        });
                        document.getElementById('selectedTimeText').textContent = `Retry call on ${formattedTime}`;
                        document.getElementById('selectedTimeDisplay').style.display = 'block';
                        selectedCnpCustomDateTime = dateTime.toISOString();
                    } else {
                        document.getElementById('selectedTimeDisplay').style.display = 'none';
                    }
                } else {
                    document.getElementById('selectedTimeDisplay').style.display = 'none';
                }
            };
            
            // Remove existing listeners and add new ones
            dateInput.removeEventListener('change', updateCustomTimeDisplay);
            timeInput.removeEventListener('change', updateCustomTimeDisplay);
            dateInput.addEventListener('change', updateCustomTimeDisplay);
            timeInput.addEventListener('change', updateCustomTimeDisplay);
            
            // Initial update
            updateCustomTimeDisplay();
        }
    }
    
    // Confirm CNP time selection and submit
    async function confirmCnpTimeSelection() {
        if (!currentTaskId) {
            showAlert('Task ID not found', 'error');
            return;
        }
        
        let retryAt = null;
        let retryMinutes = null;
        
        // Validate selection
        if (isCustomTimeSelected) {
            const dateInput = document.getElementById('cnpCustomDate');
            const timeInput = document.getElementById('cnpCustomTime');
            const date = dateInput.value;
            const time = timeInput.value;
            
            if (!date || !time) {
                showAlert('Please select both date and time for custom option', 'warning');
                return;
            }
            
            const selectedDateTime = new Date(`${date}T${time}`);
            const now = new Date();
            
            if (selectedDateTime <= now) {
                showAlert('Please select a future date and time', 'warning');
                return;
            }
            
            retryAt = selectedDateTime.toISOString();
        } else if (selectedCnpMinutes !== null) {
            retryMinutes = selectedCnpMinutes;
        } else {
            showAlert('Please select a retry time option', 'warning');
            return;
        }
        
        try {
            const requestBody = {};
            if (retryAt) {
                requestBody.retry_at = retryAt;
            } else if (retryMinutes !== null) {
                requestBody.retry_minutes = retryMinutes;
            }
            
            const result = await apiCall(`/tasks/${currentTaskId}/cnp`, {
                method: 'POST',
                body: JSON.stringify(requestBody)
            });
            
            if (result && result.success) {
                const timeMsg = isCustomTimeSelected 
                    ? `New calling task created for selected time.`
                    : `New calling task created for ${retryMinutes} minutes later.`;
                showAlert(`Call Not Picked marked. ${timeMsg}`, 'success', 4000);
                closeCnpTimeSelectionModal();
                currentTaskId = null; // Reset after successful submission
                // Refresh tasks list after a short delay (preserve current filters)
                setTimeout(() => {
                    const status = window.currentStatus || currentStatus || 'all';
                    const dateFilter = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all';
                    const customDate = document.getElementById('customDatePicker')?.value || null;
                    loadTasks(status, dateFilter, customDate);
                }, 500);
            } else {
                showAlert(result?.message || result?.error || 'Failed to mark as CNP', 'error');
            }
        } catch (error) {
            console.error('Error marking as CNP:', error);
            showAlert('Error marking as CNP: ' + error.message, 'error');
        }
    }
    
    // Cancel CNP time selection
    function cancelCnpTimeSelection() {
        closeCnpTimeSelectionModal();
        // Don't reset currentTaskId here - user might want to try again
    }
    
    // Close CNP time selection modal
    function closeCnpTimeSelectionModal() {
        const modal = document.getElementById('cnpTimeSelectionModal');
        if (modal) {
            modal.classList.remove('active');
            // Reset selections
            selectedCnpMinutes = null;
            selectedCnpCustomDateTime = null;
            isCustomTimeSelected = false;
            document.getElementById('customTimePickerContainer').style.display = 'none';
            document.getElementById('selectedTimeDisplay').style.display = 'none';
            document.getElementById('cnpCustomDate').value = '';
            document.getElementById('cnpCustomTime').value = '';
            document.querySelectorAll('.time-option-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
        }
    }

    function closeRejectReasonModal() {
        const modal = document.getElementById('rejectReasonModal');
        modal.classList.remove('active');
        document.getElementById('rejectReasonInput').value = '';
    }
    
    function cancelRejectReasonModal() {
        // User cancelled - reset task ID and close modal
        closeRejectReasonModal();
        currentTaskId = null;
    }

    // Submit reject
    async function submitRejectProspect() {
        const rejectionReason = document.getElementById('rejectReasonInput').value.trim();
        
        if (!rejectionReason) {
            showAlert('Please enter a rejection reason', 'warning');
            return;
        }
        
        if (!currentTaskId) {
            showAlert('Task ID not found', 'error');
            return;
        }
        
        try {
            const result = await apiCall(`/tasks/${currentTaskId}/reject`, {
                method: 'POST',
                body: JSON.stringify({
                    rejection_reason: rejectionReason
                })
            });
            
            if (result && result.success) {
                // Remove task card immediately from DOM
                const taskCard = document.getElementById(`task-card-${currentTaskId}`);
                if (taskCard) {
                    taskCard.style.transition = 'opacity 0.3s, transform 0.3s';
                    taskCard.style.opacity = '0';
                    taskCard.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        taskCard.remove();
                    // Reload tasks after card removal to ensure consistency (preserve current filters)
                    const status = window.currentStatus || currentStatus || 'all';
                    const dateFilter = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all';
                    const customDate = document.getElementById('customDatePicker')?.value || null;
                    loadTasks(status, dateFilter, customDate);
                    }, 300);
                } else {
                    // Fallback if card not found by ID
                    loadTasks();
                }
                
                showAlert('Prospect rejected successfully', 'success');
                closeRejectReasonModal();
                currentTaskId = null; // Reset after successful submission
            } else {
                showAlert(result?.message || 'Failed to reject prospect', 'error');
            }
        } catch (error) {
            console.error('Error rejecting prospect:', error);
            showAlert('Error rejecting prospect: ' + error.message, 'error');
        }
    }

    // Open manager lead requirement form modal (Step 2 - after verify clicked)
    async function openManagerLeadRequirementFormModal(taskId) {
        const modal = document.getElementById('managerLeadRequirementFormModal');
        const container = document.getElementById('managerLeadFormContainer');
        
        modal.classList.add('active');
        container.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner" style="display: inline-block;"></div><p style="margin-top: 15px; color: #666;">Loading form...</p></div>';
        
        try {
            const result = await apiCall(`/tasks/${taskId}/lead-requirement-form`);
            
            if (result && result.success) {
                renderManagerLeadForm(result);
            } else {
                showAlert('Failed to load form: ' + (result?.error || result?.message || 'Unknown error'), 'error');
                closeManagerLeadRequirementFormModal();
            }
        } catch (error) {
            console.error('Error loading form:', error);
            showAlert('Error loading form: ' + error.message, 'error');
            closeManagerLeadRequirementFormModal();
        }
    }

    function closeManagerLeadRequirementFormModal() {
        const modal = document.getElementById('managerLeadRequirementFormModal');
        modal.classList.remove('active');
        document.getElementById('managerLeadFormContainer').innerHTML = '';
        // Reset currentTaskId when modal is closed (user cancelled)
        currentTaskId = null;
    }
    
    function cancelManagerLeadRequirementForm() {
        // User clicked close/cancel button - reset task ID and close modal
        closeManagerLeadRequirementFormModal();
    }

    // Render manager lead requirement form (similar to telecaller but all fields visible)
    function renderManagerLeadForm(data) {
        const container = document.getElementById('managerLeadFormContainer');
        
        // Update modal title based on whether it's a prospect or direct lead
        const modalTitle = document.querySelector('#managerLeadRequirementFormModal .modal-header h3');
        if (modalTitle) {
            const hasProspect = data.has_prospect === true;
            modalTitle.textContent = hasProspect ? 'Prospect Verification' : 'Lead Detail Form';
        }
        
        const formValues = data.form_values || {};
        
        // Get existing values for pre-population
        const existingCategory = formValues.category || '';
        const existingPreferredLocation = formValues.preferred_location || '';
        const existingType = formValues.type || '';
        const existingPurpose = formValues.purpose || '';
        const existingPossession = formValues.possession || '';
        const existingBudget = formValues.budget || '';
        
        let formHTML = `
            <form id="managerLeadRequirementForm" novalidate onsubmit="submitManagerLeadRequirementForm(event); return false;">
                <input type="hidden" name="task_id" value="${currentTaskId}">
                
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Basic Information</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Name <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="manager_form_name"
                                   value="${data.lead_name || ''}"
                                   required
                                   placeholder="Enter lead name"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Mobile Number <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="tel" 
                                   name="phone" 
                                   id="manager_form_phone"
                                   value="${data.lead_phone || ''}"
                                   required
                                   placeholder="Enter phone number"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Lead Requirements</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Category Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Category <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="category" 
                                    id="manager_form_category" 
                                    required
                                    onchange="handleManagerCategoryChange(this.value)"
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Category --</option>
                                <option value="Residential" ${existingCategory === 'Residential' ? 'selected' : ''}>Residential</option>
                                <option value="Commercial" ${existingCategory === 'Commercial' ? 'selected' : ''}>Commercial</option>
                                <option value="Both" ${existingCategory === 'Both' ? 'selected' : ''}>Both</option>
                                <option value="N.A" ${existingCategory === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Preferred Location Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Preferred Location <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="preferred_location" 
                                    id="manager_form_preferred_location" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Preferred Location --</option>
                                <option value="Inside City" ${existingPreferredLocation === 'Inside City' ? 'selected' : ''}>Inside City</option>
                                <option value="Sitapur Road" ${existingPreferredLocation === 'Sitapur Road' ? 'selected' : ''}>Sitapur Road</option>
                                <option value="Hardoi Road" ${existingPreferredLocation === 'Hardoi Road' ? 'selected' : ''}>Hardoi Road</option>
                                <option value="Faizabad Road" ${existingPreferredLocation === 'Faizabad Road' ? 'selected' : ''}>Faizabad Road</option>
                                <option value="Sultanpur Road" ${existingPreferredLocation === 'Sultanpur Road' ? 'selected' : ''}>Sultanpur Road</option>
                                <option value="Shaheed Path" ${existingPreferredLocation === 'Shaheed Path' ? 'selected' : ''}>Shaheed Path</option>
                                <option value="Raebareily Road" ${existingPreferredLocation === 'Raebareily Road' ? 'selected' : ''}>Raebareily Road</option>
                                <option value="Kanpur Road" ${existingPreferredLocation === 'Kanpur Road' ? 'selected' : ''}>Kanpur Road</option>
                                <option value="Outer Ring Road" ${existingPreferredLocation === 'Outer Ring Road' ? 'selected' : ''}>Outer Ring Road</option>
                                <option value="Bijnor Road" ${existingPreferredLocation === 'Bijnor Road' ? 'selected' : ''}>Bijnor Road</option>
                                <option value="Deva Road" ${existingPreferredLocation === 'Deva Road' ? 'selected' : ''}>Deva Road</option>
                                <option value="Sushant Golf City" ${existingPreferredLocation === 'Sushant Golf City' ? 'selected' : ''}>Sushant Golf City</option>
                                <option value="Vrindavan Yojana" ${existingPreferredLocation === 'Vrindavan Yojana' ? 'selected' : ''}>Vrindavan Yojana</option>
                                <option value="N.A" ${existingPreferredLocation === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Type Field (dependent on Category) -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Type <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="type" 
                                    id="manager_form_type" 
                                    required
                                    ${!existingCategory ? 'disabled' : ''}
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; ${!existingCategory ? 'background-color: #f5f5f5;' : ''}">
                                <option value="">-- Select Type (select category first) --</option>
                            </select>
                        </div>
                        
                        <!-- Purpose Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Purpose <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="purpose" 
                                    id="manager_form_purpose" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Purpose --</option>
                                <option value="End Use" ${existingPurpose === 'End Use' ? 'selected' : ''}>End Use</option>
                                <option value="Short Term Investment" ${existingPurpose === 'Short Term Investment' ? 'selected' : ''}>Short Term Investment</option>
                                <option value="Long Term Investment" ${existingPurpose === 'Long Term Investment' ? 'selected' : ''}>Long Term Investment</option>
                                <option value="Rental Income" ${existingPurpose === 'Rental Income' ? 'selected' : ''}>Rental Income</option>
                                <option value="Investment + End Use" ${existingPurpose === 'Investment + End Use' ? 'selected' : ''}>Investment + End Use</option>
                                <option value="N.A" ${existingPurpose === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Possession Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Possession <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="possession" 
                                    id="manager_form_possession" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Possession --</option>
                                <option value="Under Construction" ${existingPossession === 'Under Construction' ? 'selected' : ''}>Under Construction</option>
                                <option value="Ready To Move" ${existingPossession === 'Ready To Move' ? 'selected' : ''}>Ready To Move</option>
                                <option value="Pre Launch" ${existingPossession === 'Pre Launch' ? 'selected' : ''}>Pre Launch</option>
                                <option value="Both" ${existingPossession === 'Both' ? 'selected' : ''}>Both</option>
                                <option value="N.A" ${existingPossession === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Budget Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Budget <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="budget" 
                                    id="manager_form_budget" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Budget --</option>
                                <option value="Below 50 Lacs" ${existingBudget === 'Below 50 Lacs' ? 'selected' : ''}>Below 50 Lacs</option>
                                <option value="50-75 Lacs" ${existingBudget === '50-75 Lacs' ? 'selected' : ''}>50-75 Lacs</option>
                                <option value="75 Lacs-1 Cr" ${existingBudget === '75 Lacs-1 Cr' ? 'selected' : ''}>75 Lacs-1 Cr</option>
                                <option value="Above 1 Cr" ${existingBudget === 'Above 1 Cr' ? 'selected' : ''}>Above 1 Cr</option>
                                <option value="Above 2 Cr" ${existingBudget === 'Above 2 Cr' ? 'selected' : ''}>Above 2 Cr</option>
                                <option value="N.A" ${existingBudget === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Follow Up Required Section -->
                <div style="margin-bottom: 24px;">
                    <div style="padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                        <div style="display: flex; align-items: center; margin-bottom: 12px;">
                            <input type="checkbox" 
                                   name="follow_up_required" 
                                   id="manager_form_follow_up_required"
                                   style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                            <label for="manager_form_follow_up_required" style="font-size: 14px; font-weight: 500; color: #333; cursor: pointer; margin: 0;">
                                <strong>Follow Up Required</strong>
                            </label>
                        </div>
                        <small style="display: block; color: #666; font-size: 12px; margin-left: 28px;">Check this if you need to schedule a follow-up call for this lead</small>
                        
                        <!-- Follow Up Date & Time Picker (shown conditionally when Follow Up Required is checked) -->
                        <div id="followUpDateContainer" style="display: none; margin-top: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Follow Up Date & Time</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="datetime-local" 
                                   name="follow_up_date" 
                                   id="manager_form_follow_up_date"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            <small style="display: block; margin-top: 4px; color: #666; font-size: 12px;">Select date and time for the follow-up call. A calling task will be created automatically.</small>
                            
                            <!-- Create Sales Executive Task Option (shown when Follow Up Required is checked) -->
                            <div id="createTelecallerTaskContainer" style="display: none; margin-top: 12px;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" 
                                           name="create_telecaller_task" 
                                           id="create_telecaller_task_checkbox"
                                           style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                    <label for="create_telecaller_task_checkbox" style="font-size: 14px; font-weight: 500; color: #333; cursor: pointer; margin: 0;">
                                        Create calling task for Sales Executive also
                                    </label>
                                </div>
                                <small style="display: block; color: #666; font-size: 12px; margin-left: 28px; margin-top: 4px;">
                                    This will create a calling task for the original Sales Executive who provided this lead
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Verification Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Lead Status</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="lead_status" id="manager_form_lead_status" required style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Lead Status --</option>
                                <option value="hot">Hot</option>
                                <option value="warm">Warm</option>
                                <option value="cold">Cold</option>
                                <option value="junk">Junk</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Lead Quality</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="lead_quality" id="manager_form_lead_quality" required style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Lead Quality --</option>
                                <option value="1">1 - Bad</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5 - Best Lead</option>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Interested Projects</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="text"
                                   id="manager_project_input"
                                   placeholder="Type project name and press Enter"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 12px;">
                            <div id="project-tags-container" class="project-tags-wrapper">
                                <div class="project-tags-grid" id="project-tags-grid">
                                    <!-- Project tags will be loaded dynamically -->
                                </div>
                            </div>
                            <input type="hidden" name="interested_projects" id="manager_form_interested_projects_hidden">
                        </div>
                    </div>
                    
                    <!-- Customer Profiling Section -->
                    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Customer Profiling <span style="color: #666; font-weight: 400; font-size: 14px;">(Optional)</span></h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Customer Job
                                </label>
                                <input type="text" 
                                       name="customer_job" 
                                       id="manager_form_customer_job"
                                       value="${formValues.customer_job || ''}"
                                       placeholder="Enter customer job / occupation"
                                       style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Industry / Sector
                                </label>
                                <select name="industry_sector" 
                                        id="manager_form_industry_sector"
                                        style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                    <option value="">-- Select Industry / Sector --</option>
                                    <option value="IT" ${formValues.industry_sector === 'IT' ? 'selected' : ''}>IT</option>
                                    <option value="Education" ${formValues.industry_sector === 'Education' ? 'selected' : ''}>Education</option>
                                    <option value="Healthcare" ${formValues.industry_sector === 'Healthcare' ? 'selected' : ''}>Healthcare</option>
                                    <option value="Business" ${formValues.industry_sector === 'Business' ? 'selected' : ''}>Business</option>
                                    <option value="FMCG" ${formValues.industry_sector === 'FMCG' ? 'selected' : ''}>FMCG</option>
                                    <option value="Government" ${formValues.industry_sector === 'Government' ? 'selected' : ''}>Government</option>
                                    <option value="Other" ${formValues.industry_sector === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Buying Frequency
                                </label>
                                <select name="buying_frequency" 
                                        id="manager_form_buying_frequency"
                                        style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                    <option value="">-- Select Buying Frequency --</option>
                                    <option value="Regular" ${formValues.buying_frequency === 'Regular' ? 'selected' : ''}>Regular</option>
                                    <option value="Occasional" ${formValues.buying_frequency === 'Occasional' ? 'selected' : ''}>Occasional</option>
                                    <option value="First-time" ${formValues.buying_frequency === 'First-time' ? 'selected' : ''}>First-time</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Living City
                                </label>
                                <input type="text" 
                                       name="living_city" 
                                       id="manager_form_living_city"
                                       value="${formValues.living_city || ''}"
                                       placeholder="Enter living city"
                                       style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    City Type
                                </label>
                                <select name="city_type" 
                                        id="manager_form_city_type"
                                        style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                    <option value="">-- Select City Type --</option>
                                    <option value="Metro" ${formValues.city_type === 'Metro' ? 'selected' : ''}>Metro</option>
                                    <option value="Tier 1" ${formValues.city_type === 'Tier 1' ? 'selected' : ''}>Tier 1</option>
                                    <option value="Tier 2" ${formValues.city_type === 'Tier 2' ? 'selected' : ''}>Tier 2</option>
                                    <option value="Tier 3" ${formValues.city_type === 'Tier 3' ? 'selected' : ''}>Tier 3</option>
                                    <option value="Local Resident" ${formValues.city_type === 'Local Resident' ? 'selected' : ''}>Local Resident</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 16px;">
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                            <strong>Manager Remark</strong>
                        </label>
                        <textarea name="manager_remark" id="manager_form_manager_remark" rows="3" placeholder="Enter remarks or notes..." style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"></textarea>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e0e0e0; margin-top: 24px;">
                    <button type="button" 
                            onclick="cancelManagerLeadRequirementForm()" 
                            style="padding: 10px 20px; border: 1px solid #ddd; border-radius: 6px; background: white; color: #333; cursor: pointer; font-size: 14px; font-weight: 500;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="padding: 10px 20px; background: #205A44; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>Verify Prospect
                    </button>
                </div>
            </form>
        `;
        
        try {
            container.innerHTML = formHTML;
            
            // Load interested projects
            loadInterestedProjectsForManager();
            
            // Add Enter key handler for custom project input
            const projectInput = document.getElementById('manager_project_input');
            if (projectInput) {
                projectInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const value = this.value.trim();
                        if (value) {
                            addManagerProjectTag(value);
                            this.value = ''; // Clear input after adding
                        }
                    }
                });
            }
            
            // Initialize dependent fields (category -> type)
            const categorySelect = document.getElementById('manager_form_category');
            const typeSelect = document.getElementById('manager_form_type');
            
            if (categorySelect && typeSelect) {
                // Initialize Type field based on existing category
                if (categorySelect.value) {
                    updateManagerTypeOptions(categorySelect.value, typeSelect, existingType);
                }
            }
        } catch (error) {
            console.error('Error inserting form HTML:', error);
            container.innerHTML = `
                <div style="padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 8px; color: #c33;">
                    <h4 style="margin: 0 0 10px 0;">Error Loading Form</h4>
                    <p style="margin: 0;">${error.message}</p>
                </div>
            `;
        }
        
        // Initialize Follow Up Required checkbox handler
        const followUpRequiredCheckbox = document.getElementById('manager_form_follow_up_required');
        if (followUpRequiredCheckbox) {
            followUpRequiredCheckbox.addEventListener('change', function() {
                handleFollowUpRequiredChange(this.checked);
            });
            // Initialize on page load if checkbox is already checked
            if (followUpRequiredCheckbox.checked) {
                handleFollowUpRequiredChange(true);
            }
        }
    }
    
    // Handle Follow Up Required checkbox change - show/hide follow-up date picker
    function handleFollowUpRequiredChange(isRequired) {
        const followUpContainer = document.getElementById('followUpDateContainer');
        const followUpDateInput = document.getElementById('manager_form_follow_up_date');
        const telecallerTaskContainer = document.getElementById('createTelecallerTaskContainer');
        
        if (isRequired) {
            // Show follow-up date picker
            if (followUpContainer) {
                followUpContainer.style.display = 'block';
                followUpContainer.style.visibility = 'visible';
            }
            if (followUpDateInput) {
                followUpDateInput.style.display = 'block';
                followUpDateInput.style.visibility = 'visible';
                followUpDateInput.removeAttribute('disabled');
                followUpDateInput.removeAttribute('readonly');
                // Don't set required attribute - we'll validate in JavaScript
                followUpDateInput.removeAttribute('required');
                followUpDateInput.required = false;
            }
            // Show telecaller task checkbox
            if (telecallerTaskContainer) {
                telecallerTaskContainer.style.display = 'block';
            }
        } else {
            // Hide follow-up date picker
            if (followUpContainer) {
                followUpContainer.style.display = 'none';
            }
            if (followUpDateInput) {
                // Always remove required attribute when hiding to prevent validation error
                followUpDateInput.removeAttribute('required');
                followUpDateInput.required = false;
                followUpDateInput.value = '';
            }
            // Hide telecaller task checkbox
            if (telecallerTaskContainer) {
                telecallerTaskContainer.style.display = 'none';
            }
            // Uncheck telecaller task checkbox when hiding
            const telecallerTaskCheckbox = document.getElementById('create_telecaller_task_checkbox');
            if (telecallerTaskCheckbox) {
                telecallerTaskCheckbox.checked = false;
            }
        }
    }

    // Handle form field changes for manager form
    function handleManagerFormFieldChange(fieldKey, value, dependentField = null) {
        if (fieldKey === 'category' && dependentField === 'type') {
            const typeSelect = document.getElementById('manager_form_type');
            if (typeSelect) {
                updateManagerTypeOptions(value, typeSelect);
            }
        }
    }

    // Handle category change for manager form
    window.handleManagerCategoryChange = function(category) {
        const typeSelect = document.getElementById('manager_form_type');
        if (typeSelect) {
            updateManagerTypeOptions(category, typeSelect);
        }
    };
    
    // Update type options based on category
    function updateManagerTypeOptions(category, typeSelect, existingValue = null) {
        const typeOptions = {
            'Residential': ['Plots & Villas', 'Apartments', 'Studio', 'Farmhouse', 'N.A'],
            'Commercial': ['Retail Shops', 'Office Space', 'Studio', 'N.A'],
            'Both': ['Plots & Villas', 'Apartments', 'Retail Shops', 'Office Space', 'Studio', 'Farmhouse', 'Agricultural', 'Others', 'N.A'],
            'N.A': ['N.A']
        };
        
        const currentValue = existingValue || typeSelect.value;
        const options = typeOptions[category] || typeOptions['Both'];
        
        // Enable/disable Type field based on category selection
        if (category && category !== '') {
            typeSelect.disabled = false;
            typeSelect.style.backgroundColor = '';
        } else {
            typeSelect.disabled = true;
            typeSelect.style.backgroundColor = '#f5f5f5';
        }
        
        typeSelect.innerHTML = '<option value="">-- Select Type (select category first) --</option>';
        options.forEach(option => {
            const selected = option === currentValue ? 'selected' : '';
            typeSelect.innerHTML += `<option value="${option}" ${selected}>${option}</option>`;
        });
        
        // If current value is not in the new options, clear it
        if (currentValue && !options.includes(currentValue)) {
            typeSelect.value = '';
        }
    }

    // Load interested projects for manager form (render as tags)
    async function loadInterestedProjectsForManager() {
        try {
            // Use full path for interested projects endpoint
            const response = await fetch('/api/interested-project-names', {
                headers: getAuthHeaders(),
            });
            const projectsResponse = await response.json();
            const projectTagsGrid = document.getElementById('project-tags-grid');
            
            if (projectsResponse && projectsResponse.success && projectsResponse.data && projectTagsGrid) {
                projectTagsGrid.innerHTML = '';
                projectsResponse.data.forEach(project => {
                    const tag = document.createElement('div');
                    tag.className = 'project-tag';
                    tag.dataset.projectId = project.id;
                    tag.innerHTML = `
                        <span class="project-tag-text">${escapeHtml(project.name)}</span>
                        <i class="fas fa-check project-tag-check"></i>
                    `;
                    tag.addEventListener('click', function() {
                        toggleProjectTag(this);
                    });
                    projectTagsGrid.appendChild(tag);
                });
            }
        } catch (error) {
            console.error('Error loading interested projects:', error);
        }
    }

    // Toggle project tag selection
    function toggleProjectTag(tagElement) {
        tagElement.classList.toggle('selected');
        updateSelectedProjects();
    }

    // Add a custom project tag for manager form
    function addManagerProjectTag(projectName) {
        const projectTagsGrid = document.getElementById('project-tags-grid');
        if (!projectTagsGrid || !projectName || !projectName.trim()) {
            return;
        }
        
        const trimmedName = projectName.trim();
        
        // Check if tag already exists (case-insensitive)
        const existingTags = projectTagsGrid.querySelectorAll('.project-tag');
        for (let tag of existingTags) {
            const tagText = tag.querySelector('.project-tag-text')?.textContent?.trim();
            if (tagText && tagText.toLowerCase() === trimmedName.toLowerCase()) {
                // Tag already exists, just select it
                tag.classList.add('selected');
                updateSelectedProjects();
                return;
            }
        }
        
        // Create new tag element
        const tag = document.createElement('div');
        tag.className = 'project-tag selected'; // Auto-select custom projects
        tag.dataset.projectName = trimmedName; // Use projectName instead of projectId for custom projects
        tag.dataset.isCustom = 'true'; // Flag to identify custom projects
        tag.innerHTML = `
            <span class="project-tag-text">${escapeHtml(trimmedName)}</span>
            <i class="fas fa-check project-tag-check"></i>
        `;
        tag.addEventListener('click', function() {
            toggleProjectTag(this);
        });
        
        projectTagsGrid.appendChild(tag);
        updateSelectedProjects();
    }

    // Update hidden input with selected project IDs
    function updateSelectedProjects() {
        const selectedTags = document.querySelectorAll('#project-tags-grid .project-tag.selected');
        const selectedProjects = Array.from(selectedTags).map(tag => {
            // Check if it's a custom project (has projectName) or database project (has projectId)
            if (tag.dataset.isCustom === 'true' && tag.dataset.projectName) {
                return { name: tag.dataset.projectName, is_custom: true };
            } else if (tag.dataset.projectId) {
                return parseInt(tag.dataset.projectId);
            }
            return null;
        }).filter(p => p !== null);
        
        const hiddenInput = document.getElementById('manager_form_interested_projects_hidden');
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(selectedProjects);
        }
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // Submit manager lead requirement form (verify)
    async function submitManagerLeadRequirementForm(event) {
        event.preventDefault();
        
        if (!currentTaskId) {
            showAlert('Task ID not found', 'error');
            return;
        }
        
        // Get follow-up required checkbox state
        const followUpRequiredCheckbox = document.getElementById('manager_form_follow_up_required');
        const isFollowUpRequired = followUpRequiredCheckbox ? followUpRequiredCheckbox.checked : false;
        
        // Handle required attribute for follow-up date before form validation
        // This prevents HTML5 validation error when field is hidden but required
        const followUpDateInput = document.getElementById('manager_form_follow_up_date');
        const followUpContainer = document.getElementById('followUpDateContainer');
        
        // Always ensure if container is hidden, required is removed (safety check)
        if (followUpContainer && followUpContainer.style.display === 'none') {
            if (followUpDateInput) {
                followUpDateInput.removeAttribute('required');
                followUpDateInput.required = false;
            }
        }
        
        const form = event.target;
        const formData = new FormData(form);
        
        // Convert FormData to object
        const data = {};
        
        formData.forEach((value, key) => {
            // Skip interested_projects as we'll get it from selected tags
            if (key !== 'interested_projects') {
                data[key] = value;
            }
        });
        
        // Add follow_up_required as boolean
        data['follow_up_required'] = isFollowUpRequired ? '1' : '0';
        
        // Validate Lead Quality
        if (!data['lead_quality'] || data['lead_quality'] === '') {
            showAlert('Please select Lead Quality', 'warning');
            const leadQualitySelect = document.getElementById('manager_form_lead_quality');
            if (leadQualitySelect) {
                leadQualitySelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    leadQualitySelect.focus();
                }, 100);
            }
            return;
        }
        
        // Get selected interested projects from tags (both IDs and custom names)
        const selectedTags = document.querySelectorAll('#project-tags-grid .project-tag.selected');
        data['interested_projects'] = Array.from(selectedTags).map(tag => {
            if (tag.dataset.isCustom === 'true' && tag.dataset.projectName) {
                return { name: tag.dataset.projectName, is_custom: true };
            } else if (tag.dataset.projectId) {
                return parseInt(tag.dataset.projectId);
            }
            return null;
        }).filter(p => p !== null);
        
        // Ensure interested_projects is an array
        if (!data['interested_projects'] || data['interested_projects'].length === 0) {
            showAlert('Please select at least one Interested Project', 'warning');
            return;
        }
        
        // Validate follow-up date & time if Follow Up Required is checked
        if (isFollowUpRequired) {
            if (!data['follow_up_date'] || data['follow_up_date'] === '') {
                showAlert('Please select a Follow Up Date & Time', 'warning');
                // Re-show the container and make input visible if validation fails
                if (followUpContainer) {
                    followUpContainer.style.display = 'block';
                }
                if (followUpDateInput) {
                    // Show the field but don't set required (we validate in JS)
                    followUpDateInput.style.display = 'block';
                    followUpDateInput.style.visibility = 'visible';
                    followUpDateInput.removeAttribute('required');
                    followUpDateInput.required = false;
                    // Scroll to the field
                    followUpDateInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        followUpDateInput.focus();
                    }, 100);
                }
                return;
            }
            
            // Validate that datetime is not in the past
            const selectedDateTime = new Date(data['follow_up_date']);
            const now = new Date();
            
            if (selectedDateTime <= now) {
                showAlert('Follow Up Date & Time cannot be in the past. Please select a future date and time.', 'warning');
                if (followUpDateInput) {
                    followUpDateInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        followUpDateInput.focus();
                    }, 100);
                }
                return;
            }
        } else {
            // Clear follow-up date if Follow Up Required is not checked (to avoid sending it to backend)
            data['follow_up_date'] = '';
            // Ensure required is removed and field is hidden when not Follow Up Required
            if (followUpDateInput) {
                followUpDateInput.removeAttribute('required');
                followUpDateInput.required = false;
            }
            if (followUpContainer) {
                followUpContainer.style.display = 'none';
            }
        }
        
        try {
            const response = await apiCall(`/tasks/${currentTaskId}/verify`, {
                method: 'POST',
                body: JSON.stringify(data)
            });
            
            if (response && response.success) {
                // Remove task card immediately from DOM
                const taskCard = document.getElementById(`task-card-${currentTaskId}`);
                if (taskCard) {
                    taskCard.style.transition = 'opacity 0.3s, transform 0.3s';
                    taskCard.style.opacity = '0';
                    taskCard.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        taskCard.remove();
                    // Reload tasks after card removal to ensure consistency (preserve current filters)
                    const status = window.currentStatus || currentStatus || 'all';
                    const dateFilter = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all';
                    const customDate = document.getElementById('customDatePicker')?.value || null;
                    loadTasks(status, dateFilter, customDate);
                    }, 300);
                } else {
                    // Fallback if card not found by ID
                    setTimeout(() => {
                        loadTasks();
                    }, 500);
                }
                
                const message = response.message || (isFollowUpRequired 
                    ? 'Follow-up task created successfully! Prospect will be called on the selected date and time.' 
                    : 'Prospect verified successfully!');
                showAlert(message, 'success', 3000);
                closeManagerLeadRequirementFormModal();
                currentTaskId = null; // Reset after successful submission
            } else {
                showAlert(response?.message || response?.error || 'Failed to process request', 'error');
            }
        } catch (error) {
            console.error('Error verifying prospect:', error);
            showAlert('Error verifying prospect: ' + error.message, 'error');
        }
    }

    // Remove all overdue tasks
    async function removeAllOverdueTasks() {
        if (!confirm('Are you sure you want to remove all overdue tasks? This action cannot be undone.')) {
            return;
        }

        const removeBtn = document.getElementById('removeAllOverdueBtn');
        if (removeBtn) {
            removeBtn.disabled = true;
            removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Removing...';
        }

        try {
            const result = await apiCall('/sales-manager/tasks/remove-all-overdue', {
                method: 'POST',
                body: JSON.stringify({})
            });

            if (result && result.success) {
                const count = result.count || 0;
                showAlert(`Successfully removed ${count} overdue task(s)`, 'success');
                
                // Reload tasks after a short delay (preserve current filters)
                setTimeout(() => {
                    const status = window.currentStatus || currentStatus || 'all';
                    const dateFilter = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all';
                    const customDate = document.getElementById('customDatePicker')?.value || null;
                    loadTasks(status, dateFilter, customDate);
                }, 500);
            } else {
                showAlert(result?.message || result?.error || 'Failed to remove overdue tasks', 'error');
            }
        } catch (error) {
            console.error('Error removing overdue tasks:', error);
            showAlert('Error removing overdue tasks: ' + error.message, 'error');
        } finally {
            if (removeBtn) {
                removeBtn.disabled = false;
                removeBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Remove All Overdue';
            }
        }
    }
    
    // Attach to window for global access
    window.removeAllOverdueTasks = removeAllOverdueTasks;

    // View prospect details (read-only)
    async function openProspectDetailModal(taskId, viewMode = false) {
        currentTaskId = taskId;
        const modal = document.getElementById('prospectDetailModal');
        const content = document.getElementById('prospectDetailContent');
        
        modal.classList.add('active');
        content.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner" style="display: inline-block;"></div><p style="margin-top: 15px; color: #666;">Loading...</p></div>';
        
        try {
            const result = await apiCall(`/tasks/${taskId}`);
            
            if (result && result.success && result.data) {
                const task = result.data;
                const lead = task.lead || {};
                const prospect = task.prospect || {};
                const formFields = lead.form_fields || {};
                const isProspect = prospect && prospect.id;
                const isFromGoogleSheets = lead.source === 'google_sheets';
                
                let detailsHTML = '';
                
                // Header with name and phone
                detailsHTML += `
                    <div style="background: linear-gradient(135deg, #205A44 0%, #063A1C 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 8px 0; font-size: 20px;">${lead.name || 'Lead'}</h3>
                        <p style="margin: 0; font-size: 16px; opacity: 0.9;">${lead.phone || '-'}</p>
                    </div>
                `;
                
                // Prospect Details Section
                if (isProspect) {
                    detailsHTML += `
                        <div style="margin-bottom: 20px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                            <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #063A1C;">Prospect Details</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div><strong>Customer Name:</strong> ${prospect.customer_name || '-'}</div>
                                <div><strong>Phone:</strong> ${prospect.phone || '-'}</div>
                                <div><strong>Budget:</strong> ${prospect.budget || '-'}</div>
                                <div><strong>Preferred Location:</strong> ${prospect.preferred_location || '-'}</div>
                                <div><strong>Size:</strong> ${prospect.size || '-'}</div>
                                <div><strong>Purpose:</strong> ${prospect.purpose || '-'}</div>
                                <div><strong>Possession:</strong> ${prospect.possession || '-'}</div>
                                <div><strong>Verification Status:</strong> ${prospect.verification_status || '-'}</div>
                                ${prospect.remark ? `<div style="grid-column: 1 / -1;"><strong>Remark:</strong> ${prospect.remark}</div>` : ''}
                                ${prospect.manager_remark ? `<div style="grid-column: 1 / -1;"><strong>Manager Remark:</strong> ${prospect.manager_remark}</div>` : ''}
                            </div>
                        </div>
                    `;
                }
                
                // Note: Google Sheets Details section removed as per user request
                
                // Form Data Section (if available)
                if (formFields && Object.keys(formFields).length > 0) {
                    detailsHTML += `
                        <div style="margin-bottom: 20px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                            <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #063A1C;">Form Data</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                ${Object.entries(formFields).map(([key, value]) => `
                                    <div>
                                        <strong>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</strong> ${value || '-'}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
                
                // Basic Lead Details (if not prospect and not from Google Sheets)
                if (!isProspect && !isFromGoogleSheets) {
                    detailsHTML += `
                        <div style="margin-bottom: 20px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                            <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #063A1C;">Lead Details</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div><strong>Email:</strong> ${lead.email || '-'}</div>
                                <div><strong>Status:</strong> ${lead.status || '-'}</div>
                            </div>
                        </div>
                    `;
                }
                
                content.innerHTML = `<div>${detailsHTML}</div>`;
            } else {
                showAlert('Failed to load task details', 'error');
                closeProspectDetailModal();
            }
        } catch (error) {
            console.error('Error loading task details:', error);
            showAlert('Error loading task details', 'error');
            closeProspectDetailModal();
        }
    }

    function closeProspectDetailModal() {
        const modal = document.getElementById('prospectDetailModal');
        modal.classList.remove('active');
        document.getElementById('prospectDetailContent').innerHTML = '';
        currentTaskId = null;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== DOM LOADED - INITIALIZING TASKS ===');
        console.log('API_BASE_URL:', API_BASE_URL);
        console.log('API_TOKEN available:', !!API_TOKEN);
        console.log('API_TOKEN value:', API_TOKEN ? API_TOKEN.substring(0, 20) + '...' : 'null');
        
        // Set up event delegation for filter buttons (instead of inline onclick)
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.getAttribute('data-status');
                console.log('Filter button clicked, status:', status);
                if (window.filterTasks) {
                    window.filterTasks(status);
                } else {
                    console.error('filterTasks function not available');
                    filterTasks(status); // Try direct call as fallback
                }
            });
        });
        
        // Set up status dropdown filter for mobile
        const filterDropdown = document.getElementById('taskFilterDropdown');
        if (filterDropdown) {
            filterDropdown.addEventListener('change', function(e) {
                const status = this.value;
                console.log('Status filter dropdown changed, status:', status);
                if (window.filterTasks) {
                    window.filterTasks(status);
                } else {
                    filterTasks(status);
                }
            });
            
            // Set initial value from saved filter
            try {
                const saved = localStorage.getItem('salesManagerTasksFilter');
                if (saved && ['all', 'pending', 'overdue', 'rescheduled', 'completed'].includes(saved)) {
                    filterDropdown.value = saved;
                }
            } catch (e) {
                console.error('Error reading saved filter:', e);
            }
        }

        const categoryDropdownDesktop = document.getElementById('taskTypeFilterDesktop');
        const categoryDropdownMobile = document.getElementById('taskTypeFilterMobile');

        function handleCategoryChange(categoryValue) {
            const currentStatusValue = window.currentStatus || currentStatus || 'all';
            const dateFilterValue = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all';
            const customDateValue = document.getElementById('customDatePicker')?.value || null;
            if (window.filterTasks) {
                window.filterTasks(currentStatusValue, dateFilterValue, customDateValue, categoryValue);
            } else {
                filterTasks(currentStatusValue, dateFilterValue, customDateValue, categoryValue);
            }
        }

        if (categoryDropdownDesktop) {
            categoryDropdownDesktop.addEventListener('change', function() {
                handleCategoryChange(this.value);
            });
        }

        if (categoryDropdownMobile) {
            categoryDropdownMobile.addEventListener('change', function() {
                handleCategoryChange(this.value);
            });
        }

        try {
            const savedCategory = localStorage.getItem('salesManagerTaskCategory');
            if (savedCategory && ['all', 'meeting', 'site_visit', 'prospect', 'closer', 'other'].includes(savedCategory)) {
                if (categoryDropdownDesktop) {
                    categoryDropdownDesktop.value = savedCategory;
                }
                if (categoryDropdownMobile) {
                    categoryDropdownMobile.value = savedCategory;
                }
                currentCategory = savedCategory;
                window.currentCategory = currentCategory;
            }
        } catch (e) {
            console.error('Error reading saved task category:', e);
        }
        
        // Set up date dropdown filters (mobile and desktop)
        const dateDropdownMobile = document.getElementById('dateFilterDropdown');
        const dateDropdownDesktop = document.getElementById('dateFilterDropdownDesktop');
        const customDatePicker = document.getElementById('customDatePicker');
        
        function handleDateFilterChange(dateFilter) {
            console.log('Date filter changed:', dateFilter);
            
            // Show/hide custom date picker
            if (customDatePicker) {
                if (dateFilter === 'custom') {
                    customDatePicker.style.display = 'block';
                    // Set default to today if no saved date
                    if (!customDatePicker.value) {
                        const savedCustomDate = localStorage.getItem('salesManagerCustomDate');
                        customDatePicker.value = savedCustomDate || new Date().toISOString().split('T')[0];
                    }
                } else {
                    customDatePicker.style.display = 'none';
                }
            }
            
            // Get current status and apply both filters
            const currentStatusValue = window.currentStatus || currentStatus || 'all';
            const currentCategoryValue = window.currentCategory || currentCategory || 'all';
            const customDate = (dateFilter === 'custom' && customDatePicker && customDatePicker.value) ? customDatePicker.value : null;
            
            if (window.filterTasks) {
                window.filterTasks(currentStatusValue, dateFilter, customDate, currentCategoryValue);
            } else {
                filterTasks(currentStatusValue, dateFilter, customDate, currentCategoryValue);
            }
        }
        
        if (dateDropdownMobile) {
            dateDropdownMobile.addEventListener('change', function(e) {
                handleDateFilterChange(this.value);
            });
            
            // Set initial value from saved filter
            try {
                const saved = localStorage.getItem('salesManagerDateFilter');
                if (saved && ['all', 'today', 'tomorrow', 'this_week', 'this_month', 'custom'].includes(saved)) {
                    dateDropdownMobile.value = saved;
                    if (saved === 'custom') {
                        handleDateFilterChange('custom');
                    }
                }
            } catch (e) {
                console.error('Error reading saved date filter:', e);
            }
        }
        
        if (dateDropdownDesktop) {
            dateDropdownDesktop.addEventListener('change', function(e) {
                handleDateFilterChange(this.value);
            });
            
            // Set initial value from saved filter
            try {
                const saved = localStorage.getItem('salesManagerDateFilter');
                if (saved && ['all', 'today', 'tomorrow', 'this_week', 'this_month', 'custom'].includes(saved)) {
                    dateDropdownDesktop.value = saved;
                    if (saved === 'custom') {
                        handleDateFilterChange('custom');
                    }
                }
            } catch (e) {
                console.error('Error reading saved date filter:', e);
            }
        }
        
        // Handle custom date picker change
        if (customDatePicker) {
            customDatePicker.addEventListener('change', function(e) {
                const customDate = this.value;
                console.log('Custom date changed:', customDate);
                
                // Save to localStorage
                try {
                    localStorage.setItem('salesManagerCustomDate', customDate);
                } catch (e) {
                    console.error('Failed to save custom date:', e);
                }
                
                // Get current filters and apply
                const currentStatusValue = window.currentStatus || currentStatus || 'all';
                const dateFilter = 'custom';
                const currentCategoryValue = window.currentCategory || currentCategory || 'all';
                
                if (window.filterTasks) {
                    window.filterTasks(currentStatusValue, dateFilter, customDate, currentCategoryValue);
                } else {
                    filterTasks(currentStatusValue, dateFilter, customDate, currentCategoryValue);
                }
            });
        }
        
        const tasksGridEl = document.getElementById('tasksGrid');
        console.log('Tasks grid element found:', !!tasksGridEl);
        
        if (tasksGridEl) {
            console.log('Calling filterTasks() with saved filter:', currentStatus);
            // Restore date filter
            let savedDateFilter = 'all';
            let savedCustomDate = null;
            let savedCategoryFilter = 'all';
            try {
                const saved = localStorage.getItem('salesManagerDateFilter');
                if (saved && ['all', 'today', 'tomorrow', 'this_week', 'this_month', 'custom'].includes(saved)) {
                    savedDateFilter = saved;
                }
                savedCustomDate = localStorage.getItem('salesManagerCustomDate');
                const savedCategory = localStorage.getItem('salesManagerTaskCategory');
                if (savedCategory && ['all', 'meeting', 'site_visit', 'prospect', 'closer', 'other'].includes(savedCategory)) {
                    savedCategoryFilter = savedCategory;
                }
            } catch (e) {
                console.error('Error reading saved date filter:', e);
            }
            
            // Use filterTasks instead of loadTasks to restore UI state
            filterTasks(currentStatus, savedDateFilter, savedCustomDate, savedCategoryFilter);
            
            // Auto-refresh every 60 seconds (1 minute) to move tasks from Rescheduled to Pending
            // Only refresh when page/tab is visible (not hidden)
            setInterval(function() {
                if (!document.hidden) {
                    console.log('Auto-refreshing tasks...');
                    const status = window.currentStatus || currentStatus || 'all';
                    const dateFilter = document.getElementById('dateFilterDropdown')?.value || document.getElementById('dateFilterDropdownDesktop')?.value || 'all';
                    const customDate = document.getElementById('customDatePicker')?.value || null;
                    const category = document.getElementById('taskTypeFilterDesktop')?.value || document.getElementById('taskTypeFilterMobile')?.value || 'all';
                    loadTasks(status, dateFilter, customDate, category);
                }
            }, 60000); // 60 seconds = 1 minute
        } else {
            console.error('ERROR: Tasks grid element not found on DOM ready!');
            // Try again after a short delay
            setTimeout(function() {
                const retryEl = document.getElementById('tasksGrid');
                if (retryEl) {
                    console.log('Tasks grid found on retry, calling filterTasks() with saved filter:', currentStatus);
                    // Use filterTasks instead of loadTasks to restore UI state
                    filterTasks(currentStatus);
                    
                    // Auto-refresh every 60 seconds (1 minute) to move tasks from Rescheduled to Pending
                    // Only refresh when page/tab is visible (not hidden)
                    setInterval(function() {
                        if (!document.hidden) {
                            console.log('Auto-refreshing tasks...');
                            loadTasks();
                        }
                    }, 60000); // 60 seconds = 1 minute
                } else {
                    console.error('ERROR: Tasks grid still not found after retry!');
                }
            }, 500);
        }
    });
    
    // Fallback: If DOMContentLoaded already fired, call immediately
    if (document.readyState === 'loading') {
        // DOM is still loading, wait for DOMContentLoaded
        console.log('DOM still loading, waiting for DOMContentLoaded...');
    } else {
        // DOM already loaded, call immediately
        console.log('DOM already loaded, initializing immediately...');
        setTimeout(function() {
            // Set up filter button event listeners
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const status = this.getAttribute('data-status');
                    console.log('Filter button clicked, status:', status);
                    if (window.filterTasks) {
                        window.filterTasks(status);
                    } else {
                        console.error('filterTasks function not available');
                        filterTasks(status); // Try direct call as fallback
                    }
                });
            });
            
            const tasksGridEl = document.getElementById('tasksGrid');
            if (tasksGridEl) {
                console.log('Tasks grid found in fallback, calling filterTasks() with saved filter:', currentStatus);
                // Use filterTasks instead of loadTasks to restore UI state
                filterTasks(currentStatus);
            }
        }, 100);
    }

    // Close modals on outside click (backdrop click)
    document.getElementById('verifyRejectPromptModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cancelVerifyRejectPrompt();
        }
    });
    
    document.getElementById('rejectReasonModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cancelRejectReasonModal();
        }
    });
    
    document.getElementById('managerLeadRequirementFormModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cancelManagerLeadRequirementForm();
        }
    });
    
    document.getElementById('cnpTimeSelectionModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cancelCnpTimeSelection();
        }
    });
    
    document.getElementById('prospectDetailModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeProspectDetailModal();
        }
    });
    
    // Note: loadTasks() is called in DOMContentLoaded event listener above (line 1820)
</script>
@endpush
