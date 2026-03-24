@extends('sales-manager.layout')

@section('title', 'Meetings - Senior Manager')
@section('page-title', 'Meetings')

@push('styles')
<style>
    #meetingsContainer {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1.25rem;
    }
    .meeting-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #3b82f6;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .meeting-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .meeting-card.completed {
        border-left-color: #10b981;
    }
    .meeting-card.cancelled {
        border-left-color: #ef4444;
    }
    .meeting-card.pending-verification {
        border-left-color: #f59e0b;
    }
    .meeting-header {
        display: flex;
        flex-direction: column;
        margin-bottom: 12px;
    }
    .meeting-actions {
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .meeting-actions .btn {
        width: 100%;
        margin-top: 0;
    }
    .meeting-info h3 {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 8px;
    }
    .meeting-info p {
        color: #6b7280;
        font-size: 14px;
        margin: 4px 0;
    }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-scheduled {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }
    .badge-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }
    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-verified {
        background: #d1fae5;
        color: #065f46;
    }
    .badge-confirmed {
        background: #dcfce7;
        color: #166534;
    }
    .badge-pending-conf {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-cancelled-conf {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-primary {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    .btn-success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    
    /* Hide empty states on mobile */
    @media (max-width: 767px) {
        .empty-state {
            display: none !important;
        }
    }
    .filters {
        background: white;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: nowrap;
        align-items: center;
    }
    
    .filters .filter-select,
    .filters .filter-btn {
        flex: 1;
        width: 25%;
        min-width: 0;
        box-sizing: border-box;
    }
    
    .mobile-text {
        display: none;
    }
    
    .desktop-text {
        display: inline;
    }
    
    @media (max-width: 768px) {
        #meetingsContainer {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        
        .meeting-card {
            padding: 16px;
        }
        
        .meeting-info h3 {
            font-size: 16px;
        }
        
        .meeting-info p {
            font-size: 13px;
        }
        
        .filters {
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 4px;
        }
        
        .filters .filter-select,
        .filters .filter-btn {
            width: 25%;
            flex: 0 0 25%;
            padding: 8px 6px;
            font-size: 12px;
            box-sizing: border-box;
        }
        
        .filters label {
            display: none;
        }
        
        /* Hide "My Meetings" title in phone view */
        h2.text-2xl {
            display: none !important;
        }
        
        /* Hide "Schedule New Meeting" button in phone view */
        div[style*="display: flex"][style*="justify-content: space-between"] > a.btn-primary,
        div[style*="display: flex"][style*="justify-content: space-between"] > button.btn-primary {
            display: none !important;
        }

        /* Show filter action buttons on phone view (one-click meeting/site visit) */
        .filters .filter-btn.btn.btn-primary {
            display: flex !important;
        }
        
        /* Also hide the entire header div container if needed */
        div[style*="display: flex"][style*="justify-content: space-between"] {
            margin-bottom: 0 !important;
        }
        
        /* Show mobile text, hide desktop text in phone view */
        .mobile-text {
            display: inline;
        }
        
        .desktop-text {
            display: none;
        }
        
        .filters input[type="date"] {
            width: 100%;
            padding: 10px;
        }
        
        #customDateInputs {
            width: 100%;
            flex-direction: column;
        }
        
        .filters > div:first-child {
            flex-direction: column;
            width: 100%;
        }
        
        /* Header responsive */
        div[style*="display: flex"][style*="justify-content: space-between"] {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        div[style*="display: flex"][style*="justify-content: space-between"] .btn {
            width: 100%;
        }
        
        /* Modal responsive */
        .modal-content {
            width: 95% !important;
            max-width: 95% !important;
            padding: 16px !important;
        }
        
        .form-group {
            margin-bottom: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
        }
        
        .form-group > div[style*="display: flex"] {
            flex-direction: column;
            gap: 10px;
        }
        
        .form-group > div[style*="display: flex"] button {
            width: 100%;
        }
    }
    .filter-select {
        padding: 8px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
    }
    .filters input[type="date"] {
        padding: 8px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
    }
    #customDateInputs {
        display: none;
        gap: 8px;
    }
    #customDateInputs.show {
        display: flex;
    }
</style>
@endpush

@section('content')
<div class="mb-6">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <h2 class="text-2xl font-bold text-gray-900">My Meetings</h2>
    </div>

    <div class="filters">
        <select id="statusFilter" class="filter-select" onchange="loadMeetings()">
            <option value="">All Status</option>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select id="verificationFilter" class="filter-select" onchange="loadMeetings()">
            <option value="">All Verification</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="rejected">Rejected</option>
        </select>
        <select id="dateFilter" class="filter-select" onchange="toggleCustomDate(); loadMeetings();">
            <option value="">All Dates</option>
            <option value="today">Today</option>
            <option value="this_week">This Week</option>
            <option value="this_month">This Month</option>
            <option value="this_year">This Year</option>
            <option value="custom">Custom Date</option>
        </select>
        <button type="button" class="filter-btn btn btn-primary" style="text-align: center; padding: 10px 16px; white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 6px;" onclick="openQuickMeetingModal()">
            <i class="fas fa-bolt"></i>
            <span class="desktop-text">1-Click Meeting</span>
            <span class="mobile-text">Meeting</span>
        </button>
        <div id="customDateInputs">
            <input type="date" id="dateFrom" class="filter-select" onchange="loadMeetings()">
            <input type="date" id="dateTo" class="filter-select" onchange="loadMeetings()">
        </div>
    </div>

    <div id="meetingsContainer">
        <div class="empty-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading meetings...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    
    function getToken() {
        return localStorage.getItem('sales_manager_token') || '{{ session("api_token") }}';
    }

    async function apiCall(endpoint, options = {}) {
        const token = getToken();
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return null;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers },
                credentials: 'same-origin',
            });

            if (response.status === 401) {
                localStorage.removeItem('sales_manager_token');
                window.location.href = '{{ route("login") }}';
                return null;
            }

            if (!response.ok) {
                const errorText = await response.text();
                try {
                    return JSON.parse(errorText);
                } catch (e) {
                    return { success: false, message: errorText };
                }
            }

            return await response.json();
        } catch (error) {
            console.error('API Call Error:', error);
            return { success: false, message: error.message };
        }
    }

    let leadOptionsCache = [];
    let isLeadOptionsLoaded = false;

    function setDefaultQuickMeetingTime() {
        const scheduledInput = document.getElementById('quickScheduledAt');
        if (!scheduledInput) return;
        const minDate = new Date();
        minDate.setMinutes(minDate.getMinutes() + 30);
        const iso = minDate.toISOString().slice(0, 16);
        scheduledInput.min = iso;
        if (!scheduledInput.value) {
            scheduledInput.value = iso;
        }
    }

    function toggleQuickMeetingModeFields() {
        const form = document.getElementById('quickMeetingForm');
        if (!form) return;
        
        const mode = form.querySelector('input[name="meeting_mode"]:checked')?.value;
        const onlineFields = document.getElementById('quickOnlineFields');
        const offlineFields = document.getElementById('quickOfflineFields');
        const locationInput = document.getElementById('quickLocationInput');
        const meetingLinkInput = form.querySelector('input[name="meeting_link"]');
        
        if (mode === 'online') {
            if (onlineFields) onlineFields.style.display = 'block';
            if (offlineFields) offlineFields.style.display = 'none';
            if (locationInput) locationInput.removeAttribute('required');
            if (meetingLinkInput) {
                meetingLinkInput.removeAttribute('required');
            }
        } else {
            if (onlineFields) onlineFields.style.display = 'none';
            if (offlineFields) offlineFields.style.display = 'block';
            if (locationInput) locationInput.setAttribute('required', 'required');
            if (meetingLinkInput) {
                meetingLinkInput.removeAttribute('required');
            }
        }
    }

    async function openQuickMeetingModal() {
        setDefaultQuickMeetingTime();
        document.getElementById('quickMeetingModal').classList.add('show');
        document.getElementById('quickMeetingError').textContent = '';

        if (!isLeadOptionsLoaded) {
            await loadLeadOptions();
            isLeadOptionsLoaded = true;
        }
        
        // Initialize meeting mode fields
        toggleQuickMeetingModeFields();
    }

    function closeQuickMeetingModal() {
        document.getElementById('quickMeetingModal').classList.remove('show');
        const form = document.getElementById('quickMeetingForm');
        if (form) {
            form.reset();
        }
        document.getElementById('leadSelect').value = '';
        document.getElementById('leadSearchInput').value = '';
        document.getElementById('quickMeetingError').textContent = '';
        // Reset to offline mode
        const offlineRadio = document.querySelector('#quickMeetingForm input[name="meeting_mode"][value="offline"]');
        if (offlineRadio) {
            offlineRadio.checked = true;
            toggleQuickMeetingModeFields();
        }
    }

    async function loadLeadOptions(search = '') {
        const query = search ? `?search=${encodeURIComponent(search)}` : '';
        const response = await apiCall(`/meetings/lead-options${query}`);

        if (response?.success) {
            leadOptionsCache = response.data || [];
            renderLeadOptions(leadOptionsCache);
        } else {
            document.getElementById('quickMeetingError').textContent = response?.message || 'Unable to load leads.';
        }
    }

    function renderLeadOptions(list) {
        const select = document.getElementById('leadSelect');
        if (!select) return;
        select.innerHTML = '<option value=\"\">Select lead</option>';
        list.forEach(lead => {
            const option = document.createElement('option');
            option.value = lead.id;
            option.textContent = `${lead.name || 'N/A'} (${lead.phone || 'N/A'})`;
            select.appendChild(option);
        });
    }

    function filterLeadOptions(term) {
        const searchTerm = term.trim().toLowerCase();
        if (!searchTerm) {
            renderLeadOptions(leadOptionsCache);
            return;
        }

        const filtered = leadOptionsCache.filter(lead => {
            const nameMatch = (lead.name || '').toLowerCase().includes(searchTerm);
            const phoneMatch = (lead.phone || '').toLowerCase().includes(searchTerm);
            return nameMatch || phoneMatch;
        });

        renderLeadOptions(filtered);
    }

    async function submitQuickMeeting(event) {
        if (event) {
            event.preventDefault();
        }
        
        const form = document.getElementById('quickMeetingForm');
        if (!form) {
            // Fallback for old button onclick
            const leadId = document.getElementById('leadSelect').value;
            const scheduledAt = document.getElementById('quickScheduledAt').value;
            const errorBox = document.getElementById('quickMeetingError');
            
            if (!leadId) {
                errorBox.textContent = 'Please select a lead.';
                return;
            }
            if (!scheduledAt) {
                errorBox.textContent = 'Please select date and time.';
                return;
            }
            return;
        }
        
        const formData = new FormData(form);
        const errorBox = document.getElementById('quickMeetingError');
        errorBox.textContent = '';

        const leadId = formData.get('lead_id');
        const scheduledAt = formData.get('scheduled_at');
        const meetingMode = formData.get('meeting_mode');
        const location = formData.get('location');
        const meetingLink = formData.get('meeting_link');

        if (!leadId) {
            errorBox.textContent = 'Please select a lead.';
            return;
        }

        if (!scheduledAt) {
            errorBox.textContent = 'Please select date and time.';
            return;
        }

        // Validate location for offline meetings
        if (meetingMode === 'offline' && !location) {
            errorBox.textContent = 'Location is required for offline meetings.';
            return;
        }

        const data = {
            lead_id: parseInt(leadId),
            meeting_sequence: parseInt(formData.get('meeting_sequence')) || 1,
            scheduled_at: new Date(scheduledAt).toISOString(),
            meeting_mode: meetingMode || 'offline',
            meeting_link: meetingLink || null,
            location: location || null,
            reminder_enabled: formData.get('reminder_enabled') === 'on',
            reminder_minutes: 5,
            meeting_notes: formData.get('meeting_notes') || null,
        };

        const result = await apiCall('/sales-manager/meetings/quick-schedule-with-reminder', {
            method: 'POST',
            body: JSON.stringify(data),
        });

        if (result?.success !== false) {
            closeQuickMeetingModal();
            if (typeof showNotification === 'function') {
                const message = 'Meeting scheduled successfully!' + (data.reminder_enabled ? ' You will get a reminder 5 minutes before.' : '');
                showNotification(message, 'success', 2500);
            } else {
                alert('Meeting scheduled successfully!');
            }
            loadMeetings();
        } else {
            errorBox.textContent = result?.message || 'Failed to schedule meeting.';
        }
    }

    function toggleCustomDate() {
        const dateFilter = document.getElementById('dateFilter').value;
        const customDateInputs = document.getElementById('customDateInputs');
        if (dateFilter === 'custom') {
            customDateInputs.classList.add('show');
        } else {
            customDateInputs.classList.remove('show');
        }
    }

    async function loadMeetings() {
        const container = document.getElementById('meetingsContainer');
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

        try {
            const status = document.getElementById('statusFilter').value;
            const verification = document.getElementById('verificationFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            
            let url = '/meetings?';
            if (status) url += `status=${status}&`;
            if (verification) url += `verification_status=${verification}&`;
            if (dateFilter) {
                url += `date_filter=${dateFilter}&`;
                if (dateFilter === 'custom') {
                    const dateFrom = document.getElementById('dateFrom').value;
                    const dateTo = document.getElementById('dateTo').value;
                    if (dateFrom) url += `date_from=${dateFrom}&`;
                    if (dateTo) url += `date_to=${dateTo}&`;
                }
            }

            const response = await apiCall(url);
            const meetings = response?.data || [];

            if (meetings.length === 0) {
                // Hide empty state on mobile - show nothing
                container.innerHTML = '';
                return;
            }

            const html = meetings.map(meeting => {
                const statusClass = meeting.status === 'completed' ? 'completed' : 
                                  meeting.status === 'cancelled' ? 'cancelled' : 
                                  meeting.verification_status === 'pending' && meeting.status === 'completed' ? 'pending-verification' : '';
                
                const statusBadge = meeting.status === 'scheduled' ? 'badge-scheduled' :
                                  meeting.status === 'completed' ? 'badge-completed' :
                                  'badge-cancelled';
                
                const verificationBadge = meeting.verification_status === 'verified' ? 'badge-verified' :
                                        meeting.verification_status === 'pending' ? 'badge-pending' : '';

                const confirmationStatusBadge = meeting.customer_confirmation_status === 'confirmed' ? 'badge-confirmed' :
                                              meeting.customer_confirmation_status === 'cancelled' ? 'badge-cancelled-conf' : 'badge-pending-conf';
                const confirmationStatusText = meeting.customer_confirmation_status || 'pending';

                return `
                    <div class="meeting-card ${statusClass}">
                        <div class="meeting-header">
                            <div class="meeting-info">
                                <h3>${meeting.customer_name || 'N/A'}</h3>
                                <p><i class="fas fa-phone mr-2"></i>${meeting.phone || 'N/A'}</p>
                                <p><i class="fas fa-calendar mr-2"></i>Scheduled: ${new Date(meeting.scheduled_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                ${meeting.completed_at ? `<p><i class="fas fa-check-circle mr-2"></i>Completed: ${new Date(meeting.completed_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>` : ''}
                                <p><i class="fas fa-tag mr-2"></i>Budget: ${meeting.budget_range || 'N/A'}</p>
                                ${meeting.property_type ? `<p><i class="fas fa-building mr-2"></i>Property: ${meeting.property_type}</p>` : ''}
                                ${meeting.meeting_mode ? `<p><i class="fas ${meeting.meeting_mode === 'online' ? 'fa-video' : 'fa-map-marker-alt'} mr-2"></i>${meeting.meeting_mode === 'online' ? 'Online' : 'Offline'} Meeting</p>` : ''}
                                ${meeting.location ? `<p><i class="fas fa-map-marker-alt mr-2"></i>${meeting.location}</p>` : ''}
                                ${meeting.meeting_link ? `<p><i class="fas fa-link mr-2"></i><a href="${meeting.meeting_link}" target="_blank" class="text-blue-600 hover:underline">Join Meeting</a></p>` : ''}
                                <div style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 4px;">
                                    <span class="badge ${statusBadge}">${meeting.status}</span>
                                    ${meeting.verification_status ? `<span class="badge ${verificationBadge}">${meeting.verification_status}</span>` : ''}
                                    <span class="badge ${confirmationStatusBadge}">${confirmationStatusText}</span>
                                </div>
                            </div>
                        </div>
                        <div class="meeting-actions">
                            ${meeting.lead_id ? `
                                <a href="/leads/${meeting.lead_id}" 
                                   class="btn" 
                                   style="background: linear-gradient(135deg, #063A1C 0%, #205A44 100%); color: white; margin-bottom: 8px; width: 100%; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-eye mr-2"></i>Lead Detail
                                </a>
                            ` : ''}
                            ${meeting.status === 'scheduled' && meeting.customer_confirmation_status !== 'cancelled' ? `
                                ${meeting.customer_confirmation_status === 'pending' ? `
                                    <button class="btn" style="background: #f59e0b; color: white;" onclick="rescheduleMeeting(${meeting.id})">
                                        <i class="fas fa-calendar mr-2"></i>Reschedule
                                    </button>
                                    <button class="btn btn-danger" onclick="cancelMeeting(${meeting.id})">
                                        <i class="fas fa-times mr-2"></i>Cancel Meeting
                                    </button>
                                ` : ''}
                                <button class="btn btn-success" onclick="showCompleteMeetingModal(${meeting.id})">
                                    <i class="fas fa-check mr-2"></i>Complete
                                </button>
                                <button class="btn btn-danger" onclick="showMarkDeadModal('meeting', ${meeting.id})">
                                    <i class="fas fa-skull mr-2"></i>Mark as Dead
                                </button>
                            ` : ''}
                            ${meeting.status === 'completed' ? `
                                ${meeting.verification_status === 'verified' ? `
                                    <button class="btn btn-primary" onclick="showConvertToSiteVisitModal(${meeting.id})">
                                        <i class="fas fa-exchange-alt mr-2"></i>Convert to Site Visit
                                    </button>
                                ` : ''}
                                ${meeting.verification_status === 'pending' ? `
                                    <div style="text-align: center; padding: 8px;">
                                        <span class="badge badge-pending">Awaiting Verification</span>
                                        ${meeting.pending_verification_with ? `<div class="mt-1 text-xs text-gray-600" style="margin-top: 4px;">Pending with: ${(meeting.pending_verification_with || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>` : ''}
                                    </div>
                                ` : ''}
                                <button class="btn btn-danger" onclick="showMarkDeadModal('meeting', ${meeting.id})">
                                    <i class="fas fa-skull mr-2"></i>Mark as Dead
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = html;
        } catch (error) {
            console.error('Error loading meetings:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading meetings</p></div>';
        }
    }

    let currentMeetingId = null;

    function showCompleteMeetingModal(id) {
        currentMeetingId = id;
        document.getElementById('completeMeetingModal').classList.add('show');
    }

    function closeCompleteMeetingModal() {
        document.getElementById('completeMeetingModal').classList.remove('show');
        document.getElementById('proofPhotosInput').value = '';
        document.getElementById('proofPhotosPreview').innerHTML = '';
        currentMeetingId = null;
    }

    // Helper function to complete the calling task
    async function completeCallingTask(taskId, taskType) {
        if (!taskId) return true; // No task to complete
        
        const apiToken = window.API_TOKEN || document.querySelector('meta[name="api-token"]')?.content;
        const apiBase = window.API_BASE_URL || '/api';
        
        if (!apiToken) {
            console.warn('API token not found, skipping task completion');
            return false;
        }
        
        try {
            let endpoint;
            if (taskType === 'Task') {
                // For manager tasks (Task model)
                endpoint = `${apiBase}/sales-manager/tasks/${taskId}/complete`;
            } else {
                // For telecaller tasks (TelecallerTask model)
                endpoint = `${apiBase}/telecaller/tasks/${taskId}/complete`;
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${apiToken}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                return true;
            } else {
                const result = await response.json();
                console.error('Failed to complete task:', result.message || 'Unknown error');
                return false;
            }
        } catch (error) {
            console.error('Error completing task:', error);
            return false;
        }
    }

    function handleProofPhotosChange(event) {
        const files = event.target.files;
        const preview = document.getElementById('proofPhotosPreview');
        preview.innerHTML = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                img.style.margin = '5px';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    }

    function parseJsonFromResponseText(responseText) {
        if (!responseText) return null;

        const trimmed = responseText.trim();
        try {
            return JSON.parse(trimmed);
        } catch (error) {
            const firstBrace = trimmed.indexOf('{');
            const lastBrace = trimmed.lastIndexOf('}');
            if (firstBrace === -1 || lastBrace === -1 || lastBrace <= firstBrace) {
                return null;
            }
            const jsonCandidate = trimmed.slice(firstBrace, lastBrace + 1);
            try {
                return JSON.parse(jsonCandidate);
            } catch (parseError) {
                return null;
            }
        }
    }

    function showMeetingSuccessModal(message) {
        const modal = document.getElementById('meetingSuccessModal');
        const messageElement = document.getElementById('meetingSuccessMessage');
        if (messageElement) {
            messageElement.textContent = message;
        }
        if (modal) {
            modal.classList.add('show');
        }
    }

    function closeMeetingSuccessModal() {
        const modal = document.getElementById('meetingSuccessModal');
        if (modal) {
            modal.classList.remove('show');
        }
    }

    // Close modal on backdrop click
    document.getElementById('meetingSuccessModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMeetingSuccessModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('meetingSuccessModal');
            if (modal && modal.classList.contains('show')) {
                closeMeetingSuccessModal();
            }
        }
    });

    async function submitCompleteMeeting() {
        if (!currentMeetingId) return;

        const formData = new FormData();
        const photosInput = document.getElementById('proofPhotosInput');
        
        if (!photosInput.files || photosInput.files.length === 0) {
            alert('Please upload at least one proof photo');
            return;
        }

        for (let i = 0; i < photosInput.files.length; i++) {
            formData.append('proof_photos[]', photosInput.files[i]);
        }

        const feedback = document.getElementById('meetingFeedback').value;
        const rating = document.getElementById('meetingRating').value;
        const notes = document.getElementById('meetingNotes').value;

        if (feedback) formData.append('feedback', feedback);
        if (rating) formData.append('rating', rating);
        if (notes) formData.append('meeting_notes', notes);

        try {
            const token = getToken();
            if (!token) {
                alert('Authentication error. Please refresh the page and try again.');
                return;
            }

            const response = await fetch(`${API_BASE_URL}/meetings/${currentMeetingId}/complete`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            // Read response as text first (can only read once)
            let responseText;
            let result;

            try {
                responseText = await response.text();
                result = parseJsonFromResponseText(responseText);
                if (!result) {
                    console.error('Invalid JSON response:', responseText.substring(0, 500));
                    alert('Server returned invalid JSON. Please try again.');
                    return;
                }
            } catch (textError) {
                console.error('Error reading response:', textError);
                alert('Network error. Please try again.');
                return;
            }

            if (response.ok && result && result.success) {
                // Complete calling task if pending
                if (window.pendingTaskCompletion) {
                    await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                    window.pendingTaskCompletion = null;
                }
                showMeetingSuccessModal('Meeting completed with proof photos! Awaiting verification.');
                closeCompleteMeetingModal();
                loadMeetings();
            } else {
                // Handle validation errors or other errors
                let errorMessage = result.message || 'Failed to complete meeting';
                
                if (result.errors) {
                    console.error('Validation errors:', result.errors);
                    // Format validation errors
                    const errorMessages = [];
                    if (result.errors.proof_photos) {
                        errorMessages.push('Proof photos: ' + result.errors.proof_photos.join(', '));
                    }
                    if (result.errors.feedback) {
                        errorMessages.push('Feedback: ' + result.errors.feedback.join(', '));
                    }
                    if (result.errors.rating) {
                        errorMessages.push('Rating: ' + result.errors.rating.join(', '));
                    }
                    if (result.errors.meeting_notes) {
                        errorMessages.push('Notes: ' + result.errors.meeting_notes.join(', '));
                    }
                    
                    if (errorMessages.length > 0) {
                        errorMessage = errorMessages.join('\n');
                    }
                }
                
                alert(errorMessage);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Network error. Please try again. ' + (error.message || ''));
        }
    }

    function showMarkDeadModal(type, id) {
        currentMeetingId = id;
        document.getElementById('deadReason').value = '';
        document.getElementById('markDeadModal').classList.add('show');
    }

    function closeMarkDeadModal() {
        document.getElementById('markDeadModal').classList.remove('show');
        document.getElementById('deadReason').value = '';
        currentMeetingId = null;
    }

    async function rescheduleMeeting(id) {
        if (!confirm('This will cancel the current meeting. Do you want to reschedule?')) {
            return;
        }

        const token = getToken();
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/meetings/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ reason: 'Rescheduled' })
            });

            const result = await response.json();

            if (response.ok) {
                alert('Meeting cancelled. Please create a new meeting with the updated time.');
                loadMeetings();
            } else {
                alert(result.message || 'Failed to cancel meeting');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while rescheduling the meeting');
        }
    }

    async function cancelMeeting(id) {
        if (!confirm('Are you sure you want to cancel this meeting?')) {
            return;
        }

        const token = getToken();
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/meetings/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ reason: 'Cancelled by manager' })
            });

            const result = await response.json();

            if (response.ok) {
                alert('Meeting cancelled successfully');
                loadMeetings();
            } else {
                alert(result.message || 'Failed to cancel meeting');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while cancelling the meeting');
        }
    }

    async function submitMarkDead() {
        if (!currentMeetingId) return;

        const reason = document.getElementById('deadReason').value.trim();
        if (!reason) {
            alert('Please provide a reason for marking as dead');
            return;
        }

        const result = await apiCall(`/meetings/${currentMeetingId}/mark-dead`, {
            method: 'POST',
            body: JSON.stringify({ reason }),
        });

        if (result && result.success) {
            // Complete calling task if pending
            if (window.pendingTaskCompletion) {
                await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                window.pendingTaskCompletion = null;
            }
            alert('Meeting marked as dead successfully');
            closeMarkDeadModal();
            loadMeetings();
        } else {
            alert(result.message || 'Failed to mark as dead');
        }
    }

    async function cancelMeeting(id) {
        if (!confirm('Cancel this meeting?')) return;

        const result = await apiCall(`/meetings/${id}/cancel`, {
            method: 'POST',
        });

        if (result && result.success) {
            // Complete calling task if pending
            if (window.pendingTaskCompletion) {
                await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                window.pendingTaskCompletion = null;
            }
            alert('Meeting cancelled');
            loadMeetings();
        } else {
            alert(result.message || 'Failed to cancel meeting');
        }
    }

    async function showConvertToSiteVisitModal(id) {
        currentMeetingId = id;
        
        // Reset form
        document.getElementById('convertProjectTagsContainer').innerHTML = '';
        document.getElementById('convertProjectInput').value = '';
        document.getElementById('convertProjectHidden').value = '';
        document.getElementById('convertScheduledAt').value = '';
        document.getElementById('convertVisitSequence').value = '';
        document.getElementById('reminderEnabled').value = 'false';
        document.getElementById('visitTypeWithFamily').checked = false;
        document.getElementById('visitTypeWithoutFamily').checked = false;
        toggleReminder(false); // Reset reminder buttons
        
        // Show modal
        document.getElementById('convertToSiteVisitModal').classList.add('show');
        
        try {
            // Load meeting data
            const meeting = await apiCall(`/meetings/${id}`);
            
            // Initialize project tags from meeting's project
            if (meeting && meeting.project) {
                addConvertProjectTag(meeting.project);
            }
            
            // Load telecallers and pre-select if lead has telecaller
            await loadTelecallersForConvert(meeting);
            
            // Set default scheduled date (next day from meeting scheduled date or tomorrow)
            const scheduledAtInput = document.getElementById('convertScheduledAt');
            let defaultDate = new Date();
            defaultDate.setDate(defaultDate.getDate() + 1); // Tomorrow
            
            if (meeting && meeting.scheduled_at) {
                const meetingDate = new Date(meeting.scheduled_at);
                meetingDate.setDate(meetingDate.getDate() + 1); // Next day from meeting
                if (meetingDate > new Date()) {
                    defaultDate = meetingDate;
                }
            }
            
            // Set minimum date (must be in future)
            const minDate = new Date();
            minDate.setHours(minDate.getHours() + 1); // At least 1 hour from now
            scheduledAtInput.min = minDate.toISOString().slice(0, 16);
            
            // Set default value
            scheduledAtInput.value = defaultDate.toISOString().slice(0, 16);
            
            // Setup project input Enter key handler
            setupConvertProjectInput();
            
        } catch (error) {
            console.error('Error loading convert form data:', error);
            alert('Error loading form data. Please try again.');
            closeConvertToSiteVisitModal();
        }
    }

    function closeConvertToSiteVisitModal() {
        document.getElementById('convertToSiteVisitModal').classList.remove('show');
        document.getElementById('convertToSiteVisitForm').reset();
        document.getElementById('convertProjectTagsContainer').innerHTML = '';
        document.getElementById('convertProjectHidden').value = '';
        toggleReminder(false);
        currentMeetingId = null;
    }
    
    // Project Tags Functions
    function setupConvertProjectInput() {
        const input = document.getElementById('convertProjectInput');
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = this.value.trim();
                    if (value) {
                        addConvertProjectTag(value);
                        this.value = '';
                    }
                }
            });
        }
        
    }
    
    function addConvertProjectTag(tagName) {
        const container = document.getElementById('convertProjectTagsContainer');
        const hiddenInput = document.getElementById('convertProjectHidden');
        
        if (!container || !hiddenInput) return;
        
        // Check if tag already exists
        const existingTags = Array.from(container.querySelectorAll('.site-visit-project-tag-text'));
        const tagExists = existingTags.some(tag => tag.textContent.trim() === tagName.trim());
        
        if (tagExists) {
            return; // Don't add duplicate
        }
        
        // Create tag element
        const tagElement = document.createElement('span');
        tagElement.className = 'site-visit-project-tag';
        tagElement.innerHTML = `
            <span class="site-visit-project-tag-text">${escapeHtml(tagName)}</span>
            <span class="site-visit-project-tag-remove" onclick="removeConvertProjectTag(this)">×</span>
        `;
        
        container.appendChild(tagElement);
        
        // Update hidden input with comma-separated values
        updateConvertProjectHiddenInput();
    }
    
    function removeConvertProjectTag(element) {
        const tagElement = element.closest('.site-visit-project-tag');
        if (tagElement) {
            tagElement.remove();
            updateConvertProjectHiddenInput();
        }
    }
    
    function updateConvertProjectHiddenInput() {
        const container = document.getElementById('convertProjectTagsContainer');
        const hiddenInput = document.getElementById('convertProjectHidden');
        
        if (!container || !hiddenInput) return;
        
        const tags = Array.from(container.querySelectorAll('.site-visit-project-tag-text'));
        const projectNames = tags.map(tag => tag.textContent.trim()).filter(name => name);
        hiddenInput.value = projectNames.join(',');
    }
    
    // Reminder Toggle Function (green theme)
    const reminderGreenStyle = 'linear-gradient(135deg, #063A1C 0%, #205A44 100%)';
    function toggleReminder(enabled) {
        const enableBtn = document.getElementById('enableReminderBtn');
        const disableBtn = document.getElementById('disableReminderBtn');
        const hiddenInput = document.getElementById('reminderEnabled');
        
        if (enabled) {
            enableBtn.style.background = reminderGreenStyle;
            enableBtn.style.color = 'white';
            enableBtn.classList.add('shadow-md');
            enableBtn.classList.remove('bg-gray-200', 'text-gray-700');
            disableBtn.style.background = '';
            disableBtn.style.color = '';
            disableBtn.classList.remove('shadow-md');
            disableBtn.classList.add('bg-gray-200', 'text-gray-700');
            hiddenInput.value = 'true';
        } else {
            disableBtn.style.background = reminderGreenStyle;
            disableBtn.style.color = 'white';
            disableBtn.classList.add('shadow-md');
            disableBtn.classList.remove('bg-gray-200', 'text-gray-700');
            enableBtn.style.background = '';
            enableBtn.style.color = '';
            enableBtn.classList.remove('shadow-md');
            enableBtn.classList.add('bg-gray-200', 'text-gray-700');
            hiddenInput.value = 'false';
        }
    }
    
    // Load Telecallers
    async function loadTelecallersForConvert(meeting = null) {
        try {
            // Try the telecallers endpoint first
            let telecallers = [];
            const apiBase = window.API_BASE_URL || '/api';
            try {
                const response = await fetch(`${apiBase}/telecallers`, {
                    headers: {
                        'Authorization': `Bearer ${getToken()}`,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    telecallers = data.telecallers || data.data || data || [];
                } else {
                    throw new Error('Telecallers endpoint not available');
                }
            } catch (error) {
                console.warn('Telecallers endpoint failed, trying users endpoint:', error);
                // Fallback: Try to get from users endpoint
                try {
                    const usersResponse = await fetch(`${apiBase}/users`, {
                        headers: {
                            'Authorization': `Bearer ${getToken()}`,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    
                    if (usersResponse.ok) {
                        const usersData = await usersResponse.json();
                        telecallers = (usersData.users || []).filter(u => {
                            const role = u.role || {};
                            return role.slug === 'sales_executive' || role.name === 'Sales Executive' || (typeof role === 'string' && role.toLowerCase().includes('sales executive'));
                        });
                    }
                } catch (e) {
                    console.error('Error loading telecallers from users endpoint:', e);
                }
            }
            
            // Get telecaller from lead if meeting has lead
            let leadTelecallerId = null;
            if (meeting && meeting.lead) {
                const lead = meeting.lead;
                
                // Get telecaller from active assignments
                if (lead.active_assignments && Array.isArray(lead.active_assignments) && lead.active_assignments.length > 0) {
                    const assignment = lead.active_assignments.find(a => {
                        const user = a.assigned_to || a.assignedTo;
                        if (!user) return false;
                        const role = user.role || {};
                        return role.slug === 'sales_executive' || role.name === 'Sales Executive' || (typeof role === 'string' && role.toLowerCase().includes('sales executive'));
                    });
                    
                    if (assignment) {
                        const user = assignment.assigned_to || assignment.assignedTo;
                        leadTelecallerId = user.id || user.user_id;
                    }
                }
                
                // Alternative: Check if lead has telecaller_id directly
                if (!leadTelecallerId && lead.telecaller_id) {
                    leadTelecallerId = lead.telecaller_id;
                }
            }
            
            populateTelecallerDropdown(telecallers, leadTelecallerId);
        } catch (error) {
            console.error('Error loading telecallers:', error);
        }
    }
    
    function populateTelecallerDropdown(telecallers, preSelectId = null) {
        const select = document.getElementById('convertTelecallerSelect');
        if (!select) return;
        
        select.innerHTML = '<option value="">Select Sales Executive (Optional)</option>';
        telecallers.forEach(telecaller => {
            const option = document.createElement('option');
            const telecallerId = telecaller.id || telecaller.user_id;
            option.value = telecallerId;
            option.textContent = `${telecaller.name || telecaller.user_name || 'Sales Executive'} (Sales Executive)`;
            
            // Pre-select if this is the lead's telecaller
            if (preSelectId && telecallerId == preSelectId) {
                option.selected = true;
            }
            
            select.appendChild(option);
        });
    }
    
    // Escape HTML helper
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function convertToSiteVisit(event) {
        if (event) {
            event.preventDefault();
        }
        
        if (!currentMeetingId) {
            alert('Meeting ID not found');
            return;
        }

        // Get form data
        const form = document.getElementById('convertToSiteVisitForm');
        const formData = new FormData(form);
        const project = formData.get('project') || document.getElementById('convertProjectHidden').value;
        const scheduledAt = formData.get('scheduled_at');
        const visitType = form.querySelector('input[name="visit_type"]:checked')?.value || null;
        const visitSequence = formData.get('visit_sequence') || null;
        const reminderEnabled = formData.get('reminder_enabled') === 'true';
        const reminderMinutes = parseInt(formData.get('reminder_minutes') || '10');
        const telecallerId = formData.get('telecaller_id') || null;

        // Validate
        if (!project || !project.trim()) {
            alert('Please select or type a project');
            return;
        }

        if (!scheduledAt) {
            alert('Please select a scheduled date and time');
            return;
        }

        // Validate scheduled date is in future
        const selectedDate = new Date(scheduledAt);
        if (selectedDate <= new Date()) {
            alert('Scheduled date and time must be in the future');
            return;
        }

        try {
            const result = await apiCall(`/meetings/${currentMeetingId}/convert-to-site-visit`, {
                method: 'POST',
                body: JSON.stringify({
                    project: project.trim(),
                    scheduled_at: scheduledAt,
                    visit_type: visitType,
                    visit_sequence: visitSequence,
                    reminder_enabled: reminderEnabled,
                    reminder_minutes: reminderMinutes,
                    telecaller_id: telecallerId,
                }),
            });

            if (result && result.success) {
                if (typeof showNotification === 'function') {
                    showNotification(result.message || 'Meeting converted to Site Visit successfully!', 'success', 3000);
                } else {
                    alert(result.message || 'Meeting converted to Site Visit successfully!');
                }
                closeConvertToSiteVisitModal();
                // Reload meetings list to show updated status
                loadMeetings();
            } else {
                alert(result.message || 'Failed to convert meeting');
            }
        } catch (error) {
            console.error('Error converting meeting:', error);
            alert('An error occurred while converting the meeting. Please try again.');
        }
    }

    // Reschedule Meeting
    function showRescheduleMeetingModal(id) {
        currentMeetingId = id;
        // Get meeting details
        apiCall(`/meetings/${id}`).then(meeting => {
            if (meeting && meeting.id) {
                const scheduledDate = new Date(meeting.scheduled_at);
                const minDateTime = new Date();
                minDateTime.setDate(minDateTime.getDate() + 1);
                minDateTime.setHours(0, 0, 0, 0);
                
                document.getElementById('rescheduleScheduledAt').value = '';
                document.getElementById('rescheduleReason').value = '';
                document.getElementById('rescheduleModalTitle').textContent = 'Reschedule Meeting';
                document.getElementById('rescheduleModalType').value = 'meeting';
                document.getElementById('rescheduleModalId').value = id;
                document.getElementById('rescheduleScheduledAt').min = minDateTime.toISOString().slice(0, 16);
                document.getElementById('rescheduleMeetingModal').classList.add('show');
            }
        });
    }

    function closeRescheduleMeetingModal() {
        document.getElementById('rescheduleMeetingModal').classList.remove('show');
        document.getElementById('rescheduleScheduledAt').value = '';
        document.getElementById('rescheduleReason').value = '';
        currentMeetingId = null;
    }

    async function submitRescheduleMeeting() {
        const type = document.getElementById('rescheduleModalType').value;
        const id = document.getElementById('rescheduleModalId').value;
        const scheduledAt = document.getElementById('rescheduleScheduledAt').value;
        const reason = document.getElementById('rescheduleReason').value.trim();

        if (!scheduledAt) {
            alert('Please select a new scheduled date and time');
            return;
        }

        if (!reason) {
            alert('Please provide a reason for rescheduling');
            return;
        }

        try {
            const result = await apiCall(`/${type === 'meeting' ? 'meetings' : 'site-visits'}/${id}/reschedule`, {
                method: 'POST',
                body: JSON.stringify({
                    scheduled_at: scheduledAt,
                    reason: reason,
                }),
            });

            if (result && result.success) {
                // Complete calling task if pending
                if (window.pendingTaskCompletion) {
                    await completeCallingTask(window.pendingTaskCompletion.taskId, window.pendingTaskCompletion.taskType);
                    window.pendingTaskCompletion = null;
                }
                if (typeof showNotification === 'function') {
                    showNotification(result.message || 'Rescheduled successfully! Verification required.', 'success', 3000);
                } else {
                    alert(result.message || 'Rescheduled successfully! Verification required.');
                }
                closeRescheduleMeetingModal();
                loadMeetings();
            } else {
                alert(result.message || 'Failed to reschedule');
                if (result.errors) {
                    console.error('Validation errors:', result.errors);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Network error. Please try again.');
        }
    }

    // Initialize
    (function() {
        loadMeetings();
    })();
</script>

<!-- Quick Schedule Meeting Modal -->
<div id="quickMeetingModal" class="modal">
    <div class="modal-content" style="max-width: 600px; padding: 0; overflow: hidden;">
        <!-- Header with gradient (matching lead page style) -->
        <div class="bg-gradient-to-r from-green-800 to-green-600 px-6 py-5 rounded-t-2xl w-full">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-check text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">Schedule Meeting</h3>
                </div>
                <button onclick="closeQuickMeetingModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Form Body -->
        <form id="quickMeetingForm" onsubmit="submitQuickMeeting(event)" class="flex flex-col flex-1 min-h-0">
            <div class="px-6 py-5 overflow-y-auto flex-1" style="max-height: 70vh;">
                <div class="space-y-4">
                    <!-- Lead Selection (only in meeting section) -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-green-600 mr-1"></i> Select Lead <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="leadSearchInput" placeholder="Search by name or phone" oninput="filterLeadOptions(this.value)" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 mb-2">
                        <select id="leadSelect" name="lead_id" required 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Loading...</option>
                        </select>
                        <small class="text-gray-500">Only leads from your Lead section are listed.</small>
                    </div>

                    <!-- Meeting Type -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tag text-green-600 mr-1"></i> Meeting Type
                        </label>
                        <select id="meeting_sequence" name="meeting_sequence" required 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 bg-white transition">
                            <option value="1">🎯 Fresh Meeting (1st)</option>
                            <option value="2">🔄 2nd Meeting</option>
                            <option value="3">⭐ 3rd Meeting</option>
                        </select>
                    </div>

                    <!-- Date & Time -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-green-600 mr-1"></i> Scheduled Date & Time
                        </label>
                        <input type="datetime-local" name="scheduled_at" id="quickScheduledAt" required 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                    </div>

                    <!-- Meeting Mode -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-video text-green-600 mr-1"></i> Meeting Mode
                        </label>
                        <div class="flex gap-3">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="meeting_mode" value="online" class="hidden peer" onchange="toggleQuickMeetingModeFields()">
                                <div class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-200 rounded-xl peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-700 transition hover:border-gray-300">
                                    <i class="fas fa-video"></i>
                                    <span class="font-medium">Online</span>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="meeting_mode" value="offline" checked class="hidden peer" onchange="toggleQuickMeetingModeFields()">
                                <div class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-200 rounded-xl peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-700 transition hover:border-gray-300">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="font-medium">Offline</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Conditional: Online = Link -->
                    <div id="quickOnlineFields" style="display:none;" class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-link text-green-600 mr-1"></i> Meeting Link (Optional)
                        </label>
                        <input type="url" name="meeting_link" placeholder="https://meet.google.com/..." 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                    </div>

                    <!-- Conditional: Offline = Location -->
                    <div id="quickOfflineFields" class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-green-600 mr-1"></i> Location
                        </label>
                        <input type="text" name="location" id="quickLocationInput" placeholder="Office address, project site, etc." 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                    </div>

                    <!-- Reminder Checkbox -->
                    <div class="form-group bg-gradient-to-br from-green-50 to-emerald-50 p-4 rounded-xl border-2 border-green-100">
                        <label class="flex items-start cursor-pointer group">
                            <input type="checkbox" name="reminder_enabled" checked 
                                class="mt-1 mr-3 w-5 h-5 text-green-600 rounded border-2 border-green-300 focus:ring-green-500">
                            <div>
                                <span class="text-sm font-semibold text-green-900 group-hover:text-green-700 transition">
                                    <i class="fas fa-bell text-green-600 mr-1"></i> Remind me before meeting
                                </span>
                                <p class="text-xs text-green-700 mt-1">Get a calling task 5 minutes before the meeting time</p>
                            </div>
                        </label>
                    </div>

                    <!-- Meeting Notes -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sticky-note text-green-600 mr-1"></i> Meeting Notes (Optional)
                        </label>
                        <textarea name="meeting_notes" id="quickMeetingNotes" rows="3" placeholder="Add any additional notes or agenda..." 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition resize-none"></textarea>
                    </div>
                </div>
            </div>

            <!-- Footer with buttons -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex gap-3 shrink-0">
                <button type="button" onclick="closeQuickMeetingModal()" 
                    class="flex-1 h-12 bg-white border-2 border-green-700 rounded-lg text-green-800 hover:bg-green-50 font-semibold transition-all shadow-sm flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="submit" 
                    class="flex-1 h-12 bg-gradient-to-r from-green-700 to-green-600 text-white rounded-lg hover:from-green-800 hover:to-green-700 font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check mr-2"></i>Schedule Meeting
                </button>
            </div>
        </form>

        <p id="quickMeetingError" style="color: #ef4444; min-height: 18px; padding: 0 24px 16px;"></p>
    </div>
</div>

<!-- Complete Meeting Modal -->
<div id="completeMeetingModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Complete Meeting</h3>
        <p style="color: #ef4444; margin-bottom: 16px;"><strong>Proof photos are required to complete the meeting.</strong></p>
        
        <div class="form-group">
            <label>Proof Photos <span style="color: #ef4444;">*</span></label>
            <input type="file" id="proofPhotosInput" multiple accept="image/*" onchange="handleProofPhotosChange(event)" required>
            <div id="proofPhotosPreview" style="display: flex; flex-wrap: wrap; margin-top: 10px;"></div>
            <small style="color: #6b7280;">Upload at least one photo as proof. Max 5MB per image.</small>
        </div>

        <div class="form-group">
            <label>Feedback</label>
            <textarea id="meetingFeedback" rows="3" placeholder="Meeting feedback..."></textarea>
        </div>

        <div class="form-group">
            <label>Rating</label>
            <select id="meetingRating">
                <option value="">Select rating</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>
        </div>

        <div class="form-group">
            <label>Notes</label>
            <textarea id="meetingNotes" rows="3" placeholder="Additional notes..."></textarea>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeCompleteMeetingModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitCompleteMeeting()">Submit</button>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div id="rescheduleMeetingModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3 id="rescheduleModalTitle" style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Reschedule</h3>
        <input type="hidden" id="rescheduleModalType" value="meeting">
        <input type="hidden" id="rescheduleModalId" value="">
        
        <div class="form-group">
            <label>New Scheduled Date & Time <span style="color: #ef4444;">*</span></label>
            <input type="datetime-local" id="rescheduleScheduledAt" required
                class="form-group input" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            <small style="color: #6b7280;">Select a future date and time</small>
        </div>

        <div class="form-group">
            <label>Reason for Rescheduling <span style="color: #ef4444;">*</span></label>
            <textarea id="rescheduleReason" rows="4" placeholder="Enter reason for rescheduling..." required
                class="form-group textarea" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"></textarea>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeRescheduleMeetingModal()">Cancel</button>
            <button type="button" class="btn" style="background: #f59e0b; color: white;" onclick="submitRescheduleMeeting()">Reschedule</button>
        </div>
    </div>
</div>

<!-- Mark as Dead Modal -->
<div id="markDeadModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Mark as Dead</h3>
        <p style="color: #ef4444; margin-bottom: 16px;">This will mark the meeting and associated lead as dead. This action cannot be undone.</p>
        
        <div class="form-group">
            <label>Reason <span style="color: #ef4444;">*</span></label>
            <textarea id="deadReason" rows="4" placeholder="Enter reason for marking as dead..." required></textarea>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeMarkDeadModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="submitMarkDead()">Mark as Dead</button>
        </div>
    </div>
</div>

<!-- Convert to Site Visit Modal -->
<div id="convertToSiteVisitModal" class="modal">
    <div class="modal-content" style="max-width: 600px; padding: 0; overflow: hidden;">
        <!-- Header with gradient (green theme - matches app) -->
        <div class="bg-gradient-to-r from-[#063A1C] to-[#205A44] px-6 py-5 rounded-t-2xl w-full">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-white text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">Convert to Site Visit</h3>
                </div>
                <button onclick="closeConvertToSiteVisitModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Form Body -->
        <form id="convertToSiteVisitForm" onsubmit="convertToSiteVisit(event)" class="flex flex-col flex-1 min-h-0">
            <div class="px-6 py-5 overflow-y-auto flex-1" style="max-height: 70vh;">
                <div class="space-y-4">
                    <!-- Project Selection -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-building mr-1" style="color: #205A44;"></i> Project <span style="color: #ef4444;">*</span>
                        </label>
                        <!-- Tag Container -->
                        <div id="convertProjectTagsContainer" class="flex flex-wrap gap-2 p-2 border-2 border-gray-200 rounded-xl min-h-[42px] bg-white mb-2">
                            <!-- Tags will be dynamically added here -->
                        </div>
                        <!-- Text Input -->
                        <input type="text" 
                               id="convertProjectInput" 
                               placeholder="Type project name and press Enter"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#205A44] focus:border-transparent transition">
                        <input type="hidden" name="project" id="convertProjectHidden" required>
                        <small class="text-gray-500">Type project name and press Enter</small>
                    </div>

                    <!-- Visit With Checkboxes -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-users mr-1" style="color: #205A44;"></i> Visit With
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="visit_type" value="with_family" id="visitTypeWithFamily"
                                    class="mr-2 w-4 h-4 border-gray-300 focus:ring-[#205A44]" style="accent-color: #205A44;">
                                <span class="text-sm text-gray-700">With Family</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="visit_type" value="without_family" id="visitTypeWithoutFamily"
                                    class="mr-2 w-4 h-4 border-gray-300 focus:ring-[#205A44]" style="accent-color: #205A44;">
                                <span class="text-sm text-gray-700">Without Family</span>
                            </label>
                        </div>
                    </div>

                    <!-- Visit Sequence -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-list-ol mr-1" style="color: #205A44;"></i> Visit Sequence
                        </label>
                        <select id="convertVisitSequence" name="visit_sequence" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#205A44] bg-white transition">
                            <option value="">Select Visit Sequence</option>
                            <option value="fresh_visit">Fresh Visit</option>
                            <option value="2nd_visit">2nd Visit</option>
                            <option value="3rd_visit">3rd Visit</option>
                        </select>
                        <small class="text-gray-500">Select the visit sequence</small>
                    </div>

                    <!-- Reminder Buttons -->
                    <div class="form-group p-4 rounded-xl border-2" style="background: linear-gradient(to bottom right, #f0fdf4, #dcfce7); border-color: #bbf7d0;">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-bell mr-1" style="color: #205A44;"></i> Reminder
                        </label>
                        <div class="flex gap-3">
                            <button type="button" id="enableReminderBtn" onclick="toggleReminder(true)"
                                class="flex-1 px-4 py-2 rounded-lg font-semibold transition-all flex items-center justify-center bg-gray-200 text-gray-700 hover:bg-gray-300">
                                <i class="fas fa-bell mr-2"></i>Enable Reminder
                            </button>
                            <button type="button" id="disableReminderBtn" onclick="toggleReminder(false)"
                                class="flex-1 px-4 py-2 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center text-white" style="background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);">
                                <i class="fas fa-bell-slash mr-2"></i>Disable Reminder
                            </button>
                        </div>
                        <input type="hidden" id="reminderEnabled" name="reminder_enabled" value="false">
                        <input type="hidden" id="reminderMinutes" name="reminder_minutes" value="10">
                        <small class="text-gray-500 mt-2 block">Get a calling task 10 minutes before the site visit</small>
                    </div>

                    <!-- Sales Executive Selection -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-tie mr-1" style="color: #205A44;"></i> Sales Executive (Optional)
                        </label>
                        <select id="convertTelecallerSelect" name="telecaller_id" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#205A44] bg-white transition">
                            <option value="">Select Sales Executive (Optional)</option>
                        </select>
                        <small class="text-gray-500">Create a calling task for selected Sales Executive 30 minutes before site visit</small>
                    </div>

                    <!-- Scheduled Date & Time -->
                    <div class="form-group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock mr-1" style="color: #205A44;"></i> Scheduled Date & Time <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="datetime-local" id="convertScheduledAt" name="scheduled_at" required 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#205A44] focus:border-transparent transition">
                        <small class="text-gray-500">Select date and time for the site visit</small>
                    </div>
                </div>
            </div>

            <!-- Footer with buttons -->
            <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex gap-3 shrink-0">
                <button type="button" onclick="closeConvertToSiteVisitModal()" 
                    class="flex-1 h-12 bg-white rounded-lg font-semibold transition-all shadow-sm flex items-center justify-center" style="border: 2px solid #205A44; color: #063A1C;" onmouseover="this.style.backgroundColor='#f0fdf4';" onmouseout="this.style.backgroundColor='white';">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="submit" 
                    class="flex-1 h-12 text-white rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center" style="background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);" onmouseover="this.style.opacity='0.95';" onmouseout="this.style.opacity='1';">
                    <i class="fas fa-exchange-alt mr-2"></i>Convert
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="meetingSuccessModal" class="modal">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 40px 30px;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
            <i class="fas fa-check" style="font-size: 40px; color: white; font-weight: bold;"></i>
        </div>
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 12px;">Success!</h3>
        <p id="meetingSuccessMessage" style="font-size: 16px; color: #666; margin-bottom: 30px; line-height: 1.5;"></p>
        <button onclick="closeMeetingSuccessModal()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 12px 32px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)';">
            <i class="fas fa-check mr-2"></i>OK
        </button>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal.show {
    display: flex;
}
.modal-content {
    background: white;
    padding: 24px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}
.form-group input[type="file"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
}
.btn-secondary {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.btn-secondary:hover {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}
/* Project Tags Styling */
.site-visit-project-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background-color: #3b82f6;
    color: white;
    border-radius: 9999px;
    font-size: 14px;
    font-weight: 500;
}
.site-visit-project-tag-remove {
    cursor: pointer;
    margin-left: 4px;
    opacity: 0.8;
    font-size: 12px;
}
.site-visit-project-tag-remove:hover {
    opacity: 1;
}
</style>
@endpush

