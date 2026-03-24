@extends('finance-manager.layout')

@section('title', 'Finance Manager Dashboard')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .stat-card h3 {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .stat-card .value {
        font-size: 32px;
        font-weight: 600;
        color: #063A1C;
    }
</style>
@endpush

@section('content')
<div>
    <h2 style="font-size: 24px; font-weight: 600; color: #063A1C; margin-bottom: 24px;">Dashboard</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Pending Incentives</h3>
            <div class="value" id="pendingCount">0</div>
        </div>
        <div class="stat-card">
            <h3>Approved This Month</h3>
            <div class="value" id="approvedCount">0</div>
        </div>
        <div class="stat-card">
            <h3>Total Amount (Pending)</h3>
            <div class="value" id="pendingAmount">₹0</div>
        </div>
        <div class="stat-card">
            <h3>Total Amount (Approved)</h3>
            <div class="value" id="approvedAmount">₹0</div>
        </div>
    </div>

    <div style="background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="font-size: 18px; font-weight: 600; color: #063A1C; margin-bottom: 16px;">Recent Activity</h3>
        <div id="recentActivity" style="color: #6b7280;">
            Loading...
        </div>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE_URL = '/api';
    const token = document.querySelector('meta[name="api-token"]').getAttribute('content');

    function getToken() {
        return token;
    }

    async function loadDashboardStats() {
        try {
            const response = await fetch(`${API_BASE_URL}/finance-manager/incentives`, {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                },
            });
            const result = await response.json();
            const incentives = result?.data || [];

            const pending = incentives.filter(i => i.status === 'pending_finance_manager');
            const approved = incentives.filter(i => i.status === 'verified');
            const thisMonth = new Date().getMonth();
            const approvedThisMonth = approved.filter(i => {
                const date = new Date(i.finance_manager_verified_at || i.created_at);
                return date.getMonth() === thisMonth;
            });

            const pendingAmount = pending.reduce((sum, i) => sum + parseFloat(i.amount || 0), 0);
            const approvedAmount = approvedThisMonth.reduce((sum, i) => sum + parseFloat(i.amount || 0), 0);

            document.getElementById('pendingCount').textContent = pending.length;
            document.getElementById('approvedCount').textContent = approvedThisMonth.length;
            document.getElementById('pendingAmount').textContent = `₹${pendingAmount.toFixed(2)}`;
            document.getElementById('approvedAmount').textContent = `₹${approvedAmount.toFixed(2)}`;

            // Update badge
            document.getElementById('pendingIncentivesBadge').textContent = pending.length;

            // Recent activity
            const recent = incentives.slice(0, 5);
            if (recent.length === 0) {
                document.getElementById('recentActivity').innerHTML = '<p>No recent activity</p>';
            } else {
                document.getElementById('recentActivity').innerHTML = recent.map(i => {
                    const date = new Date(i.created_at).toLocaleDateString('en-IN');
                    return `<p style="padding: 8px 0; border-bottom: 1px solid #f3f4f6;">${i.user?.name || 'User'} - ₹${parseFloat(i.amount).toFixed(2)} - ${i.status} - ${date}</p>`;
                }).join('');
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    loadDashboardStats();
    setInterval(loadDashboardStats, 60000); // Refresh every minute
</script>
@endpush
@endsection
