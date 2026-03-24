@extends('sales-head.layout')

@section('title', 'Dashboard - Associate Director')
@section('page-title', 'Associate Director Dashboard')

@push('styles')
<style>
    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #E5DED4;
    }
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #063A1C;
        margin-top: 8px;
    }
    .stat-label {
        font-size: 14px;
        color: #B3B5B4;
        font-weight: 500;
    }
    .performance-table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #E5DED4;
        overflow: hidden;
    }
    .table-header {
        background: #F7F6F3;
        padding: 16px 20px;
        border-bottom: 1px solid #E5DED4;
        font-weight: 600;
        color: #063A1C;
    }
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
</style>
@endpush

@section('content')
<div id="dashboard-content">
    <!-- Loading State -->
    <div id="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-brand"></div>
        <p class="mt-4 text-[#B3B5B4]">Loading dashboard data...</p>
    </div>

    <!-- Main Stats Grid -->
    <div id="main-stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 hidden">
        <div class="stat-card">
            <div class="stat-label">Total Active Leads</div>
            <div class="stat-value" id="total-leads">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Conversions</div>
            <div class="stat-value" id="closed-won">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Conversion Rate</div>
            <div class="stat-value" id="conversion-rate">0%</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Verifications</div>
            <div class="stat-value text-yellow-600" id="pending-verifications">0</div>
        </div>
    </div>

    <!-- Team Overview Stats -->
    <div id="team-stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 hidden">
        <div class="stat-card">
            <div class="stat-label">Active Senior Managers</div>
            <div class="stat-value" id="active-managers">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Sales Executives</div>
            <div class="stat-value" id="active-executives">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Sales Executives</div>
            <div class="stat-value" id="active-telecallers">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Today's Leads</div>
            <div class="stat-value" id="today-leads">0</div>
        </div>
    </div>

    <!-- Today's Metrics -->
    <div id="today-metrics" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 hidden">
        <div class="stat-card">
            <div class="stat-label">Today's Conversions</div>
            <div class="stat-value text-green-600" id="today-conversions">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Today's Site Visits</div>
            <div class="stat-value" id="today-site-visits">0</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Upcoming Site Visits</div>
            <div class="stat-value" id="upcoming-site-visits">0</div>
        </div>
    </div>

    <!-- Performance Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Senior Managers Performance -->
        <div class="performance-table hidden" id="managers-performance-section">
            <div class="table-header">Senior Managers Performance</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Size</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leads</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Converted</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                        </tr>
                    </thead>
                    <tbody id="managers-performance-body" class="bg-white divide-y divide-gray-200">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sales Executives Performance -->
        <div class="performance-table hidden" id="executives-performance-section">
            <div class="table-header">Sales Executives Performance</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Executive</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leads</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Converted</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                        </tr>
                    </thead>
                    <tbody id="executives-performance-body" class="bg-white divide-y divide-gray-200">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sales Executives Performance -->
    <div class="performance-table mb-6 hidden" id="telecallers-performance-section">
        <div class="table-header">Sales Executives Performance</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales Executive</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leads</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qualified</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                    </tr>
                </thead>
                <tbody id="telecallers-performance-body" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lead Pipeline & Source Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Lead Pipeline -->
        <div class="performance-table hidden" id="lead-pipeline-section">
            <div class="table-header">Lead Pipeline by Status</div>
            <div class="p-6">
                <canvas id="leadPipelineChart" class="chart-container"></canvas>
            </div>
        </div>

        <!-- Lead Source Distribution -->
        <div class="performance-table hidden" id="lead-source-section">
            <div class="table-header">Lead Source Distribution</div>
            <div class="p-6">
                <canvas id="leadSourceChart" class="chart-container"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Recent Leads -->
        <div class="performance-table hidden" id="recent-leads-section">
            <div class="table-header">Recent Leads</div>
            <div class="p-4 max-h-96 overflow-y-auto" id="recent-leads-body">
                <!-- Data will be loaded here -->
            </div>
        </div>

        <!-- Recent Conversions -->
        <div class="performance-table hidden" id="recent-conversions-section">
            <div class="table-header">Recent Conversions</div>
            <div class="p-4 max-h-96 overflow-y-auto" id="recent-conversions-body">
                <!-- Data will be loaded here -->
            </div>
        </div>

        <!-- Pending Verifications -->
        <div class="performance-table hidden" id="pending-verifications-section">
            <div class="table-header">Pending Verifications</div>
            <div class="p-4 max-h-96 overflow-y-auto" id="pending-verifications-body">
                <!-- Data will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Upcoming Follow-ups -->
    <div class="performance-table mb-6 hidden" id="upcoming-followups-section">
        <div class="table-header">Upcoming Follow-ups</div>
        <div class="p-4 max-h-96 overflow-y-auto" id="upcoming-followups-body">
            <!-- Data will be loaded here -->
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const API_BASE_URL = '/sales-head/dashboard/data';

    async function loadDashboardData() {
        try {
            const response = await fetch(API_BASE_URL, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                throw new Error('Invalid response from server. Please try again.');
            }

            if (!response.ok) {
                throw new Error(data.message || data.error || 'Failed to load dashboard data (Status: ' + response.status + ')');
            }

            renderDashboard(data);
        } catch (error) {
            console.error('Error loading dashboard:', error);
            const errorMessage = error.message || 'Error loading dashboard data. Please refresh the page.';
            document.getElementById('loading').innerHTML = '<div class="text-red-600 p-4 bg-red-50 rounded"><p class="font-semibold">Error:</p><p>' + errorMessage + '</p><button onclick="location.reload()" class="mt-2 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Refresh Page</button></div>';
        }
    }

    function renderDashboard(data) {
        // Hide loading
        document.getElementById('loading').classList.add('hidden');

        // Show sections
        document.getElementById('main-stats').classList.remove('hidden');
        document.getElementById('team-stats').classList.remove('hidden');
        document.getElementById('today-metrics').classList.remove('hidden');

        // Main Stats
        document.getElementById('total-leads').textContent = data.stats.total_leads || 0;
        document.getElementById('closed-won').textContent = data.stats.closed_won || 0;
        document.getElementById('conversion-rate').textContent = (data.stats.conversion_rate || 0) + '%';
        document.getElementById('pending-verifications').textContent = data.stats.pending_verifications || 0;

        // Team Stats
        document.getElementById('active-managers').textContent = data.stats.active_sales_managers || 0;
        document.getElementById('active-executives').textContent = data.stats.active_sales_executives || 0;
        document.getElementById('active-telecallers').textContent = data.stats.active_telecallers || 0;
        document.getElementById('today-leads').textContent = data.stats.today_leads || 0;

        // Today's Metrics
        document.getElementById('today-conversions').textContent = data.stats.today_conversions || 0;
        document.getElementById('today-site-visits').textContent = data.stats.today_site_visits || 0;
        document.getElementById('upcoming-site-visits').textContent = data.stats.upcoming_site_visits || 0;

        // Render Performance Tables
        renderManagersPerformance(data.managers_performance || []);
        renderExecutivesPerformance(data.executives_performance || []);
        renderTelecallersPerformance(data.telecallers_performance || []);

        // Render Charts
        renderLeadPipelineChart(data.lead_pipeline || {});
        renderLeadSourceChart(data.lead_source_distribution || {});

        // Render Recent Activities
        renderRecentLeads(data.recent_leads || []);
        renderRecentConversions(data.recent_conversions || []);
        renderPendingVerifications(data.pending_verifications || []);
        renderUpcomingFollowups(data.upcoming_followups || []);
    }

    function renderManagersPerformance(managers) {
        const tbody = document.getElementById('managers-performance-body');
        tbody.innerHTML = '';

        if (managers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No data available</td></tr>';
            return;
        }

        managers.forEach(manager => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${manager.name}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${manager.team_size}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${manager.total_leads}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${manager.leads_converted}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium ${manager.conversion_rate >= 20 ? 'text-green-600' : manager.conversion_rate >= 10 ? 'text-yellow-600' : 'text-red-600'}">${manager.conversion_rate}%</td>
            `;
            tbody.appendChild(row);
        });

        document.getElementById('managers-performance-section').classList.remove('hidden');
    }

    function renderExecutivesPerformance(executives) {
        const tbody = document.getElementById('executives-performance-body');
        tbody.innerHTML = '';

        if (executives.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No data available</td></tr>';
            return;
        }

        executives.forEach(executive => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${executive.name}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${executive.manager_name}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${executive.total_leads}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${executive.leads_converted}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium ${executive.conversion_rate >= 20 ? 'text-green-600' : executive.conversion_rate >= 10 ? 'text-yellow-600' : 'text-red-600'}">${executive.conversion_rate}%</td>
            `;
            tbody.appendChild(row);
        });

        document.getElementById('executives-performance-section').classList.remove('hidden');
    }

    function renderTelecallersPerformance(telecallers) {
        const tbody = document.getElementById('telecallers-performance-body');
        tbody.innerHTML = '';

        if (telecallers.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No data available</td></tr>';
            return;
        }

        telecallers.forEach(telecaller => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">${telecaller.name}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${telecaller.manager_name}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${telecaller.total_leads}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${telecaller.leads_qualified}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium ${telecaller.qualification_rate >= 30 ? 'text-green-600' : telecaller.qualification_rate >= 15 ? 'text-yellow-600' : 'text-red-600'}">${telecaller.qualification_rate}%</td>
            `;
            tbody.appendChild(row);
        });

        document.getElementById('telecallers-performance-section').classList.remove('hidden');
    }

    function renderLeadPipelineChart(pipeline) {
        const ctx = document.getElementById('leadPipelineChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(pipeline).map(key => key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
                datasets: [{
                    label: 'Leads',
                    data: Object.values(pipeline),
                    backgroundColor: '#205A44',
                    borderColor: '#063A1C',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        document.getElementById('lead-pipeline-section').classList.remove('hidden');
    }

    function renderLeadSourceChart(sources) {
        const ctx = document.getElementById('leadSourceChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(sources).map(key => key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
                datasets: [{
                    data: Object.values(sources),
                    backgroundColor: ['#205A44', '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        document.getElementById('lead-source-section').classList.remove('hidden');
    }

    // Status mapping function for display
    function getStatusDisplay(status) {
        const statusMap = {
            'new': { label: 'New', class: 'text-blue-800 bg-blue-100' },
            'connected': { label: 'Connected', class: 'text-blue-900 bg-blue-200' },
            'verified_prospect': { label: 'Verified Prospect', class: 'text-purple-800 bg-purple-100' },
            'meeting_scheduled': { label: 'Meeting Scheduled', class: 'text-indigo-800 bg-indigo-100' },
            'meeting_completed': { label: 'Meeting Completed', class: 'text-cyan-800 bg-cyan-100' },
            'visit_scheduled': { label: 'Visit Scheduled', class: 'text-violet-800 bg-violet-100' },
            'visit_done': { label: 'Visit Done', class: 'text-pink-800 bg-pink-100' },
            'revisited_scheduled': { label: 'Revisit Scheduled', class: 'text-fuchsia-800 bg-fuchsia-100' },
            'revisited_completed': { label: 'Revisit Completed', class: 'text-rose-800 bg-rose-100' },
            'closed': { label: 'Closed', class: 'text-green-800 bg-green-100' },
            'dead': { label: 'Dead', class: 'text-red-800 bg-red-100' },
            'on_hold': { label: 'On Hold', class: 'text-gray-800 bg-gray-100' },
            // Old statuses (backward compatibility)
            'contacted': { label: 'Contacted', class: 'text-yellow-800 bg-yellow-100' },
            'qualified': { label: 'Verified Prospect', class: 'text-purple-800 bg-purple-100' },
            'site_visit_scheduled': { label: 'Visit Scheduled', class: 'text-violet-800 bg-violet-100' },
            'site_visit_completed': { label: 'Visit Done', class: 'text-pink-800 bg-pink-100' },
            'closed_won': { label: 'Closed', class: 'text-green-800 bg-green-100' },
            'closed_lost': { label: 'Dead', class: 'text-red-800 bg-red-100' },
        };
        return statusMap[status] || { label: status || 'N/A', class: 'text-gray-800 bg-gray-100' };
    }

    function renderRecentLeads(leads) {
        const container = document.getElementById('recent-leads-body');
        container.innerHTML = '';

        if (leads.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No recent leads</p>';
            return;
        }

        leads.forEach(lead => {
            const statusInfo = getStatusDisplay(lead.status);
            const div = document.createElement('div');
            div.className = 'mb-3 p-3 bg-gray-50 rounded-lg';
            div.innerHTML = `
                <div class="font-medium text-sm text-gray-900">${lead.name}</div>
                <div class="text-xs text-gray-500 mt-1">${lead.phone}</div>
                <div class="text-xs text-gray-500 mt-1">Status: <span class="font-medium px-2 py-1 rounded ${statusInfo.class}">${statusInfo.label}</span></div>
            `;
            container.appendChild(div);
        });

        document.getElementById('recent-leads-section').classList.remove('hidden');
    }

    function renderRecentConversions(conversions) {
        const container = document.getElementById('recent-conversions-body');
        container.innerHTML = '';

        if (conversions.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No recent conversions</p>';
            return;
        }

        conversions.forEach(lead => {
            const div = document.createElement('div');
            div.className = 'mb-3 p-3 bg-green-50 rounded-lg';
            div.innerHTML = `
                <div class="font-medium text-sm text-gray-900">${lead.name}</div>
                <div class="text-xs text-gray-500 mt-1">${lead.phone}</div>
                <div class="text-xs text-green-600 font-medium mt-1">Converted</div>
            `;
            container.appendChild(div);
        });

        document.getElementById('recent-conversions-section').classList.remove('hidden');
    }

    function renderPendingVerifications(verifications) {
        const container = document.getElementById('pending-verifications-body');
        container.innerHTML = '';

        if (verifications.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No pending verifications</p>';
            return;
        }

        verifications.forEach(lead => {
            const div = document.createElement('div');
            div.className = 'mb-3 p-3 bg-yellow-50 rounded-lg';
            div.innerHTML = `
                <div class="font-medium text-sm text-gray-900">${lead.name}</div>
                <div class="text-xs text-gray-500 mt-1">${lead.phone}</div>
                <div class="text-xs text-yellow-600 font-medium mt-1">Pending Verification</div>
                <a href="/crm/verifications" class="text-xs text-blue-600 mt-2 inline-block">View Details →</a>
            `;
            container.appendChild(div);
        });

        document.getElementById('pending-verifications-section').classList.remove('hidden');
    }

    function renderUpcomingFollowups(followups) {
        const container = document.getElementById('upcoming-followups-body');
        container.innerHTML = '';

        if (followups.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-sm text-center py-4">No upcoming follow-ups</p>';
            return;
        }

        followups.forEach(followup => {
            const div = document.createElement('div');
            div.className = 'mb-3 p-3 bg-blue-50 rounded-lg';
            const scheduledDate = new Date(followup.scheduled_at);
            div.innerHTML = `
                <div class="font-medium text-sm text-gray-900">${followup.lead?.name || 'N/A'}</div>
                <div class="text-xs text-gray-500 mt-1">${followup.lead?.phone || 'N/A'}</div>
                <div class="text-xs text-blue-600 font-medium mt-1">${scheduledDate.toLocaleDateString()} ${scheduledDate.toLocaleTimeString()}</div>
            `;
            container.appendChild(div);
        });

        document.getElementById('upcoming-followups-section').classList.remove('hidden');
    }

    // Load dashboard on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();
    });
</script>
@endpush
@endsection

