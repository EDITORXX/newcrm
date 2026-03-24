@extends('sales-manager.layout')

@section('title', 'Site Visits - Senior Manager')
@section('page-title', 'Site Visits')

@push('styles')
<style>
    #visitsContainer {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.5rem;
        align-items: stretch;
    }
    .visit-card {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        padding: 22px;
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(23, 97, 168, 0.08);
        border: 1px solid rgba(23, 97, 168, 0.10);
        border-left: 5px solid #1761A8;
        display: flex;
        flex-direction: column;
        min-height: 100%;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }
    .visit-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 52px rgba(23, 97, 168, 0.12);
    }
    .visit-card.completed {
        border-left-color: #10b981;
    }
    .visit-card.cancelled {
        border-left-color: #ef4444;
    }
    .visit-card.pending-verification {
        border-left-color: #f59e0b;
    }
    .visit-header {
        display: flex;
        flex-direction: column;
        margin-bottom: 18px;
    }
    .visit-actions {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .visit-actions .btn {
        width: 100%;
        margin-left: 0;
        margin-top: 0;
    }
    .visit-info h3 {
        font-size: 1.45rem;
        font-weight: 700;
        color: #0f3d67;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
    }
    .visit-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 18px;
    }
    .visit-meta {
        display: grid;
        gap: 10px;
    }
    .visit-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #475569;
        font-size: 0.95rem;
        line-height: 1.45;
    }
    .visit-meta-item i {
        width: 16px;
        color: #1761A8;
        margin-top: 4px;
        flex-shrink: 0;
    }
    .visit-meta-item strong {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .visit-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 18px;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.01em;
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
    .badge-closer-pending {
        background: #e0edff;
        color: #1d4ed8;
    }
    .badge-closer-verified {
        background: #10b981;
        color: white;
    }
    .btn {
        padding: 12px 16px;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 700;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-primary {
        background: linear-gradient(135deg, #1761A8 0%, #124a82 100%);
        color: white;
        box-shadow: 0 10px 22px rgba(23, 97, 168, 0.22);
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #124a82 0%, #0e3b67 100%);
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(23, 97, 168, 0.28);
    }
    .btn-success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 10px 22px rgba(22, 163, 74, 0.18);
    }
    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 10px 22px rgba(245, 158, 11, 0.18);
    }
    .status-note {
        text-align: center;
        padding: 12px;
        border-radius: 14px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.88rem;
        font-weight: 600;
        border: 1px solid #bfdbfe;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }
    .filters {
        background: white;
        padding: 16px;
        border-radius: 16px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .filters .filter-select,
    .filters .filter-btn {
        flex: 1;
        min-width: 0;
        box-sizing: border-box;
    }
    .filter-select,
    .filters input[type="date"] {
        padding: 10px 12px;
        border: 1px solid #d5dfeb;
        border-radius: 12px;
        font-size: 14px;
    }
    .mobile-text {
        display: none;
    }
    .desktop-text {
        display: inline;
    }
    #customDateInputs {
        display: none;
        gap: 8px;
    }
    #customDateInputs.show {
        display: flex;
    }
    @media (max-width: 767px) {
        .empty-state {
            display: none !important;
        }
    }
    @media (min-width: 769px) and (max-width: 1280px) {
        #visitsContainer {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        #visitsContainer {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .visit-card {
            padding: 16px;
            border-radius: 20px;
        }
        .visit-info h3 {
            font-size: 1.2rem;
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
        div[style*="display: flex"][style*="justify-content: space-between"] > h2,
        div[style*="display: flex"][style*="justify-content: space-between"] > a.btn-primary {
            display: none !important;
        }
        .filters .filter-btn.btn.btn-primary {
            display: flex !important;
        }
        .filters .filter-closer {
            display: none !important;
        }
        div[style*="display: flex"][style*="justify-content: space-between"] {
            margin-bottom: 0 !important;
        }
        .mobile-text {
            display: inline;
        }
        .desktop-text {
            display: none;
        }
        #customDateInputs {
            width: 100%;
            flex-direction: column;
        }
        .modal-content {
            width: 95% !important;
            max-width: 95% !important;
            padding: 16px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="mb-6">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <h2 class="text-2xl font-bold" style="color: #063A1C;">Site Visits</h2>
        <a href="{{ route('sales-manager.site-visits.create') }}" class="btn btn-primary desktop-text">
            <i class="fas fa-plus mr-2"></i>Schedule Site Visit
        </a>
    </div>

    <div class="filters">
        <select id="statusFilter" class="filter-select" onchange="loadSiteVisits()">
            <option value="">All Status</option>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select id="verificationFilter" class="filter-select" onchange="loadSiteVisits()">
            <option value="">All Verifica</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="rejected">Rejected</option>
        </select>
        <select id="dateFilter" class="filter-select" onchange="toggleCustomDate(); loadSiteVisits();">
            <option value="">All Dates</option>
            <option value="today">Today</option>
            <option value="this_week">This Week</option>
            <option value="this_month">This Month</option>
            <option value="this_year">This Year</option>
            <option value="custom">Custom Date</option>
        </select>
        <a href="{{ route('sales-manager.site-visits.create') }}" class="filter-btn btn btn-primary" style="text-align: center; padding: 10px 16px; white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 6px;">
            <i class="fas fa-map-marker-alt"></i>
            <span class="desktop-text">Schedule Site Visit</span>
            <span class="mobile-text">Visit</span>
        </a>
        <select id="closerFilter" class="filter-select filter-closer" onchange="loadSiteVisits()" style="min-width: 120px;">
            <option value="">Closer</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="rejected">Rejected</option>
        </select>
        <div id="customDateInputs">
            <input type="date" id="dateFrom" class="filter-select" onchange="loadSiteVisits()">
            <input type="date" id="dateTo" class="filter-select" onchange="loadSiteVisits()">
        </div>
    </div>

    <div id="visitsContainer">
        <div class="empty-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading site visits...</p>
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

    function toggleCustomDate() {
        const dateFilter = document.getElementById('dateFilter').value;
        const customDateInputs = document.getElementById('customDateInputs');
        if (dateFilter === 'custom') {
            customDateInputs.classList.add('show');
        } else {
            customDateInputs.classList.remove('show');
        }
    }

    // Helper function to format budget from lead
    function formatBudget(budget) {
        if (!budget) return 'N/A';
        const budgetNum = parseFloat(budget);
        if (isNaN(budgetNum)) return budget;
        
        const budgetInLacs = budgetNum / 100000;
        if (budgetInLacs < 50) return 'Under 50 Lac';
        if (budgetInLacs < 100) return '50 Lac – 1 Cr';
        if (budgetInLacs < 200) return '1 Cr – 2 Cr';
        if (budgetInLacs < 300) return '2 Cr – 3 Cr';
        return 'Above 3 Cr';
    }

    async function loadSiteVisits() {
        const container = document.getElementById('visitsContainer');
        container.innerHTML = '<div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

        try {
            const status = document.getElementById('statusFilter').value;
            const verification = document.getElementById('verificationFilter').value;
            const closer = document.getElementById('closerFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            
            let url = '/site-visits?';
            if (status && status !== 'all') url += `status=${status}&`;
            if (verification && verification !== 'all') url += `verification_status=${verification}&`;
            if (closer && closer !== 'all') url += `closer_status=${closer}&`;
            if (dateFilter && dateFilter !== 'all') {
                url += `date_filter=${dateFilter}&`;
                if (dateFilter === 'custom') {
                    const dateFrom = document.getElementById('dateFrom').value;
                    const dateTo = document.getElementById('dateTo').value;
                    if (dateFrom) url += `date_from=${dateFrom}&`;
                    if (dateTo) url += `date_to=${dateTo}&`;
                }
            }

            const response = await apiCall(url);
            
            // Handle paginated response
            let visits = [];
            if (response) {
                if (Array.isArray(response.data)) {
                    visits = response.data;
                } else if (Array.isArray(response)) {
                    visits = response;
                } else if (response.visits && Array.isArray(response.visits)) {
                    visits = response.visits;
                }
            }
            
            console.log('Site Visits Response:', { response, visitsCount: visits.length, visits });

            if (!visits || visits.length === 0) {
                // Hide empty state on mobile - show nothing
                container.innerHTML = '';
                return;
            }

            const html = visits.map(visit => {
                const statusClass = visit.status === 'completed' ? 'completed' : 
                                  visit.status === 'cancelled' ? 'cancelled' : 
                                  visit.verification_status === 'pending' && visit.status === 'completed' ? 'pending-verification' : '';
                
                const statusBadge = visit.status === 'scheduled' ? 'badge-scheduled' :
                                  visit.status === 'completed' ? 'badge-completed' :
                                  'badge-cancelled';
                
                const verificationBadge = visit.verification_status === 'verified' ? 'badge-verified' :
                                        visit.verification_status === 'pending' ? 'badge-pending' : '';
                
                const closerBadge = visit.closer_status === 'verified' ? 'badge-closer-verified' :
                                  visit.closer_status === 'pending' ? 'badge-closer-pending' : '';

                // Field fallback logic: site visit → lead → 'N/A'
                const customerName = visit.customer_name || (visit.lead && visit.lead.name) || visit.property_name || 'N/A';
                const phone = visit.phone || (visit.lead && visit.lead.phone) || 'N/A';
                const budget = visit.budget_range || (visit.lead && visit.lead.budget ? formatBudget(visit.lead.budget) : null) || 'N/A';
                const propertyType = visit.property_type || (visit.lead && visit.lead.property_type) || 'N/A';
                const project = visit.property_name || visit.project || (visit.lead && visit.lead.preferred_projects) || 'N/A';

                return `
                    <div class="visit-card ${statusClass}">
                        <div class="visit-header">
                            <div class="visit-info">
                                <h3>${customerName}</h3>
                                <div class="visit-subtitle">${phone}</div>
                                <div class="visit-meta">
                                    <div class="visit-meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <div><strong>Scheduled</strong>${new Date(visit.scheduled_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</div>
                                    </div>
                                    ${visit.completed_at ? `<div class="visit-meta-item"><i class="fas fa-check-circle"></i><div><strong>Completed</strong>${new Date(visit.completed_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</div></div>` : ''}
                                    <div class="visit-meta-item">
                                        <i class="fas fa-tag"></i>
                                        <div><strong>Budget</strong>${budget}</div>
                                    </div>
                                    ${propertyType !== 'N/A' ? `<div class="visit-meta-item"><i class="fas fa-building"></i><div><strong>Property</strong>${propertyType}</div></div>` : ''}
                                    ${project !== 'N/A' ? `<div class="visit-meta-item"><i class="fas fa-map-marker-alt"></i><div><strong>Project</strong>${project}</div></div>` : ''}
                                </div>
                                <div class="visit-status-row">
                                    <span class="badge ${statusBadge}">${visit.status}</span>
                                    ${visit.verification_status ? `<span class="badge ${verificationBadge}">${visit.verification_status}</span>` : ''}
                                    ${visit.closer_status ? `<span class="badge ${closerBadge}">Closer: ${visit.closer_status}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="visit-actions">
                            ${visit.status === 'scheduled' ? `
                                <button class="btn btn-success" onclick="showCompleteSiteVisitModal(${visit.id})">
                                    <i class="fas fa-check"></i>Complete
                                </button>
                                <button class="btn btn-warning" onclick="showRescheduleSiteVisitModal(${visit.id})">
                                    <i class="fas fa-calendar-alt"></i>Reschedule
                                </button>
                                <button class="btn btn-danger" onclick="showMarkDeadModal('site-visit', ${visit.id})">
                                    <i class="fas fa-skull"></i>Mark as Dead
                                </button>
                            ` : ''}
                            ${visit.status === 'completed' && visit.verification_status === 'pending' ? `
                                <div class="status-note">
                                    Awaiting Verification
                                </div>
                            ` : ''}
                            ${visit.verification_status === 'verified' && !visit.closer_status && !visit.closing_verification_status ? `
                                <button class="btn btn-primary" onclick="showRequestCloserModal(${visit.id})">
                                    <i class="fas fa-handshake"></i>Request Closing
                                </button>
                            ` : ''}
                            ${visit.closing_verification_status === 'pending' ? `
                                <div class="status-note">
                                    Closing Awaiting CRM Verification
                                </div>
                            ` : ''}
                            ${visit.closing_verification_status === 'verified' && visit.closer_status === 'verified' ? `
                                <div style="text-align: center; padding: 8px; margin-bottom: 8px;">
                                    <span class="badge badge-closer-verified">Closing Verified ✓</span>
                                </div>
                                <button class="btn btn-success" onclick="showRequestIncentiveModal(${visit.id})" style="width: 100%;">
                                    <i class="fas fa-money-bill-wave"></i>Request Incentive
                                </button>
                            ` : ''}
                            ${visit.closing_verification_status === 'rejected' ? `
                                <div style="text-align: center; padding: 8px;">
                                    <span class="badge" style="background: #ef4444; color: white;">Closing Rejected</span>
                                </div>
                            ` : ''}
                            ${visit.status === 'completed' ? `
                                <button class="btn btn-danger" onclick="showMarkDeadModal('site-visit', ${visit.id})">
                                    <i class="fas fa-skull"></i>Mark as Dead
                                </button>
                            ` : ''}
                            ${visit.lead_id ? `
                                <a href="/leads/${visit.lead_id}" target="_blank" class="btn btn-primary" style="width: 100%; margin-top: 8px; text-align: center; text-decoration: none;">
                                    <i class="fas fa-eye"></i>View Detail
                                </a>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = html;
        } catch (error) {
            console.error('Error loading site visits:', error);
            container.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error loading site visits</p></div>';
        }
    }

    let currentSiteVisitId = null;

    async function showCompleteSiteVisitModal(id) {
        currentSiteVisitId = id;
        
        try {
            // Fetch site visit data to get existing project
            // Use direct fetch since apiResource route is at /api/site-visits
            const token = getToken();
            const fetchResponse = await fetch(`/api/site-visits/${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            if (!fetchResponse.ok) {
                throw new Error('Failed to fetch site visit data');
            }
            
            const siteVisit = await fetchResponse.json();
            
            // Reset form fields
            document.getElementById('siteVisitProofPhotosInput').value = '';
            document.getElementById('siteVisitProofPhotosPreview').innerHTML = '';
            document.getElementById('siteVisitFeedback').value = '';
            document.getElementById('siteVisitRating').value = '';
            document.getElementById('siteVisitNotes').value = '';
            document.getElementById('completeTentativeClosingTime').value = '';
            
            // Clear and initialize project tags
            const container = document.getElementById('completeProjectTagsContainer');
            const hiddenInput = document.getElementById('completeProjectHidden');
            const input = document.getElementById('completeProjectInput');
            
            if (container) container.innerHTML = '';
            if (hiddenInput) hiddenInput.value = '';
            if (input) input.value = '';
            
            // Initialize project tags from existing project field
            if (siteVisit && siteVisit.project) {
                const projects = siteVisit.project.split(',').map(p => p.trim()).filter(p => p);
                projects.forEach(projectName => {
                    addCompleteProjectTag(projectName);
                });
            }
            
            // Setup project input handler
            setupCompleteProjectInput();
            
            // Show modal
            document.getElementById('completeSiteVisitModal').classList.add('show');
        } catch (error) {
            console.error('Error loading site visit data:', error);
            // Still show modal even if fetch fails
            document.getElementById('completeSiteVisitModal').classList.add('show');
            setupCompleteProjectInput();
        }
    }

    function closeCompleteSiteVisitModal() {
        document.getElementById('completeSiteVisitModal').classList.remove('show');
        document.getElementById('siteVisitProofPhotosInput').value = '';
        document.getElementById('siteVisitProofPhotosPreview').innerHTML = '';
        document.getElementById('siteVisitFeedback').value = '';
        document.getElementById('siteVisitRating').value = '';
        document.getElementById('siteVisitNotes').value = '';
        document.getElementById('completeTentativeClosingTime').value = '';
        document.getElementById('completeProjectTagsContainer').innerHTML = '';
        document.getElementById('completeProjectHidden').value = '';
        document.getElementById('completeProjectInput').value = '';
        currentSiteVisitId = null;
    }
    
    // Project Tags Functions for Complete Site Visit
    function setupCompleteProjectInput() {
        const input = document.getElementById('completeProjectInput');
        if (input) {
            // Remove any existing event listeners by cloning the input
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            
            newInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = this.value.trim();
                    if (value) {
                        addCompleteProjectTag(value);
                        this.value = '';
                    }
                }
            });
        }
    }
    
    function addCompleteProjectTag(tagName) {
        const container = document.getElementById('completeProjectTagsContainer');
        const hiddenInput = document.getElementById('completeProjectHidden');
        
        if (!container || !hiddenInput) return;
        
        // Check if tag already exists
        const existingTags = Array.from(container.querySelectorAll('.complete-project-tag-text'));
        const tagExists = existingTags.some(tag => tag.textContent.trim() === tagName.trim());
        
        if (tagExists) {
            return; // Don't add duplicate
        }
        
        // Create tag element
        const tagElement = document.createElement('span');
        tagElement.className = 'complete-project-tag';
        tagElement.innerHTML = `
            <span class="complete-project-tag-text">${escapeHtml(tagName)}</span>
            <span class="complete-project-tag-remove" onclick="removeCompleteProjectTag(this)">×</span>
        `;
        
        container.appendChild(tagElement);
        
        // Update hidden input with comma-separated values
        updateCompleteProjectHiddenInput();
    }
    
    function removeCompleteProjectTag(element) {
        const tagElement = element.closest('.complete-project-tag');
        if (tagElement) {
            tagElement.remove();
            updateCompleteProjectHiddenInput();
        }
    }
    
    function updateCompleteProjectHiddenInput() {
        const container = document.getElementById('completeProjectTagsContainer');
        const hiddenInput = document.getElementById('completeProjectHidden');
        
        if (!container || !hiddenInput) return;
        
        const tags = Array.from(container.querySelectorAll('.complete-project-tag-text'));
        const projectNames = tags.map(tag => tag.textContent.trim()).filter(name => name);
        hiddenInput.value = projectNames.join(',');
    }
    
    // Escape HTML helper
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function handleSiteVisitProofPhotosChange(event) {
        const files = event.target.files;
        const preview = document.getElementById('siteVisitProofPhotosPreview');
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

    function showSiteVisitSuccessModal(message) {
        const modal = document.getElementById('siteVisitSuccessModal');
        const messageElement = document.getElementById('siteVisitSuccessMessage');
        if (messageElement) {
            messageElement.textContent = message;
        }
        if (modal) {
            modal.classList.add('show');
        }
    }

    function closeSiteVisitSuccessModal() {
        const modal = document.getElementById('siteVisitSuccessModal');
        if (modal) {
            modal.classList.remove('show');
        }
    }

    // Close modal on backdrop click
    document.getElementById('siteVisitSuccessModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeSiteVisitSuccessModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('siteVisitSuccessModal');
            if (modal && modal.classList.contains('show')) {
                closeSiteVisitSuccessModal();
            }
        }
    });

    async function submitCompleteSiteVisit() {
        if (!currentSiteVisitId) return;

        const formData = new FormData();
        const photosInput = document.getElementById('siteVisitProofPhotosInput');
        
        if (!photosInput.files || photosInput.files.length === 0) {
            alert('Please upload at least one proof photo');
            return;
        }

        for (let i = 0; i < photosInput.files.length; i++) {
            formData.append('proof_photos[]', photosInput.files[i]);
        }

        const feedback = document.getElementById('siteVisitFeedback').value;
        const rating = document.getElementById('siteVisitRating').value;
        const notes = document.getElementById('siteVisitNotes').value;
        const visitedProjects = document.getElementById('completeProjectHidden').value;
        const tentativeClosingTime = document.getElementById('completeTentativeClosingTime').value;

        if (feedback) formData.append('feedback', feedback);
        if (rating) formData.append('rating', rating);
        if (notes) formData.append('visit_notes', notes);
        if (visitedProjects) formData.append('visited_projects', visitedProjects);
        if (tentativeClosingTime) formData.append('tentative_closing_time', tentativeClosingTime);

        try {
            const token = getToken();
            const response = await fetch(`${API_BASE_URL}/site-visits/${currentSiteVisitId}/complete`, {
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
                showSiteVisitSuccessModal('Site visit completed with proof photos! Awaiting verification.');
                closeCompleteSiteVisitModal();
                loadSiteVisits();
            } else {
                // Handle validation errors or other errors
                let errorMessage = result.message || 'Failed to complete site visit';
                
                if (result.errors) {
                    console.error('Validation errors:', result.errors);
                    // Format validation errors
                    const errorMessages = [];
                    if (result.errors.proof_photos) {
                        errorMessages.push('Proof photos: ' + (Array.isArray(result.errors.proof_photos) ? result.errors.proof_photos.join(', ') : result.errors.proof_photos));
                    }
                    if (result.errors.feedback) {
                        errorMessages.push('Feedback: ' + (Array.isArray(result.errors.feedback) ? result.errors.feedback.join(', ') : result.errors.feedback));
                    }
                    if (result.errors.rating) {
                        errorMessages.push('Rating: ' + (Array.isArray(result.errors.rating) ? result.errors.rating.join(', ') : result.errors.rating));
                    }
                    if (result.errors.visit_notes) {
                        errorMessages.push('Notes: ' + (Array.isArray(result.errors.visit_notes) ? result.errors.visit_notes.join(', ') : result.errors.visit_notes));
                    }
                    if (result.errors.visited_projects) {
                        errorMessages.push('Visited Projects: ' + (Array.isArray(result.errors.visited_projects) ? result.errors.visited_projects.join(', ') : result.errors.visited_projects));
                    }
                    if (result.errors.tentative_closing_time) {
                        errorMessages.push('Tentative Closing Time: ' + (Array.isArray(result.errors.tentative_closing_time) ? result.errors.tentative_closing_time.join(', ') : result.errors.tentative_closing_time));
                    }
                    
                    if (errorMessages.length > 0) {
                        errorMessage = errorMessages.join('\n');
                    }
                }
                
                alert(errorMessage);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Network error. Please try again.');
        }
    }

    function showRequestCloserModal(id) {
        currentSiteVisitId = id;
        document.getElementById('requestCloserModal').classList.add('show');
    }

    function showRequestIncentiveModal(id) {
        currentSiteVisitId = id;
        // Show a simple prompt or modal for incentive request
        const amount = prompt('Enter incentive amount:');
        if (amount && parseFloat(amount) > 0) {
            requestIncentive(id, parseFloat(amount));
        }
    }

    async function requestIncentive(siteVisitId, amount) {
        try {
            const token = getToken();
            const response = await fetch(`${API_BASE_URL}/site-visits/${siteVisitId}/request-incentive`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    type: 'closer',
                    amount: amount,
                }),
            });

            const result = await response.json();
            if (result && result.success) {
                showSiteVisitSuccessModal('Incentive request submitted! Awaiting Finance Manager approval.');
                loadSiteVisits();
            } else {
                alert(result.message || 'Failed to request incentive');
            }
        } catch (error) {
            console.error('Error requesting incentive:', error);
            alert('Network error. Please try again.');
        }
    }

    function closeRequestCloserModal() {
        document.getElementById('requestCloserModal').classList.remove('show');
        // Reset form
        document.getElementById('closingRequestForm').reset();
        document.getElementById('closerProofPhotosPreview').innerHTML = '';
        document.getElementById('closerKycDocumentsPreview').innerHTML = '';
        currentSiteVisitId = null;
    }

    function handleCloserProofPhotosChange(event) {
        const files = event.target.files;
        const preview = document.getElementById('closerProofPhotosPreview');
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

    function handleCloserKycDocumentsChange(event) {
        const files = event.target.files;
        const preview = document.getElementById('closerKycDocumentsPreview');
        preview.innerHTML = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.margin = '5px';
                div.style.position = 'relative';
                
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.style.border = '2px solid #e0e0e0';
                    div.appendChild(img);
                } else {
                    const icon = document.createElement('div');
                    icon.innerHTML = '<i class="fas fa-file-pdf" style="font-size: 48px; color: #ef4444;"></i>';
                    icon.style.textAlign = 'center';
                    icon.style.padding = '20px';
                    icon.style.border = '2px solid #e0e0e0';
                    icon.style.borderRadius = '8px';
                    icon.style.background = '#f9fafb';
                    div.appendChild(icon);
                }
                
                const fileName = document.createElement('div');
                fileName.textContent = file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name;
                fileName.style.fontSize = '11px';
                fileName.style.marginTop = '4px';
                fileName.style.textAlign = 'center';
                fileName.style.color = '#6b7280';
                div.appendChild(fileName);
                
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }
    }

    async function submitRequestCloser() {
        if (!currentSiteVisitId) return;

        // Validate form
        const form = document.getElementById('closingRequestForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData();
        
        // KYC fields
        const customerName = document.getElementById('closerCustomerName').value.trim();
        const nomineeName = document.getElementById('closerNomineeName').value.trim();
        const secondCustomerName = document.getElementById('closerSecondCustomerName').value.trim();
        const customerDob = document.getElementById('closerCustomerDob').value;
        const panCard = document.getElementById('closerPanCard').value.trim().toUpperCase();
        const aadhaarCard = document.getElementById('closerAadhaarCard').value.trim();
        
        if (!customerName || !nomineeName || !customerDob || !panCard || !aadhaarCard) {
            alert('Please fill all required KYC fields');
            return;
        }

        formData.append('customer_name', customerName);
        formData.append('nominee_name', nomineeName);
        if (secondCustomerName) {
            formData.append('second_customer_name', secondCustomerName);
        }
        formData.append('customer_dob', customerDob);
        formData.append('pan_card', panCard);
        formData.append('aadhaar_card_no', aadhaarCard);

        // KYC Documents
        const kycDocumentsInput = document.getElementById('closerKycDocumentsInput');
        if (!kycDocumentsInput.files || kycDocumentsInput.files.length === 0) {
            alert('Please upload at least one KYC document');
            return;
        }
        for (let i = 0; i < kycDocumentsInput.files.length; i++) {
            formData.append('kyc_documents[]', kycDocumentsInput.files[i]);
        }

        // Proof Photos
        const photosInput = document.getElementById('closerProofPhotosInput');
        if (!photosInput.files || photosInput.files.length === 0) {
            alert('Please upload at least one proof photo');
            return;
        }
        for (let i = 0; i < photosInput.files.length; i++) {
            formData.append('proof_photos[]', photosInput.files[i]);
        }
        
        // Incentive Amount
        const incentiveAmount = document.getElementById('closerIncentiveAmount').value;
        if (!incentiveAmount || parseFloat(incentiveAmount) <= 0) {
            alert('Please enter a valid incentive amount');
            return;
        }
        formData.append('incentive_amount', incentiveAmount);

        try {
            const token = getToken();
            const response = await fetch(`${API_BASE_URL}/site-visits/${currentSiteVisitId}/request-closer`, {
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

            if (result && result.success) {
                showSiteVisitSuccessModal('Closing request submitted with KYC details! Awaiting CRM verification.');
                closeRequestCloserModal();
                // Reset form
                document.getElementById('closingRequestForm').reset();
                document.getElementById('closerProofPhotosPreview').innerHTML = '';
                document.getElementById('closerKycDocumentsPreview').innerHTML = '';
                loadSiteVisits();
            } else {
                alert(result.message || 'Failed to request closer');
                if (result.errors) {
                    console.error('Validation errors:', result.errors);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Network error. Please try again.');
        }
    }

    function showMarkDeadModal(type, id) {
        currentSiteVisitId = id;
        document.getElementById('deadReason').value = '';
        document.getElementById('markDeadModal').classList.add('show');
    }

    function closeMarkDeadModal() {
        document.getElementById('markDeadModal').classList.remove('show');
        document.getElementById('deadReason').value = '';
        currentSiteVisitId = null;
    }

    async function submitMarkDead() {
        if (!currentSiteVisitId) return;

        const reason = document.getElementById('deadReason').value.trim();
        if (!reason) {
            alert('Please provide a reason for marking as dead');
            return;
        }

        const result = await apiCall(`/site-visits/${currentSiteVisitId}/mark-dead`, {
            method: 'POST',
            body: JSON.stringify({ reason }),
        });

        if (result && result.success) {
            alert('Site visit marked as dead successfully');
            closeMarkDeadModal();
            loadSiteVisits();
        } else {
            alert(result.message || 'Failed to mark as dead');
        }
    }

    // Reschedule Site Visit
    function showRescheduleSiteVisitModal(id) {
        currentSiteVisitId = id;
        // Use existing site visit data from the list or fetch if needed
        const minDateTime = new Date();
        minDateTime.setDate(minDateTime.getDate() + 1);
        minDateTime.setHours(0, 0, 0, 0);

        document.getElementById('rescheduleSiteVisitScheduledAt').value = '';
        document.getElementById('rescheduleSiteVisitReason').value = '';
        document.getElementById('rescheduleSiteVisitModalTitle').textContent = 'Reschedule Site Visit';
        document.getElementById('rescheduleSiteVisitModalType').value = 'site-visit';
        document.getElementById('rescheduleSiteVisitModalId').value = id;
        document.getElementById('rescheduleSiteVisitScheduledAt').min = minDateTime.toISOString().slice(0, 16);
        document.getElementById('rescheduleSiteVisitModal').classList.add('show');
    }

    function closeRescheduleSiteVisitModal() {
        document.getElementById('rescheduleSiteVisitModal').classList.remove('show');
        document.getElementById('rescheduleSiteVisitScheduledAt').value = '';
        document.getElementById('rescheduleSiteVisitReason').value = '';
        currentSiteVisitId = null;
    }

    async function submitRescheduleSiteVisit() {
        const type = document.getElementById('rescheduleSiteVisitModalType').value;
        const id = document.getElementById('rescheduleSiteVisitModalId').value;
        const scheduledAt = document.getElementById('rescheduleSiteVisitScheduledAt').value;
        const reason = document.getElementById('rescheduleSiteVisitReason').value.trim();

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
                if (typeof showNotification === 'function') {
                    showNotification(result.message || 'Rescheduled successfully! Verification required.', 'success', 3000);
                } else {
                    alert(result.message || 'Rescheduled successfully! Verification required.');
                }
                closeRescheduleSiteVisitModal();
                loadSiteVisits();
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
        loadSiteVisits();
    })();
</script>

<!-- Reschedule Site Visit Modal -->
<div id="rescheduleSiteVisitModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <h3 id="rescheduleSiteVisitModalTitle" style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Reschedule Site Visit</h3>
        <input type="hidden" id="rescheduleSiteVisitModalType" value="site-visit">
        <input type="hidden" id="rescheduleSiteVisitModalId" value="">
        
        <div class="form-group">
            <label>New Scheduled Date & Time <span style="color: #ef4444;">*</span></label>
            <input type="datetime-local" id="rescheduleSiteVisitScheduledAt" required
                style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            <small style="color: #6b7280;">Select a future date and time</small>
        </div>

        <div class="form-group">
            <label>Reason for Rescheduling <span style="color: #ef4444;">*</span></label>
            <textarea id="rescheduleSiteVisitReason" rows="4" placeholder="Enter reason for rescheduling..." required
                style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"></textarea>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeRescheduleSiteVisitModal()">Cancel</button>
            <button type="button" class="btn" style="background: #f59e0b; color: white;" onclick="submitRescheduleSiteVisit()">Reschedule</button>
        </div>
    </div>
</div>

<!-- Complete Site Visit Modal -->
<div id="completeSiteVisitModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Complete Site Visit</h3>
        <p style="color: #ef4444; margin-bottom: 16px;"><strong>Proof photos are required to complete the site visit.</strong></p>
        
        <div class="form-group">
            <label>Proof Photos <span style="color: #ef4444;">*</span></label>
            <input type="file" id="siteVisitProofPhotosInput" multiple accept="image/*" onchange="handleSiteVisitProofPhotosChange(event)" required>
            <div id="siteVisitProofPhotosPreview" style="display: flex; flex-wrap: wrap; margin-top: 10px;"></div>
            <small style="color: #6b7280;">Upload at least one photo as proof. Max 5MB per image.</small>
        </div>

        <div class="form-group">
            <label>Visited Project</label>
            <div id="completeProjectTagsContainer" class="complete-project-tags-container" style="display: flex; flex-wrap: wrap; gap: 8px; padding: 8px; border: 2px solid #e0e0e0; border-radius: 8px; min-height: 42px; background: white; margin-bottom: 8px;">
                <!-- Tags will be dynamically added here -->
            </div>
            <input type="text" id="completeProjectInput" placeholder="Type project name and press Enter" 
                style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; margin-bottom: 4px;">
            <input type="hidden" id="completeProjectHidden" name="visited_projects">
            <small style="color: #6b7280;">Type project name and press Enter to add. Click × to remove.</small>
        </div>

        <div class="form-group">
            <label>Tentative Closing Time</label>
            <select id="completeTentativeClosingTime" name="tentative_closing_time" 
                style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                <option value="">Select an option</option>
                <option value="within_3_days">Within 3 Days</option>
                <option value="tomorrow">Tomorrow</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="it_will_take_time">It Will Take Time</option>
            </select>
        </div>

        <div class="form-group">
            <label>Feedback</label>
            <textarea id="siteVisitFeedback" rows="3" placeholder="Site visit feedback..." 
                style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"></textarea>
        </div>

        <div class="form-group">
            <label>Rating</label>
            <select id="siteVisitRating" 
                style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
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
            <textarea id="siteVisitNotes" rows="3" placeholder="Additional notes..." 
                style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"></textarea>
        </div>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <button type="button" class="btn btn-secondary" onclick="closeCompleteSiteVisitModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitCompleteSiteVisit()">Submit</button>
        </div>
    </div>
</div>

<!-- Request Closer Modal -->
<div id="requestCloserModal" class="modal">
    <div class="modal-content" style="max-width: 700px; max-height: 90vh; overflow-y: auto;">
        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Request for Closing with KYC Details</h3>
        <p style="color: #ef4444; margin-bottom: 16px;"><strong>All fields are required. Please fill complete KYC details.</strong></p>
        
        <form id="closingRequestForm">
            <!-- Customer Information -->
            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #e0e0e0;">
                <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #063A1C;">Customer Information</h4>
                
                <div class="form-group">
                    <label>Customer Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="closerCustomerName" placeholder="Enter customer name" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                </div>

                <div class="form-group">
                    <label>Nominee Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="closerNomineeName" placeholder="Enter nominee name" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                </div>

                <div class="form-group">
                    <label>Second Customer Name (if available)</label>
                    <input type="text" id="closerSecondCustomerName" placeholder="Enter second customer name (optional)"
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                </div>

                <div class="form-group">
                    <label>Date of Birth <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="closerCustomerDob" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                </div>

                <div class="form-group">
                    <label>PAN Card Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="closerPanCard" placeholder="Enter PAN card number" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; text-transform: uppercase;"
                        maxlength="10" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}">
                    <small style="color: #6b7280;">Format: ABCDE1234F</small>
                </div>

                <div class="form-group">
                    <label>Aadhaar Card Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="closerAadhaarCard" placeholder="Enter Aadhaar card number" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"
                        maxlength="12" pattern="[0-9]{12}">
                    <small style="color: #6b7280;">12-digit Aadhaar number</small>
                </div>
            </div>

            <!-- KYC Documents -->
            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #e0e0e0;">
                <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #063A1C;">KYC Documents</h4>
                
                <div class="form-group">
                    <label>KYC Documents <span style="color: #ef4444;">*</span></label>
                    <input type="file" id="closerKycDocumentsInput" multiple accept="image/*,.pdf" onchange="handleCloserKycDocumentsChange(event)" required>
                    <div id="closerKycDocumentsPreview" style="display: flex; flex-wrap: wrap; margin-top: 10px;"></div>
                    <small style="color: #6b7280;">Upload KYC documents (images or PDF). Max 5MB per file.</small>
                </div>
            </div>

            <!-- Proof Photos and Incentive -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: #063A1C;">Proof & Incentive</h4>
                
                <div class="form-group">
                    <label>Proof Photos <span style="color: #ef4444;">*</span></label>
                    <input type="file" id="closerProofPhotosInput" multiple accept="image/*" onchange="handleCloserProofPhotosChange(event)" required>
                    <div id="closerProofPhotosPreview" style="display: flex; flex-wrap: wrap; margin-top: 10px;"></div>
                    <small style="color: #6b7280;">Upload at least one photo as proof. Max 5MB per image.</small>
                </div>

                <div class="form-group">
                    <label>Incentive Amount <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="closerIncentiveAmount" step="0.01" min="0" placeholder="Enter incentive amount" required
                        style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                    <small style="color: #6b7280;">Enter the incentive amount for this closer.</small>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeRequestCloserModal()">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitRequestCloser()">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="siteVisitSuccessModal" class="modal">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 40px 30px;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
            <i class="fas fa-check" style="font-size: 40px; color: white; font-weight: bold;"></i>
        </div>
        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin-bottom: 12px;">Success!</h3>
        <p id="siteVisitSuccessMessage" style="font-size: 16px; color: #666; margin-bottom: 30px; line-height: 1.5;"></p>
        <button onclick="closeSiteVisitSuccessModal()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 12px 32px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)';">
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
.btn-secondary {
    background: #6b7280;
    color: white;
}
.btn-secondary:hover {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    background: #4b5563;
}

/* Project Tags Styling */
.complete-project-tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    min-height: 42px;
    background: white;
    margin-bottom: 8px;
}

.complete-project-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.complete-project-tag-text {
    user-select: none;
}

.complete-project-tag-remove {
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
    line-height: 1;
    margin-left: 4px;
    opacity: 0.9;
    transition: opacity 0.2s;
}

.complete-project-tag-remove:hover {
    opacity: 1;
}
</style>
@endpush
