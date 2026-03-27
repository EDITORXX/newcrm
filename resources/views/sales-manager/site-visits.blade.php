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
    #visitsContainer.list-view {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .visit-card {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        padding: 16px;
        border-radius: 20px;
        box-shadow: 0 18px 45px rgba(23, 97, 168, 0.08);
        border: 1px solid rgba(23, 97, 168, 0.10);
        border-left: 4px solid #1761A8;
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
        margin-bottom: 10px;
    }
    .visit-topline {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .visit-actions {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .visit-actions .btn {
        width: 100%;
        margin-left: 0;
        margin-top: 0;
    }
    .card-action-menu {
        position: relative;
        width: 100%;
    }
    .card-action-trigger {
        width: 100%;
    }
    .card-action-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        z-index: 20;
        display: none;
        flex-direction: column;
        gap: 8px;
        padding: 10px;
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid #d7e7f7;
        box-shadow: 0 20px 40px rgba(23, 97, 168, 0.14);
        min-width: 100%;
    }
    .card-action-dropdown.show {
        display: flex;
    }
    .card-action-item {
        width: 100%;
        border: none;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        cursor: pointer;
    }
    .card-action-item.btn-success,
    .card-action-item.btn-danger,
    .card-action-item.btn-primary,
    .card-action-item.btn-warning {
        width: 100%;
        margin: 0;
    }
    .visit-info h3 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f3d67;
        letter-spacing: -0.02em;
        margin-bottom: 4px;
    }
    .visit-subtitle {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    .visit-remark {
        color: #475569;
        font-size: 0.88rem;
        line-height: 1.45;
        background: #f7fbfe;
        border: 1px solid #deebf8;
        border-radius: 12px;
        padding: 9px 11px;
        min-height: 0;
        margin-bottom: 10px;
    }
    .visit-remark strong {
        display: block;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 6px;
    }
    .visit-secondary {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        color: #64748b;
        font-size: 0.82rem;
        margin-bottom: 10px;
    }
    .visit-secondary span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .visit-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 0;
        margin-bottom: 4px;
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
    .badge-awaiting {
        background: #fff7ed;
        color: #c2410c;
    }
    .badge-closing-pending {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .badge-closing-rejected {
        background: #fee2e2;
        color: #b91c1c;
    }
    .btn {
        padding: 10px 14px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        font-size: 0.88rem;
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
    .view-toggle-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fff;
        padding: 4px;
        border-radius: 12px;
        box-shadow: 0 8px 18px rgba(16, 24, 20, 0.07);
        border: 1px solid #e4e0d7;
    }
    .asm-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 12px;
        flex-wrap: wrap;
    }
    .asm-page-title {
        font-size: 1.85rem;
        font-weight: 800;
        color: #063A1C;
        letter-spacing: -0.02em;
        margin: 0;
    }
    .asm-page-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .view-toggle-btn {
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 0.78rem;
        font-weight: 700;
        border-radius: 9px;
        padding: 7px 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .view-toggle-btn.active {
        background: linear-gradient(135deg, #1761A8 0%, #124a82 100%);
        color: #fff;
        box-shadow: 0 10px 18px rgba(23, 97, 168, 0.18);
    }
    #visitsContainer.list-view .visit-card {
        flex-direction: row;
        align-items: flex-start;
        gap: 14px;
        padding: 14px 16px;
    }
    #visitsContainer.list-view .visit-header {
        flex: 1 1 auto;
        margin-bottom: 0;
    }
    #visitsContainer.list-view .visit-actions {
        flex: 0 0 170px;
        margin-top: 0;
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
        .asm-page-header {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
            margin-bottom: 14px;
        }
        .asm-page-title {
            text-align: center;
            font-size: 1.75rem;
        }
        .asm-page-toolbar {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        #visitsContainer {
            grid-template-columns: 1fr;
            gap: 1rem;
            max-width: 420px;
            margin: 0 auto;
        }
        .visit-card {
            padding: 14px;
            border-radius: 16px;
        }
        .visit-info h3 {
            font-size: 1.1rem;
        }
        .visit-subtitle,
        .visit-secondary,
        .visit-remark {
            font-size: 0.84rem;
        }
        .visit-actions {
            gap: 8px;
        }
        .card-action-dropdown {
            position: static;
            margin-top: 8px;
        }
        .view-toggle-group {
            width: auto;
            justify-content: center;
            margin: 0 auto;
        }
        .view-toggle-btn {
            justify-content: center;
        }
        #visitsContainer.list-view .visit-card {
            flex-direction: column;
            align-items: stretch;
        }
        #visitsContainer.list-view .visit-actions {
            flex: 1 1 auto;
        }
        .visit-remark {
            margin-bottom: 8px;
        }
        .filters {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 10px;
            border: 1px solid #dbe6f3;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #f6faff 100%);
            box-shadow: 0 8px 24px rgba(30, 64, 175, 0.08);
        }
        .filters .filter-select,
        .filters .filter-btn {
            width: calc(50% - 5px);
            flex: 1 1 calc(50% - 5px);
            padding: 10px 12px;
            font-size: 13px;
            box-sizing: border-box;
        }
        .filters .filter-btn.btn.btn-primary {
            display: flex !important;
            width: 100%;
            flex: 1 1 100%;
            min-height: 44px;
        }
        .filters .filter-closer {
            width: 100%;
            flex: 1 1 100%;
        }
        .mobile-text {
            display: inline;
        }
        .desktop-text {
            display: inline;
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
    <div class="asm-page-header">
        <h2 class="asm-page-title">Site Visits</h2>
    </div>

    <div class="filters">
        <select id="statusFilter" class="filter-select" onchange="loadSiteVisits()">
            <option value="">All Status</option>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select id="verificationFilter" class="filter-select" onchange="loadSiteVisits()">
            <option value="">All Verification</option>
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
    const ASM_SECTION_VIEW_PREFERENCES = @json($sectionViewPreferences ?? []);
    const ASM_SECTION_VIEW_SAVE_URL = @json(route('sales-manager.settings.update'));

    function getAsmPreferredView(sectionKey, fallbackView) {
        const preferred = ASM_SECTION_VIEW_PREFERENCES?.[sectionKey];
        return preferred === 'list' || preferred === 'card' ? preferred : fallbackView;
    }

    async function persistAsmSectionViewPreference(sectionKey, view) {
        try {
            ASM_SECTION_VIEW_PREFERENCES[sectionKey] = view;
            await fetch(ASM_SECTION_VIEW_SAVE_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    section_view_preferences: {
                        [sectionKey]: view
                    }
                })
            });
        } catch (error) {
            console.error('Failed to persist section view preference:', error);
        }
    }
    
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

    function getVisitRemark(visit) {
        return visit.visit_notes || visit.notes || visit.feedback || visit.property_address || visit.project || 'No remark added yet.';
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function truncateText(value, maxLength) {
        const text = String(value || '');
        return text.length > maxLength ? text.slice(0, maxLength).trim() + '...' : text;
    }

    function setVisitsView(view, shouldPersist = true) {
        const container = document.getElementById('visitsContainer');
        if (!container) return;
        container.classList.toggle('list-view', view === 'list');
        document.querySelectorAll('.view-toggle-btn[data-view]').forEach((btn) => {
            btn.classList.toggle('active', btn.getAttribute('data-view') === view);
        });
        try {
            localStorage.setItem('asm_visits_view', view);
        } catch (e) {}
        if (shouldPersist) {
            persistAsmSectionViewPreference('site_visits', view);
        }
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
                const remark = truncateText(getVisitRemark(visit), 120);
                const isScheduled = visit.status === 'scheduled';
                const isCompleted = visit.status === 'completed';
                const isPendingVerification = isCompleted && visit.verification_status === 'pending';
                const isVerified = visit.verification_status === 'verified';
                const hasCloserState = !!visit.closer_status;
                const hasClosingState = !!visit.closing_verification_status;

                const visitActionItems = [];

                if (isScheduled) {
                    visitActionItems.push(`
                        <button class="card-action-item btn btn-success" onclick="closeAllSiteVisitActionMenus(); showCompleteSiteVisitModal(${visit.id});">
                            <i class="fas fa-check"></i>Complete
                        </button>
                    `);
                    visitActionItems.push(`
                        <button class="card-action-item btn btn-danger" onclick="closeAllSiteVisitActionMenus(); showMarkDeadModal('site-visit', ${visit.id});">
                            <i class="fas fa-skull"></i>Mark as Dead
                        </button>
                    `);
                    visitActionItems.push(`
                        <button class="card-action-item btn btn-warning" onclick="closeAllSiteVisitActionMenus(); showRescheduleSiteVisitModal(${visit.id});">
                            <i class="fas fa-calendar-plus"></i>Reschedule
                        </button>
                    `);
                }

                if (isVerified && !hasCloserState && !hasClosingState) {
                    visitActionItems.push(`
                        <button class="card-action-item btn btn-primary" onclick="closeAllSiteVisitActionMenus(); requestCloseFromVisit(${visit.id});">
                            <i class="fas fa-circle-check"></i>Mark as Closed
                        </button>
                    `);
                }

                if (isCompleted) {
                    visitActionItems.push(`
                        <button class="card-action-item btn btn-danger" onclick="closeAllSiteVisitActionMenus(); showMarkDeadModal('site-visit', ${visit.id});">
                            <i class="fas fa-skull"></i>Mark as Dead
                        </button>
                    `);
                }

                if (visit.lead_id) {
                    visitActionItems.push(`
                        <a href="/leads/${visit.lead_id}" target="_blank" class="card-action-item btn btn-primary" onclick="closeAllSiteVisitActionMenus()">
                            <i class="fas fa-eye"></i>View Detail
                        </a>
                    `);
                }

                return `
                    <div class="visit-card ${statusClass}">
                        <div class="visit-header">
                            <div class="visit-info">
                                <div class="visit-topline">
                                    <div>
                                        <h3>${customerName}</h3>
                                        <div class="visit-subtitle">${phone}</div>
                                    </div>
                                </div>
                                <div class="visit-remark">
                                    <strong>Remark</strong>
                                    ${escapeHtml(remark)}
                                </div>
                                <div class="visit-secondary">
                                    <span><i class="fas fa-calendar"></i>${new Date(visit.scheduled_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                                    ${project !== 'N/A' ? `<span><i class="fas fa-map-marker-alt"></i>${project}</span>` : ''}
                                    ${propertyType !== 'N/A' ? `<span><i class="fas fa-building"></i>${propertyType}</span>` : ''}
                                </div>
                                <div class="visit-status-row">
                                    <span class="badge ${statusBadge}">${visit.status}</span>
                                    ${visit.verification_status ? `<span class="badge ${verificationBadge}">${visit.verification_status}</span>` : ''}
                                    ${visit.closer_status ? `<span class="badge ${closerBadge}">Closer: ${visit.closer_status}</span>` : ''}
                                    ${isPendingVerification ? `<span class="badge badge-awaiting">Awaiting Verification</span>` : ''}
                                    ${visit.closing_verification_status === 'pending' ? `<span class="badge badge-closing-pending">Closing Awaiting</span>` : ''}
                                    ${visit.closing_verification_status === 'verified' ? `<span class="badge badge-closer-verified">Closing Verified</span>` : ''}
                                    ${visit.closing_verification_status === 'rejected' ? `<span class="badge badge-closing-rejected">Closing Rejected</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="visit-actions">
                            ${visitActionItems.length ? `
                                <div class="card-action-menu" data-site-visit-action-menu="${visit.id}">
                                    <button class="btn btn-primary card-action-trigger" onclick="toggleSiteVisitActionMenu(${visit.id}, event)">
                                        <i class="fas fa-bolt"></i>Action
                                    </button>
                                    <div class="card-action-dropdown" id="siteVisitActionDropdown${visit.id}">
                                        ${visitActionItems.join('')}
                                    </div>
                                </div>
                            ` : ''}
                            
                            ${visit.closing_verification_status === 'verified' && visit.closer_status === 'verified' ? `
                                <div style="text-align: center; padding: 8px; margin-bottom: 8px;">
                                    <span class="badge badge-closer-verified">Closing Verified ✓</span>
                                </div>
                                
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

    function closeAllSiteVisitActionMenus() {
        document.querySelectorAll('.card-action-dropdown.show').forEach(menu => {
            menu.classList.remove('show');
        });
    }

    function toggleSiteVisitActionMenu(id, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const targetMenu = document.getElementById(`siteVisitActionDropdown${id}`);
        if (!targetMenu) return;

        const shouldOpen = !targetMenu.classList.contains('show');
        closeAllSiteVisitActionMenus();

        if (shouldOpen) {
            targetMenu.classList.add('show');
        }
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('[data-site-visit-action-menu]')) {
            closeAllSiteVisitActionMenus();
        }
    });

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

    async function requestCloseFromVisit(id) {
        try {
            const token = getToken();
            const response = await fetch(`${API_BASE_URL}/site-visits/${id}/request-close`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (response.ok && result.success) {
                showSiteVisitSuccessModal(result.message || 'Close request submitted successfully.');
                loadSiteVisits();
                return;
            }

            alert(result.message || 'Failed to mark as closed');
        } catch (error) {
            console.error('Error requesting close:', error);
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
        const pageParams = new URLSearchParams(window.location.search);
        const requestedDateFilter = pageParams.get('date_filter');
        const dateFilterEl = document.getElementById('dateFilter');
        const dateFromEl = document.getElementById('dateFrom');
        const dateToEl = document.getElementById('dateTo');
        const savedView = (() => {
            try { return localStorage.getItem('asm_visits_view') || 'card'; } catch (e) { return 'card'; }
        })();
        setVisitsView(getAsmPreferredView('site_visits', savedView), false);

        if (dateFilterEl && requestedDateFilter && ['today', 'this_week', 'this_month', 'this_year', 'custom'].includes(requestedDateFilter)) {
            dateFilterEl.value = requestedDateFilter;
            if (requestedDateFilter === 'custom') {
                if (dateFromEl && pageParams.get('date_from')) dateFromEl.value = pageParams.get('date_from');
                if (dateToEl && pageParams.get('date_to')) dateToEl.value = pageParams.get('date_to');
            }
            toggleCustomDate();
        }

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
    <div class="modal-content complete-site-visit-modal-card" style="max-width: 760px; padding: 0; overflow: hidden;">
        <div class="complete-site-visit-modal-head">
            <div>
                <h3>Complete Site Visit</h3>
                <p>Upload proof, capture projects, and record visit outcome before submitting.</p>
            </div>
            <span class="complete-site-visit-badge">Required</span>
        </div>
        <div class="complete-site-visit-modal-body">
            <div class="complete-site-visit-alert">
                <i class="fas fa-camera"></i>
                <div>
                    <strong>Proof photos are mandatory.</strong>
                    <span>Add at least one clear site visit photo. Max 5MB per image.</span>
                </div>
            </div>
        
        <div class="complete-site-visit-upload">
            <label for="siteVisitProofPhotosInput">Proof Photos <span class="req">*</span></label>
            <input type="file" id="siteVisitProofPhotosInput" multiple accept="image/*" onchange="handleSiteVisitProofPhotosChange(event)" required>
            <div id="siteVisitProofPhotosPreview" class="complete-site-visit-preview"></div>
        </div>

        <div class="complete-site-visit-grid">
            <div class="form-group complete-site-visit-field complete-site-visit-field-wide">
                <label for="completeProjectInput">Visited Project</label>
                <div id="completeProjectTagsContainer" class="complete-site-visit-tags"></div>
                <input type="text" id="completeProjectInput" placeholder="Type project name and press Enter">
                <input type="hidden" id="completeProjectHidden" name="visited_projects">
            <small style="color: #6b7280;">Type project name and press Enter to add. Click × to remove.</small>
        </div>

        <div class="form-group complete-site-visit-field">
            <label for="completeTentativeClosingTime">Tentative Closing Time</label>
            <select id="completeTentativeClosingTime" name="tentative_closing_time">
                <option value="">Select an option</option>
                <option value="within_3_days">Within 3 Days</option>
                <option value="tomorrow">Tomorrow</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="it_will_take_time">It Will Take Time</option>
            </select>
        </div>

        <div class="form-group complete-site-visit-field complete-site-visit-field-wide">
            <label for="siteVisitFeedback">Feedback</label>
            <textarea id="siteVisitFeedback" rows="4" placeholder="Summarize site visit response, interest level, and objections..."></textarea>
        </div>

        <div class="form-group complete-site-visit-field">
            <label for="siteVisitRating">Rating</label>
            <select id="siteVisitRating">
                <option value="">Select rating</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>
        </div>

        <div class="form-group complete-site-visit-field complete-site-visit-field-wide">
            <label for="siteVisitNotes">Notes</label>
            <textarea id="siteVisitNotes" rows="4" placeholder="Add internal notes, next steps, or commercial details..."></textarea>
        </div>

        </div>
        <div class="complete-site-visit-actions">
            <button type="button" class="btn btn-secondary" onclick="closeCompleteSiteVisitModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="submitCompleteSiteVisit()">Submit</button>
        </div>
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
    overflow-y: auto;
    padding: 18px 0;
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
.complete-site-visit-modal-card {
    display: flex;
    flex-direction: column;
    width: min(760px, 92vw);
    max-height: min(88vh, 920px);
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    border: 1px solid #dce9e2;
    box-shadow: 0 24px 48px rgba(23, 97, 168, 0.16);
}
.complete-site-visit-modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 24px 28px 18px;
    background: linear-gradient(135deg, #f6fbff 0%, #edf5ff 100%);
    border-bottom: 1px solid #d7e5f6;
}
.complete-site-visit-modal-head h3 {
    margin: 0;
    font-size: 28px;
    line-height: 1.1;
    color: #0f3d67;
}
.complete-site-visit-modal-head p {
    margin: 8px 0 0;
    color: #62778b;
    font-size: 14px;
}
.complete-site-visit-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    border-radius: 999px;
    background: #e7f0ff;
    color: #1761A8;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.complete-site-visit-modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 24px 28px 12px;
}
.complete-site-visit-alert {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px 16px;
    margin-bottom: 20px;
    border: 1px solid #ffd4d4;
    border-radius: 16px;
    background: linear-gradient(135deg, #fff5f5 0%, #fffafa 100%);
}
.complete-site-visit-alert i {
    font-size: 20px;
    color: #dc2626;
    margin-top: 2px;
}
.complete-site-visit-alert strong,
.complete-site-visit-alert span {
    display: block;
}
.complete-site-visit-alert strong {
    color: #b42318;
    margin-bottom: 3px;
}
.complete-site-visit-alert span {
    color: #7a5c5c;
    font-size: 13px;
}
.complete-site-visit-upload {
    padding: 18px;
    margin-bottom: 20px;
    border: 1px solid #dce9e2;
    border-radius: 18px;
    background: #f9fcff;
}
.complete-site-visit-upload label,
.complete-site-visit-field label {
    display: block;
    margin-bottom: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #123f68;
}
.complete-site-visit-upload input[type="file"] {
    width: 100%;
    padding: 12px;
    border: 1px dashed #8eb5dc;
    border-radius: 14px;
    background: #fff;
}
.complete-site-visit-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
}
.complete-site-visit-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(220px, 0.52fr);
    gap: 18px;
}
.complete-site-visit-field {
    margin-bottom: 0;
}
.complete-site-visit-field-wide {
    grid-column: 1 / -1;
}
.complete-site-visit-field input[type="text"],
.complete-site-visit-field textarea,
.complete-site-visit-field select {
    width: 100%;
    border: 1px solid #d4dfeb;
    border-radius: 14px;
    padding: 14px 15px;
    color: #183c2c;
    background: #fff;
}
.complete-site-visit-field textarea {
    min-height: 120px;
    resize: vertical;
}
.complete-site-visit-field small {
    display: block;
    margin-top: 8px;
    color: #6b7280;
}
.complete-site-visit-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px;
    min-height: 52px;
    margin-bottom: 8px;
    border: 1px solid #d4dfeb;
    border-radius: 14px;
    background: #fff;
}
.complete-site-visit-upload input[type="file"]:focus,
.complete-site-visit-field input[type="text"]:focus,
.complete-site-visit-field textarea:focus,
.complete-site-visit-field select:focus {
    outline: none;
    border-color: #1761A8;
    box-shadow: 0 0 0 3px rgba(23, 97, 168, 0.12);
}
.complete-site-visit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 18px 28px 24px;
    border-top: 1px solid #e4edf6;
    background: #ffffff;
}
.complete-site-visit-actions .btn {
    min-width: 120px;
    min-height: 46px;
    border-radius: 12px;
    font-weight: 700;
}
@media (max-width: 768px) {
    .complete-site-visit-modal-card {
        width: min(760px, 95vw);
        max-height: 90vh;
    }
    .complete-site-visit-modal-head,
    .complete-site-visit-modal-body,
    .complete-site-visit-actions {
        padding-left: 18px;
        padding-right: 18px;
    }
    .complete-site-visit-modal-head {
        flex-direction: column;
        align-items: stretch;
    }
    .complete-site-visit-grid {
        grid-template-columns: 1fr;
    }
    .complete-site-visit-actions {
        flex-direction: column-reverse;
    }
    .complete-site-visit-actions .btn {
        width: 100%;
    }
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
