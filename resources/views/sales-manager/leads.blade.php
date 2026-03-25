@extends('sales-manager.layout')

@section('title', 'Leads - Senior Manager')
@section('page-title', 'Leads')

@push('styles')
<style>
    .lead-view-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem;
        background: #edf2f7;
        border: 1px solid #dbe4ee;
        border-radius: 9999px;
    }

    .lead-view-toggle button {
        border: 0;
        background: transparent;
        color: #5f6c7b;
        padding: 0.7rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .lead-view-toggle button.active {
        background: linear-gradient(135deg, #063A1C, #205A44);
        color: #fff;
        box-shadow: 0 8px 18px rgba(6, 58, 28, 0.18);
    }

    #leadsGrid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .leads-list-shell {
        border: 1px solid #e5e7eb;
        border-radius: 1.25rem;
        overflow: hidden;
        background: #ffffff;
    }

    .leads-list-table {
        width: 100%;
        border-collapse: collapse;
    }

    .leads-list-table thead th {
        background: #f8fafc;
        color: #475467;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0.95rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .leads-list-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #eef2f7;
        vertical-align: top;
    }

    .leads-list-table tbody tr:hover {
        background: #fcfdfd;
    }

    .lead-list-name {
        color: #101828;
        font-weight: 700;
        font-size: 0.96rem;
    }

    .lead-list-sub {
        color: #667085;
        font-size: 0.84rem;
        margin-top: 0.2rem;
    }

    .lead-remark-text {
        color: #344054;
        font-size: 0.85rem;
        line-height: 1.45;
        max-width: 320px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .lead-list-actions {
        display: flex;
        gap: 0.5rem;
        min-width: 190px;
    }

    .lead-list-actions a,
    .lead-list-actions button {
        flex: 1 1 0;
        border-radius: 0.85rem;
        padding: 0.72rem 0.85rem;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
    }
    
    /* Lead card container - ensure proper sizing */
    .bg-white.rounded-lg.shadow-md {
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: visible;
        box-sizing: border-box;
    }
    
    /* Professional Verified Badge Styling - Compact */
    .verified-badge {
        position: relative;
        z-index: 10;
        white-space: nowrap;
        letter-spacing: 0.01em;
        text-transform: uppercase;
        font-weight: 500;
        box-shadow: 0 1px 2px rgba(16, 185, 129, 0.15);
        transition: all 0.2s ease;
        line-height: 1.2;
    }
    
    .verified-badge:hover {
        transform: translateY(-0.5px);
        box-shadow: 0 2px 3px rgba(16, 185, 129, 0.2);
    }
    
    .verified-badge i {
        filter: drop-shadow(0 0.5px 0.5px rgba(0, 0, 0, 0.1));
    }
    
    /* Button container in cards */
    .bg-white.rounded-lg.shadow-md .lead-card-action-row {
        width: 100%;
        box-sizing: border-box;
        overflow: visible;
        margin-top: 1rem;
    }
    
    /* Buttons in lead cards */
    .bg-white.rounded-lg.shadow-md .lead-card-action-row a,
    .bg-white.rounded-lg.shadow-md .lead-card-action-row button {
        flex: 1 1 0;
        min-width: 0;
        max-width: calc(50% - 4px);
        padding: 10px 8px;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        box-sizing: border-box;
    }

    .favorite-lead-btn {
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #6b7280;
        flex: 0 0 auto !important;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .lead-list-actions .favorite-lead-btn {
        flex: 0 0 auto !important;
    }

    .favorite-lead-btn:hover {
        border-color: #f59e0b;
        color: #b45309;
    }

    .favorite-lead-btn.active {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #b45309;
    }

    @media (max-width: 1024px) {
        #leadsGrid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Filters layout - desktop: one line, mobile: stacked */
    .lead-filters-row {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: center;
        gap: 8px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    .lead-filters-row input,
    .lead-filters-row select {
        flex: 1;
        min-width: 0;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    @media (max-width: 768px) {
        #leadsGrid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .lead-view-toggle {
            width: 100%;
            justify-content: stretch;
        }

        .lead-view-toggle button {
            flex: 1 1 0;
        }

        .leads-list-shell {
            overflow-x: auto;
        }

        .leads-list-table {
            min-width: 880px;
        }
        
        /* Search and filter controls */
        .flex.items-center.justify-between {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .lead-filters-row {
            width: 100%;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 4px;
        }
        
        .lead-filters-row input,
        .lead-filters-row select {
            padding: 8px 6px;
            min-width: 0;
            font-size: 12px;
            box-sizing: border-box;
        }
        
        .lead-filters-row {
            overflow: hidden;
        }
        
        .lead-filters-row input {
            width: 25%;
            flex: 0 0 25%;
            max-width: 25%;
        }
        
        .lead-filters-row select:nth-of-type(1) {
            width: 25%;
            flex: 0 0 25%;
            max-width: 25%;
        }
        
        .lead-filters-row select:nth-of-type(2) {
            width: 25%;
            flex: 0 0 25%;
            max-width: 25%;
        }
        
        .lead-filters-row button {
            width: 25%;
            flex: 0 0 25%;
            max-width: 25%;
            padding: 8px 6px;
            font-size: 12px;
        }
        
        /* Modal responsive */
        #editLeadModal .max-w-2xl {
            max-width: 95% !important;
            width: 100% !important;
            margin: 10px;
            padding: 16px !important;
        }
        
        #editLeadModal h3 {
            font-size: 18px !important;
        }
        
        /* Lead cards responsive */
        .bg-white.rounded-lg.shadow {
            padding: 16px !important;
        }
        
        /* Ensure buttons don't overflow in cards */
        .bg-white.rounded-lg.shadow .lead-card-action-row {
            width: 100%;
            box-sizing: border-box;
            overflow: visible;
        }
        
        .bg-white.rounded-lg.shadow .lead-card-action-row a,
        .bg-white.rounded-lg.shadow .lead-card-action-row button {
            flex: 1 1 0;
            min-width: 0;
            max-width: 50%;
            padding: 8px 6px;
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Verified badge responsive - even smaller on mobile */
        .verified-badge {
            font-size: 9px;
            padding: 4px 8px;
        }
        
        .verified-badge i {
            font-size: 8px;
            margin-right: 3px;
        }
        
        /* Pagination responsive */
        #pagination {
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }
        
        /* Hide empty state on mobile */
        .empty-state-mobile {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex items-center justify-between mb-6" style="flex-wrap: wrap; gap: 12px;">
        <div class="lead-filters-row flex gap-2" style="flex-wrap: nowrap; align-items: center; width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
            <input 
                type="text" 
                id="searchInput"
                placeholder="Search leads..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                style="flex: 1; min-width: 0; max-width: 25%; box-sizing: border-box;"
                onkeyup="handleSearch()"
            >
            <select 
                id="statusFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                style="flex: 1; min-width: 0; max-width: 25%; box-sizing: border-box;"
                onchange="loadLeads()"
            >
                <option value="">All Status</option>
                <option value="new">New</option>
                <option value="contacted">Contacted</option>
                <option value="connected">Connected</option>
                <option value="verified_prospect">Verified Prospect</option>
                <option value="meeting_scheduled">Meeting Scheduled</option>
                <option value="meeting_completed">Meeting Completed</option>
                <option value="visit_scheduled">Visit Scheduled</option>
                <option value="visit_done">Visit Done</option>
                <option value="revisited_scheduled">Revisit Scheduled</option>
                <option value="revisited_completed">Revisit Completed</option>
                <option value="closed">Closed</option>
                <option value="dead">Dead</option>
                <option value="on_hold">On Hold</option>
            </select>
            <select 
                id="userFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                style="flex: 1; min-width: 0; max-width: 25%; box-sizing: border-box;"
                onchange="loadLeads()"
            >
                <option value="">All Users</option>
                <!-- Options will be populated dynamically -->
            </select>
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 bg-green-600 text-white font-medium hover:bg-green-700" style="flex: 1; min-width: 0; max-width: 25%; box-sizing: border-box; text-align: center; white-space: nowrap;" onclick="openAddLeadModal()">
                <i class="fas fa-plus mr-1"></i>Lead
            </button>
        </div>
        <div class="lead-view-toggle">
            <button type="button" id="leadCardsViewBtn" class="active" onclick="setLeadView('cards')">
                <i class="fas fa-th-large mr-2"></i>Cards
            </button>
            <button type="button" id="leadListViewBtn" onclick="setLeadView('list')">
                <i class="fas fa-list-ul mr-2"></i>List
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-gray-400 text-4xl mb-4"></i>
        <p class="text-gray-500">Loading leads...</p>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-12 empty-state-mobile" style="display: none;">
        <i class="fas fa-user-friends text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Leads Found</h3>
        <p class="text-gray-500">No leads match your current filters.</p>
    </div>

    <!-- Leads Cards -->
    <div id="leadsCards" style="display: none;">
        <div id="leadsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Leads will be loaded here -->
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="mt-6 flex items-center justify-between">
            <!-- Pagination will be loaded here -->
        </div>
    </div>

    <div id="leadsList" style="display: none;">
        <div class="leads-list-shell">
            <table class="leads-list-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Status</th>
                        <th>Remark</th>
                        <th>Assigned</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="leadsListBody">
                    <!-- Leads list rows -->
                </tbody>
            </table>
        </div>

        <div id="paginationList" class="mt-6 flex items-center justify-between">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

<!-- Edit Lead Modal -->
<div id="editLeadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Edit Lead</h3>
            <div id="editLeadModalContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="mt-6 flex gap-2">
                <button 
                    onclick="closeEditLeadModal()" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                >
                    Cancel
                </button>
                <button 
                    onclick="submitUpdateLead()" 
                    class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]"
                >
                    Update Lead
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Short Details Modal -->
<div id="shortDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Short Details</h3>
                <button onclick="closeShortDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="shortDetailsContent" class="text-gray-700">
                <!-- Lead details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Schedule Meeting Modal -->
<div id="scheduleMeetingModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Schedule Meeting</h3>
            <div id="scheduleMeetingModalContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="mt-6 flex gap-2">
                <button 
                    onclick="closeScheduleMeetingModal()" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                >
                    Cancel
                </button>
                <button 
                    onclick="submitCreateMeeting()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                >
                    Schedule Meeting
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Site Visit Modal -->
<div id="scheduleSiteVisitModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Schedule Site Visit</h3>
            <div id="scheduleSiteVisitModalContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="mt-6 flex gap-2">
                <button 
                    onclick="closeScheduleSiteVisitModal()" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                >
                    Cancel
                </button>
                <button 
                    onclick="submitCreateSiteVisit()" 
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                >
                    Schedule Site Visit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div id="addLeadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Add New Lead</h3>
            <form id="addLeadForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        id="addLeadName" 
                        required
                        placeholder="Enter lead name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                    <input 
                        type="tel" 
                        id="addLeadPhone" 
                        required
                        placeholder="Enter phone number"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                </div>
                <div id="addLeadError" class="text-red-500 text-sm" style="display: none;"></div>
            </form>
            <div class="mt-6 flex gap-2">
                <button 
                    onclick="closeAddLeadModal()" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                >
                    Cancel
                </button>
                <button 
                    onclick="submitAddLead()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                >
                    Add Lead
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api") }}';
    const SALES_MANAGER_API_URL = '{{ url("/api/sales-manager") }}';
    const API_TOKEN = '{{ $api_token }}';
    const LOGGED_IN_USER_NAME = '{{ auth()->user()->name }}';
    const LOGGED_IN_USER_ID = {{ auth()->user()->id }};
    const MANAGER_NAME = @if(auth()->user()->manager_id && auth()->user()->manager) '{{ auth()->user()->manager->name }}' @else '{{ auth()->user()->name }}' @endif;
    let searchTimeout = null;
    let allLeads = [];
    let currentLeadId = null;
    let teamMembers = [];
    let currentUser = null;
    let currentLeadView = 'cards';
    let currentLeadPage = 1;
    let favoriteLeadIds = new Set();

    // Get auth headers with Bearer token
    function getAuthHeaders() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${API_TOKEN}`,
        };
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setLeadView(view) {
        currentLeadView = view === 'list' ? 'list' : 'cards';
        document.getElementById('leadCardsViewBtn')?.classList.toggle('active', currentLeadView === 'cards');
        document.getElementById('leadListViewBtn')?.classList.toggle('active', currentLeadView === 'list');
        document.getElementById('leadsCards').style.display = currentLeadView === 'cards' && allLeads.length ? 'block' : 'none';
        document.getElementById('leadsList').style.display = currentLeadView === 'list' && allLeads.length ? 'block' : 'none';
    }

    function getLeadRemark(lead) {
        const formValues = lead.form_values || lead.formFields || {};
        const candidates = [
            lead.manager_remark,
            lead.remark,
            lead.notes,
            lead.requirements,
            formValues.manager_remark,
            formValues.remark,
        ];

        const remark = candidates.find(item => typeof item === 'string' && item.trim());
        return remark ? remark.trim() : 'No remark added';
    }

    function formatLeadStatus(status) {
        return String(status || 'new')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, (char) => char.toUpperCase());
    }

    function isFavoriteLead(lead) {
        return Boolean(lead && (lead.is_favorite || favoriteLeadIds.has(Number(lead.id))));
    }

    function createFavoriteLeadButton(leadId, isFavorite, extraClass = '') {
        const activeClass = isFavorite ? 'active' : '';
        const iconClass = isFavorite ? 'fas fa-star' : 'far fa-star';
        const title = isFavorite ? 'Remove from Favorites' : 'Add to Favorites';
        const className = ['favorite-lead-btn', activeClass, extraClass].filter(Boolean).join(' ');

        return `
            <button type="button"
                class="${className}"
                title="${title}"
                data-favorite-lead-id="${leadId}"
                data-favorite-state="${isFavorite ? '1' : '0'}"
                onclick="toggleLeadFavorite(${leadId})">
                <i class="${iconClass}"></i>
            </button>
        `;
    }

    function updateFavoriteButtonsState(leadId, isFavorite) {
        const buttons = document.querySelectorAll(`[data-favorite-lead-id="${leadId}"]`);
        buttons.forEach((button) => {
            button.dataset.favoriteState = isFavorite ? '1' : '0';
            button.classList.toggle('active', isFavorite);
            button.title = isFavorite ? 'Remove from Favorites' : 'Add to Favorites';
            const icon = button.querySelector('i');
            if (icon) {
                icon.className = isFavorite ? 'fas fa-star' : 'far fa-star';
            }
        });
    }

    async function refreshFavoriteLeadsFromApi() {
        try {
            const response = await fetch(`${SALES_MANAGER_API_URL}/favorite-leads`, {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const favorites = Array.isArray(data?.data) ? data.data : [];
            favoriteLeadIds = new Set(favorites.map(item => Number(item.lead_id)).filter(Number.isFinite));
        } catch (error) {
            console.error('Error refreshing favorite leads:', error);
        }
    }

    async function toggleLeadFavorite(leadId) {
        const numericLeadId = Number(leadId);
        const isFavorite = favoriteLeadIds.has(numericLeadId);
        const method = isFavorite ? 'DELETE' : 'POST';

        try {
            const response = await fetch(`${SALES_MANAGER_API_URL}/leads/${numericLeadId}/favorite`, {
                method,
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            });

            const data = await response.json();
            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Failed to update favorite lead');
            }

            if (isFavorite) {
                favoriteLeadIds.delete(numericLeadId);
            } else {
                favoriteLeadIds.add(numericLeadId);
            }

            allLeads = allLeads.map((lead) => {
                if (Number(lead.id) === numericLeadId) {
                    return { ...lead, is_favorite: !isFavorite };
                }
                return lead;
            });

            updateFavoriteButtonsState(numericLeadId, !isFavorite);
        } catch (error) {
            console.error('Error toggling favorite lead:', error);
            alert('Failed to update favorite lead. Please try again.');
        }
    }

    function createLeadListRow(lead) {
        const leadId = Number(lead.id);
        const favorite = isFavoriteLead(lead);
        const assignedTo = lead.active_assignments && lead.active_assignments.length > 0
            ? lead.active_assignments[0].assigned_to.name
            : 'Unassigned';
        const createdAt = new Date(lead.created_at).toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
        const remark = getLeadRemark(lead);

        return `
            <tr>
                <td>
                    <div class="lead-list-name">${escapeHtml(lead.name || 'N/A')}</div>
                    <div class="lead-list-sub"><i class="fas fa-phone mr-2 text-gray-400"></i>${escapeHtml(lead.phone || 'N/A')}</div>
                    ${lead.email ? `<div class="lead-list-sub"><i class="fas fa-envelope mr-2 text-gray-400"></i>${escapeHtml(lead.email)}</div>` : ''}
                </td>
                <td>
                    <div class="mb-2">${getStatusBadge(lead.status)}</div>
                    <div class="lead-list-sub">${escapeHtml(formatLeadStatus(lead.status))}</div>
                </td>
                <td>
                    <div class="lead-remark-text" title="${escapeHtml(remark)}">${escapeHtml(remark)}</div>
                </td>
                <td>
                    <div class="text-sm font-semibold text-gray-800">${escapeHtml(assignedTo)}</div>
                    <div class="lead-list-sub">${escapeHtml(lead.preferred_location || 'No location')}</div>
                </td>
                <td>
                    <div class="text-sm font-semibold text-gray-800">${escapeHtml(createdAt)}</div>
                    <div class="lead-list-sub">${escapeHtml(lead.budget || 'Budget not set')}</div>
                </td>
                <td>
                    <div class="lead-list-actions">
                        ${createFavoriteLeadButton(leadId, favorite)}
                        <a href="/leads/${lead.id}" class="flex items-center justify-center bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 shadow-md">
                            <i class="fas fa-eye mr-2"></i>View
                        </a>
                        <button type="button" onclick="viewShortDetails(${lead.id})" class="flex items-center justify-center bg-gradient-to-r from-blue-600 to-blue-700 text-white hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md">
                            <i class="fas fa-info-circle mr-2"></i>Short
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    // Load team members for filter
    async function loadTeamMembers() {
        try {
            console.log('Loading team members...');
            const response = await fetch(`${API_BASE_URL}/sales-manager/profile`, {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('Team members data:', data);
                currentUser = data.user;
                teamMembers = data.team_members || [];
                const profileFavorites = Array.isArray(data.favorite_leads) ? data.favorite_leads : [];
                favoriteLeadIds = new Set(
                    profileFavorites
                        .map(item => Number(item.lead_id))
                        .filter(Number.isFinite)
                );
                
                // Populate user filter dropdown
                const userFilter = document.getElementById('userFilter');
                if (userFilter) {
                    // Clear existing options
                    userFilter.innerHTML = '<option value="">All Users</option>';
                    
                    // Add current user (manager)
                    if (currentUser && currentUser.id) {
                        const option = document.createElement('option');
                        option.value = currentUser.id;
                        option.textContent = `${currentUser.name} (Me)`;
                        userFilter.appendChild(option);
                        console.log('Added current user:', currentUser.name);
                    }
                    
                    // Add team members
                    if (teamMembers && teamMembers.length > 0) {
                        teamMembers.forEach(member => {
                            if (member && member.id && member.name) {
                                const option = document.createElement('option');
                                option.value = member.id;
                                option.textContent = member.name;
                                userFilter.appendChild(option);
                                console.log('Added team member:', member.name);
                            }
                        });
                    } else {
                        console.warn('No team members found in response');
                    }
                } else {
                    console.error('User filter dropdown not found');
                }
            } else {
                console.error('Failed to load team members. Status:', response.status);
                const errorText = await response.text();
                console.error('Error response:', errorText);
            }
        } catch (error) {
            console.error('Error loading team members:', error);
        }
    }

    // Load leads
    async function loadLeads(page = 1) {
        currentLeadPage = page;
        const loadingState = document.getElementById('loadingState');
        const emptyState = document.getElementById('emptyState');
        const leadsCards = document.getElementById('leadsCards');
        const leadsGrid = document.getElementById('leadsGrid');
        const leadsList = document.getElementById('leadsList');
        const leadsListBody = document.getElementById('leadsListBody');
        
        loadingState.style.display = 'block';
        emptyState.style.display = 'none';
        leadsCards.style.display = 'none';
        leadsList.style.display = 'none';

        try {
            const status = document.getElementById('statusFilter').value;
            const search = document.getElementById('searchInput').value;
            const assignedTo = document.getElementById('userFilter')?.value || '';
            
            const params = new URLSearchParams({
                page: page,
                per_page: 15,
            });
            
            if (status) {
                params.append('status', status);
            }
            
            if (search) {
                params.append('search', search);
            }
            
            if (assignedTo) {
                params.append('assigned_to', assignedTo);
            }

            const response = await fetch(`${API_BASE_URL}/leads?${params}`, {
                headers: getAuthHeaders(),
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Failed to load leads');
            }

            const data = await response.json();

            if (data.data && data.data.length > 0) {
                allLeads = data.data.map((lead) => ({
                    ...lead,
                    is_favorite: favoriteLeadIds.has(Number(lead.id)),
                }));
                leadsGrid.innerHTML = '';
                leadsListBody.innerHTML = '';
                allLeads.forEach(lead => {
                    const card = createLeadCard(lead);
                    leadsGrid.appendChild(card);
                    leadsListBody.insertAdjacentHTML('beforeend', createLeadListRow(lead));
                });
                
                renderPagination(data);
                emptyState.style.display = 'none';
                setLeadView(currentLeadView);
            } else {
                allLeads = [];
                leadsCards.style.display = 'none';
                leadsList.style.display = 'none';
                emptyState.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading leads:', error);
            alert('Failed to load leads. Please try again.');
        } finally {
            loadingState.style.display = 'none';
        }
    }

    // Create lead card
    function createLeadCard(lead) {
        const card = document.createElement('div');
        card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200 p-5';
        
        const leadId = Number(lead.id);
        const favorite = isFavoriteLead(lead);
        const statusBadge = getStatusBadge(lead.status);
        const assignedTo = lead.active_assignments && lead.active_assignments.length > 0 
            ? lead.active_assignments[0].assigned_to.name 
            : 'Unassigned';
        const createdAt = new Date(lead.created_at).toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });

        card.innerHTML = `
            <div class="flex items-start justify-between mb-4" style="gap: 12px;">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1 truncate">${lead.name || 'N/A'}</h3>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    ${createFavoriteLeadButton(leadId, favorite)}
                    ${statusBadge}
                </div>
            </div>
            
            <div class="space-y-2">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-phone w-5 text-gray-400"></i>
                    <span>${lead.phone || 'N/A'}</span>
                </div>
                ${lead.email ? `
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-envelope w-5 text-gray-400"></i>
                    <span>${lead.email}</span>
                </div>
                ` : ''}
                ${lead.preferred_location ? `
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                    <span>${lead.preferred_location}</span>
                </div>
                ` : ''}
                ${lead.budget ? `
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-rupee-sign w-5 text-gray-400"></i>
                    <span>${typeof lead.budget === 'string' ? lead.budget : (lead.budget ? '₹' + parseFloat(lead.budget).toLocaleString('en-IN') : 'N/A')}</span>
                </div>
                ` : ''}
                <div class="flex items-center text-sm text-gray-500">
                    <i class="fas fa-calendar w-5 text-gray-400"></i>
                    <span>${createdAt}</span>
                </div>
            </div>

            <div class="lead-card-action-row flex gap-2 mt-4" style="width: 100%; box-sizing: border-box;">
                <a 
                    href="/leads/${lead.id}" 
                    class="flex-1 flex items-center justify-center px-2 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200 font-medium text-xs shadow-md min-w-0 overflow-hidden"
                    style="flex: 1 1 0; min-width: 0; max-width: 50%;"
                >
                    <i class="fas fa-eye mr-1.5 flex-shrink-0"></i>
                    <span class="truncate">View Detail</span>
                </a>
                <button 
                    onclick="viewShortDetails(${lead.id})" 
                    class="flex-1 flex items-center justify-center px-2 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 font-medium text-xs shadow-md min-w-0 overflow-hidden"
                    style="flex: 1 1 0; min-width: 0; max-width: 50%;"
                >
                    <i class="fas fa-info-circle mr-1.5 flex-shrink-0"></i>
                    <span class="truncate">Short Detail</span>
                </button>
            </div>
        </div>
        
        <!-- Expandable Details Section -->
        <div 
            id="details-${lead.id}" 
            class="hidden border-t border-gray-200 bg-gray-50 p-5"
            style="transition: all 0.3s ease;"
        >
            <div class="space-y-3 mb-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500">Name:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.name || 'N/A'}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Phone:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.phone || 'N/A'}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Email:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.email || 'N/A'}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Status:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.status || 'N/A'}</span>
                    </div>
                    ${lead.address ? `
                    <div class="col-span-2">
                        <span class="text-gray-500">Address:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.address}</span>
                    </div>
                    ` : ''}
                    ${lead.city || lead.state || lead.pincode ? `
                    <div>
                        <span class="text-gray-500">City/State:</span>
                        <span class="font-medium text-gray-900 ml-2">${[lead.city, lead.state, lead.pincode].filter(Boolean).join(', ') || 'N/A'}</span>
                    </div>
                    ` : ''}
                    <div>
                        <span class="text-gray-500">Source:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.source || 'N/A'}</span>
                    </div>
                    ${lead.preferred_location ? `
                    <div>
                        <span class="text-gray-500">Preferred Location:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.preferred_location}</span>
                    </div>
                    ` : ''}
                    ${lead.preferred_size ? `
                    <div>
                        <span class="text-gray-500">Preferred Size:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.preferred_size}</span>
                    </div>
                    ` : ''}
                    ${lead.property_type ? `
                    <div>
                        <span class="text-gray-500">Property Type:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.property_type}</span>
                    </div>
                    ` : ''}
                    ${lead.use_end_use ? `
                    <div>
                        <span class="text-gray-500">Use:</span>
                        <span class="font-medium text-gray-900 ml-2">${lead.use_end_use}</span>
                    </div>
                    ` : ''}
                </div>
                ${lead.notes ? `
                <div class="mt-3 p-3 bg-white rounded border border-gray-200">
                    <span class="text-xs font-medium text-gray-500 uppercase">Notes:</span>
                    <p class="text-sm text-gray-700 mt-1">${lead.notes}</p>
                </div>
                ` : ''}
                ${lead.requirements ? `
                <div class="mt-3 p-3 bg-white rounded border border-gray-200">
                    <span class="text-xs font-medium text-gray-500 uppercase">Requirements:</span>
                    <p class="text-sm text-gray-700 mt-1">${lead.requirements}</p>
                </div>
                ` : ''}
            </div>
            
            <div class="flex gap-2 mt-4">
                <button 
                    onclick="openEditLeadModal(${lead.id})" 
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors text-sm font-medium"
                >
                    <i class="fas fa-edit mr-2"></i>
                    Edit Lead
                </button>
                <button 
                    onclick="openScheduleMeetingModal(${lead.id})" 
                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"
                >
                    <i class="fas fa-handshake mr-2"></i>
                    Schedule Meeting
                </button>
                <button 
                    onclick="openScheduleSiteVisitModal(${lead.id})" 
                    class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium"
                >
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    Schedule Site Visit
                </button>
            </div>
        </div>
        `;
        
        return card;
    }

    // Get status badge
    function getStatusBadge(status) {
        const badges = {
            'new': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">New</span>',
            'contacted': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Contacted</span>',
            'connected': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Connected</span>',
            'verified_prospect': '<span class="verified-badge inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium rounded bg-gradient-to-r from-emerald-500 to-green-600 text-white shadow-sm border border-emerald-400/30"><i class="fas fa-check-circle mr-1 text-[9px]"></i>Verified</span>',
            'meeting_scheduled': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">Meeting Scheduled</span>',
            'meeting_completed': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-cyan-100 text-cyan-800">Meeting Completed</span>',
            'visit_scheduled': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-violet-100 text-violet-800">Visit Scheduled</span>',
            'visit_done': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-pink-100 text-pink-800">Visit Done</span>',
            'revisited_scheduled': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-fuchsia-100 text-fuchsia-800">Revisit Scheduled</span>',
            'revisited_completed': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-rose-100 text-rose-800">Revisit Completed</span>',
            'closed': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Closed</span>',
            'dead': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dead</span>',
            'on_hold': '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">On Hold</span>',
        };
        return badges[status] || '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">' + status + '</span>';
    }

    // Render pagination
    function renderPagination(data) {
        const paginations = [
            document.getElementById('pagination'),
            document.getElementById('paginationList'),
        ];
        if (data.last_page <= 1) {
            paginations.forEach((pagination) => {
                if (pagination) {
                    pagination.innerHTML = '';
                }
            });
            return;
        }

        let html = '<div class="flex items-center gap-2">';
        
        // Previous button
        if (data.current_page > 1) {
            html += `<button onclick="loadLeads(${data.current_page - 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>`;
        }
        
        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `<button onclick="loadLeads(${i})" class="px-3 py-2 border border-gray-300 rounded-lg ${i === data.current_page ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' : 'hover:bg-gray-50'}">${i}</button>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += `<span class="px-3 py-2">...</span>`;
            }
        }
        
        // Next button
        if (data.current_page < data.last_page) {
            html += `<button onclick="loadLeads(${data.current_page + 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>`;
        }
        
        html += '</div>';
        html += `<div class="text-sm text-gray-500">Showing ${data.from} to ${data.to} of ${data.total} leads</div>`;
        
        paginations.forEach((pagination) => {
            if (pagination) {
                pagination.innerHTML = html;
            }
        });
    }

    // Toggle lead details
    function toggleLeadDetails(leadId) {
        const detailsDiv = document.getElementById(`details-${leadId}`);
        const chevron = document.getElementById(`chevron-${leadId}`);
        const btn = document.getElementById(`viewDetailsBtn-${leadId}`);
        
        if (detailsDiv.classList.contains('hidden')) {
            detailsDiv.classList.remove('hidden');
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
            btn.innerHTML = `<i class="fas fa-chevron-up mr-2" id="chevron-${leadId}"></i> Hide Details`;
        } else {
            detailsDiv.classList.add('hidden');
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
            btn.innerHTML = `<i class="fas fa-chevron-down mr-2" id="chevron-${leadId}"></i> View Details`;
        }
    }


    // Open Edit Lead Modal
    async function openEditLeadModal(leadId) {
        currentLeadId = leadId;
        const modal = document.getElementById('editLeadModal');
        const content = document.getElementById('editLeadModalContent');
        
        // Find lead in current list or fetch from API
        let lead = allLeads.find(l => l.id === leadId);
        
        if (!lead) {
            try {
                const response = await fetch(`${API_BASE_URL}/leads/${leadId}`, {
                    headers: getAuthHeaders(),
                });
                const data = await response.json();
                lead = data;
            } catch (error) {
                console.error('Error loading lead:', error);
                alert('Failed to load lead details');
                return;
            }
        }

        content.innerHTML = `
            <form id="editLeadForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" id="editName" value="${lead.name || ''}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                        <input type="text" id="editPhone" value="${lead.phone || ''}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="editEmail" value="${lead.email || ''}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="editStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="new" ${lead.status === 'new' ? 'selected' : ''}>New</option>
                            <option value="contacted" ${lead.status === 'contacted' ? 'selected' : ''}>Contacted</option>
                            <option value="connected" ${lead.status === 'connected' ? 'selected' : ''}>Connected</option>
                            <option value="verified_prospect" ${lead.status === 'verified_prospect' ? 'selected' : ''}>Verified Prospect</option>
                            <option value="meeting_scheduled" ${lead.status === 'meeting_scheduled' ? 'selected' : ''}>Meeting Scheduled</option>
                            <option value="meeting_completed" ${lead.status === 'meeting_completed' ? 'selected' : ''}>Meeting Completed</option>
                            <option value="visit_scheduled" ${lead.status === 'visit_scheduled' ? 'selected' : ''}>Visit Scheduled</option>
                            <option value="visit_done" ${lead.status === 'visit_done' ? 'selected' : ''}>Visit Done</option>
                            <option value="revisited_scheduled" ${lead.status === 'revisited_scheduled' ? 'selected' : ''}>Revisit Scheduled</option>
                            <option value="revisited_completed" ${lead.status === 'revisited_completed' ? 'selected' : ''}>Revisit Completed</option>
                            <option value="closed" ${lead.status === 'closed' ? 'selected' : ''}>Closed</option>
                            <option value="dead" ${lead.status === 'dead' ? 'selected' : ''}>Dead</option>
                            <option value="on_hold" ${lead.status === 'on_hold' ? 'selected' : ''}>On Hold</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" id="editCity" value="${lead.city || ''}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input type="text" id="editState" value="${lead.state || ''}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                        <input type="text" id="editPincode" value="${lead.pincode || ''}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Location</label>
                        <input type="text" id="editPreferredLocation" value="${lead.preferred_location || ''}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Budget</label>
                        <input type="number" id="editBudget" value="${lead.budget || ''}" step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property Type</label>
                        <select id="editPropertyType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select</option>
                            <option value="apartment" ${lead.property_type === 'apartment' ? 'selected' : ''}>Apartment</option>
                            <option value="villa" ${lead.property_type === 'villa' ? 'selected' : ''}>Villa</option>
                            <option value="plot" ${lead.property_type === 'plot' ? 'selected' : ''}>Plot</option>
                            <option value="commercial" ${lead.property_type === 'commercial' ? 'selected' : ''}>Commercial</option>
                            <option value="other" ${lead.property_type === 'other' ? 'selected' : ''}>Other</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea id="editAddress" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">${lead.address || ''}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Requirements</label>
                    <textarea id="editRequirements" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">${lead.requirements || ''}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="editNotes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">${lead.notes || ''}</textarea>
                </div>
            </form>
        `;
        
        modal.classList.remove('hidden');
    }

    // Close Edit Lead Modal
    function closeEditLeadModal() {
        document.getElementById('editLeadModal').classList.add('hidden');
        currentLeadId = null;
    }

    // Submit Update Lead
    async function submitUpdateLead() {
        if (!currentLeadId) return;
        
        const form = document.getElementById('editLeadForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const updateData = {
            name: document.getElementById('editName').value,
            phone: document.getElementById('editPhone').value,
            email: document.getElementById('editEmail').value || null,
            city: document.getElementById('editCity').value || null,
            state: document.getElementById('editState').value || null,
            pincode: document.getElementById('editPincode').value || null,
            address: document.getElementById('editAddress').value || null,
            preferred_location: document.getElementById('editPreferredLocation').value || null,
            budget: document.getElementById('editBudget').value || null,
            property_type: document.getElementById('editPropertyType').value || null,
            requirements: document.getElementById('editRequirements').value || null,
            notes: document.getElementById('editNotes').value || null,
            status: document.getElementById('editStatus').value,
        };
        
        try {
            const response = await fetch(`${API_BASE_URL}/leads/${currentLeadId}`, {
                method: 'PUT',
                headers: getAuthHeaders(),
                body: JSON.stringify(updateData),
            });

            const data = await response.json();
            
            if (response.ok) {
                if (typeof showNotification === 'function') {
                    showNotification('Lead updated successfully!', 'success', 3000);
                } else {
                    alert('Lead updated successfully!');
                }
                closeEditLeadModal();
                loadLeads();
            } else {
                const errorMsg = data.message || data.errors || 'Failed to update lead';
                alert(typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg));
            }
        } catch (error) {
            console.error('Error updating lead:', error);
            alert('Failed to update lead. Please try again.');
        }
    }

    // Open Schedule Meeting Modal
    async function openScheduleMeetingModal(leadId) {
        currentLeadId = leadId;
        const modal = document.getElementById('scheduleMeetingModal');
        const content = document.getElementById('scheduleMeetingModalContent');
        
        // Find lead
        let lead = allLeads.find(l => l.id === leadId);
        if (!lead) {
            try {
                const response = await fetch(`${API_BASE_URL}/leads/${leadId}`, {
                    headers: getAuthHeaders(),
                });
                lead = await response.json();
            } catch (error) {
                console.error('Error loading lead:', error);
                alert('Failed to load lead details');
                return;
            }
        }

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDateTime = tomorrow.toISOString().slice(0, 16);

        content.innerHTML = `
            <form id="meetingForm" class="space-y-4">
                <input type="hidden" id="meetingLeadId" value="${leadId}">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
                        <input type="text" id="meetingCustomerName" value="${lead.name || ''}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                        <input type="text" id="meetingPhone" value="${lead.phone || ''}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                        <input type="text" id="meetingEmployee" value="${LOGGED_IN_USER_NAME}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                        <input type="text" id="meetingOccupation"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <input type="hidden" id="meetingDateOfVisit" value="">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                        <input type="text" id="meetingProject"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Budget Range *</label>
                        <select id="meetingBudgetRange" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select</option>
                            <option value="Under 50 Lac">Under 50 Lac</option>
                            <option value="50 Lac – 1 Cr">50 Lac – 1 Cr</option>
                            <option value="1 Cr – 2 Cr">1 Cr – 2 Cr</option>
                            <option value="2 Cr – 3 Cr">2 Cr – 3 Cr</option>
                            <option value="Above 3 Cr">Above 3 Cr</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Team Leader</label>
                        <input type="text" id="meetingTeamLeader" value="${MANAGER_NAME}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property Type *</label>
                        <select id="meetingPropertyType" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select</option>
                            <option value="Plot/Villa">Plot/Villa</option>
                            <option value="Flat">Flat</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Just Exploring">Just Exploring</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Mode *</label>
                        <select id="meetingPaymentMode" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select</option>
                            <option value="Self Fund">Self Fund</option>
                            <option value="Loan">Loan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tentative Period *</label>
                        <select id="meetingTentativePeriod" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select</option>
                            <option value="Within 1 Month">Within 1 Month</option>
                            <option value="Within 3 Months">Within 3 Months</option>
                            <option value="Within 6 Months">Within 6 Months</option>
                            <option value="More than 6 Months">More than 6 Months</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lead Type *</label>
                        <select id="meetingLeadType" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select</option>
                            <option value="New Visit">New Visit</option>
                            <option value="Revisited">Revisited</option>
                            <option value="Meeting" selected>Meeting</option>
                            <option value="Prospect">Prospect</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled At *</label>
                        <input type="datetime-local" id="meetingScheduledAt" required min="${minDateTime}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meeting Notes</label>
                        <textarea id="meetingNotes" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Photos (Optional)</label>
                        <input type="file" id="meetingPhotos" multiple accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">You can select multiple images (Max 5MB each)</p>
                    </div>
                </div>
            </form>
        `;
        
        modal.classList.remove('hidden');
        
        // Auto-fill date_of_visit from scheduled_at when scheduled_at changes
        const scheduledAtInput = document.getElementById('meetingScheduledAt');
        const dateOfVisitInput = document.getElementById('meetingDateOfVisit');
        
        if (scheduledAtInput && dateOfVisitInput) {
            scheduledAtInput.addEventListener('change', function() {
                const scheduledDate = new Date(this.value);
                if (scheduledDate && !isNaN(scheduledDate.getTime())) {
                    // Extract date part (YYYY-MM-DD)
                    const dateOnly = scheduledDate.toISOString().split('T')[0];
                    dateOfVisitInput.value = dateOnly;
                }
            });
        }
    }

    // Close Schedule Meeting Modal
    function closeScheduleMeetingModal() {
        document.getElementById('scheduleMeetingModal').classList.add('hidden');
        currentLeadId = null;
    }

    // Submit Create Meeting
    async function submitCreateMeeting() {
        if (!currentLeadId) return;
        
        const form = document.getElementById('meetingForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Ensure date_of_visit is filled from scheduled_at if empty
        const scheduledAt = document.getElementById('meetingScheduledAt').value;
        const dateOfVisitInput = document.getElementById('meetingDateOfVisit');
        if (!dateOfVisitInput.value && scheduledAt) {
            const scheduledDate = new Date(scheduledAt);
            if (scheduledDate && !isNaN(scheduledDate.getTime())) {
                dateOfVisitInput.value = scheduledDate.toISOString().split('T')[0];
            }
        }
        
        const formData = new FormData();
        formData.append('lead_id', currentLeadId);
        formData.append('customer_name', document.getElementById('meetingCustomerName').value);
        formData.append('phone', document.getElementById('meetingPhone').value);
        formData.append('employee', document.getElementById('meetingEmployee').value || '');
        formData.append('occupation', document.getElementById('meetingOccupation').value || '');
        formData.append('date_of_visit', dateOfVisitInput.value || '');
        formData.append('project', document.getElementById('meetingProject').value || '');
        formData.append('budget_range', document.getElementById('meetingBudgetRange').value);
        formData.append('team_leader', document.getElementById('meetingTeamLeader').value || '');
        formData.append('property_type', document.getElementById('meetingPropertyType').value);
        formData.append('payment_mode', document.getElementById('meetingPaymentMode').value);
        formData.append('tentative_period', document.getElementById('meetingTentativePeriod').value);
        formData.append('lead_type', document.getElementById('meetingLeadType').value);
        formData.append('scheduled_at', document.getElementById('meetingScheduledAt').value);
        formData.append('meeting_notes', document.getElementById('meetingNotes').value || '');
        
        const photos = document.getElementById('meetingPhotos').files;
        for (let i = 0; i < photos.length; i++) {
            formData.append('photos[]', photos[i]);
        }
        
        try {
            const response = await fetch(`${API_BASE_URL}/sales-manager/meetings`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();
            
            if (response.ok && data.success !== false) {
                if (typeof showNotification === 'function') {
                    showNotification('Meeting scheduled successfully!', 'success', 3000);
                } else {
                    alert('Meeting scheduled successfully!');
                }
                closeScheduleMeetingModal();
                loadLeads();
            } else {
                const errorMsg = data.message || data.errors || 'Failed to schedule meeting';
                alert(typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg));
            }
        } catch (error) {
            console.error('Error scheduling meeting:', error);
            alert('Failed to schedule meeting. Please try again.');
        }
    }

    // Open Schedule Site Visit Modal
    async function openScheduleSiteVisitModal(leadId) {
        currentLeadId = leadId;
        const modal = document.getElementById('scheduleSiteVisitModal');
        const content = document.getElementById('scheduleSiteVisitModalContent');
        
        // Load team members if not loaded
        if (teamMembers.length === 0) {
            await loadTeamMembers();
        }
        
        // Find lead
        let lead = allLeads.find(l => l.id === leadId);
        if (!lead) {
            try {
                const response = await fetch(`${API_BASE_URL}/leads/${leadId}`, {
                    headers: getAuthHeaders(),
                });
                lead = await response.json();
            } catch (error) {
                console.error('Error loading lead:', error);
                alert('Failed to load lead details');
                return;
            }
        }

        // Check if lead has existing site visits
        let hasExistingSiteVisits = false;
        try {
            const siteVisitResponse = await fetch(`${API_BASE_URL}/site-visits?lead_id=${leadId}`, {
                headers: getAuthHeaders(),
            });
            if (siteVisitResponse.ok) {
                const siteVisitData = await siteVisitResponse.json();
                hasExistingSiteVisits = siteVisitData.data && siteVisitData.data.length > 0;
            }
        } catch (error) {
            console.error('Error checking site visits:', error);
        }

        // Determine Lead Type: "New Visit" if no existing visits, "Revisited" if visits exist
        const leadTypeValue = hasExistingSiteVisits ? 'Revisited' : 'New Visit';

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDateTime = tomorrow.toISOString().slice(0, 16);

        const teamMembersOptions = teamMembers.map(member => 
            `<option value="${member.id}">${member.name}</option>`
        ).join('');

        content.innerHTML = `
            <form id="siteVisitForm" class="space-y-4">
                <input type="hidden" id="siteVisitLeadId" value="${leadId}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Property Name</label>
                    <input type="text" id="siteVisitPropertyName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled At *</label>
                    <input type="datetime-local" id="siteVisitScheduledAt" required min="${minDateTime}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <input type="text" id="siteVisitEmployee" value="${LOGGED_IN_USER_NAME}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lead Type *</label>
                    <select id="siteVisitLeadType" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select</option>
                        <option value="New Visit" ${leadTypeValue === 'New Visit' ? 'selected' : ''}>New Visit</option>
                        <option value="Revisited" ${leadTypeValue === 'Revisited' ? 'selected' : ''}>Revisited</option>
                        <option value="Meeting">Meeting</option>
                        <option value="Prospect">Prospect</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                    <select id="siteVisitAssignedTo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">Select Team Member</option>
                        ${teamMembersOptions}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Visit Notes</label>
                    <textarea id="siteVisitNotes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
            </form>
        `;
        
        modal.classList.remove('hidden');
    }

    // Close Schedule Site Visit Modal
    function closeScheduleSiteVisitModal() {
        document.getElementById('scheduleSiteVisitModal').classList.add('hidden');
        currentLeadId = null;
    }

    // Submit Create Site Visit
    async function submitCreateSiteVisit() {
        if (!currentLeadId) return;
        
        const form = document.getElementById('siteVisitForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const visitData = {
            lead_id: currentLeadId,
            property_name: document.getElementById('siteVisitPropertyName').value || null,
            scheduled_at: document.getElementById('siteVisitScheduledAt').value,
            assigned_to: document.getElementById('siteVisitAssignedTo').value || null,
            employee: document.getElementById('siteVisitEmployee').value || null,
            lead_type: document.getElementById('siteVisitLeadType').value || null,
            visit_notes: document.getElementById('siteVisitNotes').value || null,
        };
        
        try {
            const response = await fetch(`${API_BASE_URL}/site-visits`, {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(visitData),
            });

            const data = await response.json();
            
            if (response.ok && data.success !== false) {
                if (typeof showNotification === 'function') {
                    showNotification('Site visit scheduled successfully!', 'success', 3000);
                } else {
                    alert('Site visit scheduled successfully!');
                }
                closeScheduleSiteVisitModal();
                loadLeads();
            } else {
                const errorMsg = data.message || data.errors || 'Failed to schedule site visit';
                alert(typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg));
            }
        } catch (error) {
            console.error('Error scheduling site visit:', error);
            alert('Failed to schedule site visit. Please try again.');
        }
    }

    // Handle search with debounce
    function handleSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadLeads(1);
        }, 500);
    }

    // Open Add Lead Modal
    function openAddLeadModal() {
        const modal = document.getElementById('addLeadModal');
        const form = document.getElementById('addLeadForm');
        const errorDiv = document.getElementById('addLeadError');
        
        // Reset form
        form.reset();
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
        
        modal.classList.remove('hidden');
    }

    // Close Add Lead Modal
    function closeAddLeadModal() {
        const modal = document.getElementById('addLeadModal');
        const form = document.getElementById('addLeadForm');
        const errorDiv = document.getElementById('addLeadError');
        
        modal.classList.add('hidden');
        form.reset();
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }

    // Submit Add Lead
    async function submitAddLead() {
        const form = document.getElementById('addLeadForm');
        const errorDiv = document.getElementById('addLeadError');
        const nameInput = document.getElementById('addLeadName');
        const phoneInput = document.getElementById('addLeadPhone');
        
        // Reset error
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const leadData = {
            name: nameInput.value.trim(),
            phone: phoneInput.value.trim(),
        };
        
        try {
            const response = await fetch(`${API_BASE_URL}/leads`, {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(leadData),
            });
            
            const data = await response.json();
            
            if (response.ok) {
                if (typeof showNotification === 'function') {
                    showNotification('Lead added successfully!', 'success', 3000);
                } else {
                    alert('Lead added successfully!');
                }
                closeAddLeadModal();
                loadLeads(1); // Reload leads list
            } else {
                const errorMsg = data.message || (data.errors ? JSON.stringify(data.errors) : 'Failed to add lead');
                errorDiv.textContent = typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg);
                errorDiv.style.display = 'block';
            }
        } catch (error) {
            console.error('Error adding lead:', error);
            errorDiv.textContent = 'Failed to add lead. Please try again.';
            errorDiv.style.display = 'block';
        }
    }

    // View short details modal
    async function viewShortDetails(leadId) {
        const modal = document.getElementById('shortDetailsModal');
        const content = document.getElementById('shortDetailsContent');
        
        // Show loading state
        content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div><p class="mt-4 text-gray-600">Loading lead details...</p></div>';
        modal.classList.remove('hidden');
        
        try {
            const response = await fetch(`${API_BASE_URL}/leads/${leadId}`, {
                headers: getAuthHeaders(),
            });
            
            if (!response.ok) {
                throw new Error('Failed to load lead details');
            }
            
            const data = await response.json();
            const lead = data.data || data;
            
            // Render lead details
            const statusLabels = {
                'new': 'New',
                'contacted': 'Contacted',
                'connected': 'Connected',
                'verified_prospect': 'Verified Prospect',
                'meeting_scheduled': 'Meeting Scheduled',
                'meeting_completed': 'Meeting Completed',
                'visit_scheduled': 'Visit Scheduled',
                'visit_done': 'Visit Done',
                'revisited_scheduled': 'Revisit Scheduled',
                'revisited_completed': 'Revisit Completed',
                'closed': 'Closed',
                'dead': 'Dead',
                'on_hold': 'On Hold',
            };
            
            const statusLabel = statusLabels[lead.status] || lead.status;
            const createdDate = new Date(lead.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            
            content.innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Name</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.name || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.phone || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.email || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1 text-sm text-gray-900">${statusLabel}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Location</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.city || ''}${lead.city && lead.state ? ', ' : ''}${lead.state || ''}${lead.pincode ? ' - ' + lead.pincode : ''}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Budget</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.budget || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Preferred Location</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.preferred_location || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Created</label>
                            <p class="mt-1 text-sm text-gray-900">${createdDate}</p>
                        </div>
                    </div>
                    ${lead.requirements ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Requirements</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.requirements}</p>
                        </div>
                    ` : ''}
                    ${lead.notes ? `
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Notes</label>
                            <p class="mt-1 text-sm text-gray-900">${lead.notes}</p>
                        </div>
                    ` : ''}
                    ${lead.form_fields && Object.keys(lead.form_fields).length > 0 ? `
                        <div class="pt-4 border-t">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Form Data</h4>
                            <div class="grid grid-cols-2 gap-4">
                                ${Object.entries(lead.form_fields).map(([key, value]) => `
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</label>
                                        <p class="mt-1 text-sm text-gray-900">${value || 'N/A'}</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                    <div class="pt-4 border-t">
                        <a href="/leads/${leadId}" class="block w-full text-center px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                            View Full Details
                        </a>
                    </div>
                </div>
            `;
        } catch (error) {
            content.innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600">Failed to load lead details. Please try again.</p>
                    <button onclick="viewShortDetails(${leadId})" class="mt-4 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">
                        Retry
                    </button>
                </div>
            `;
        }
    }

    function closeShortDetailsModal() {
        document.getElementById('shortDetailsModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('shortDetailsModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeShortDetailsModal();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeShortDetailsModal();
        }
    });

    // Load leads on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTeamMembers().then(() => {
            loadLeads();
        });
    });
</script>
@endpush
