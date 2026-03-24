@extends('finance-manager.layout')

@section('title', 'Incentive Management')

@push('styles')
<style>
    .filters {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filters select {
        padding: 10px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
    }
    .incentive-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 16px;
    }
    .incentive-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 12px;
    }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-verified {
        background: #d1fae5;
        color: #065f46;
    }
    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
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
</style>
@endpush

@section('content')
<div>
    <h2 style="font-size: 24px; font-weight: 600; color: #063A1C; margin-bottom: 24px;">Incentive Management</h2>

    <div class="filters">
        <select id="statusFilter" onchange="loadIncentives()">
            <option value="">All Status</option>
            <option value="pending_finance_manager">Pending</option>
            <option value="verified">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
        <select id="typeFilter" onchange="loadIncentives()">
            <option value="">All Types</option>
            <option value="closer">Closer</option>
            <option value="site_visit">Site Visit</option>
        </select>
    </div>

    <div id="incentivesContainer">
        <div style="text-align: center; padding: 40px; color: #6b7280;">
            <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 12px;"></i>
            <p>Loading incentives...</p>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Reject Incentive</h3>
        <form id="rejectForm">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Rejection Reason <span style="color: #ef4444;">*</span></label>
                <textarea id="rejectionReason" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; min-height: 100px;" placeholder="Enter rejection reason"></textarea>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn" style="background: #6b7280; color: white;" onclick="closeRejectModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitReject()">Reject</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE_URL = '/api';
    const token = document.querySelector('meta[name="api-token"]').getAttribute('content');
    let currentRejectIncentiveId = null;

    function getToken() {
        return token;
    }

    async function loadIncentives() {
        const container = document.getElementById('incentivesContainer');
        const statusFilter = document.getElementById('statusFilter').value;
        const typeFilter = document.getElementById('typeFilter').value;

        container.innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 12px;"></i><p>Loading...</p></div>';

        try {
            let url = `${API_BASE_URL}/finance-manager/incentives`;
            const params = new URLSearchParams();
            if (statusFilter) params.append('status', statusFilter);
            if (typeFilter) params.append('type', typeFilter);
            if (params.toString()) url += '?' + params.toString();

            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Accept': 'application/json',
                },
            });
            const result = await response.json();
            const incentives = result?.data || [];

            if (incentives.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 40px; color: #6b7280;"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i><p>No incentives found</p></div>';
                return;
            }

            const html = incentives.map(incentive => {
                const statusBadge = incentive.status === 'pending_finance_manager' ? 'badge-pending' :
                                   incentive.status === 'verified' ? 'badge-verified' : 'badge-rejected';
                const statusText = incentive.status === 'pending_finance_manager' ? 'Pending' :
                                 incentive.status === 'verified' ? 'Approved' : 'Rejected';
                const leadName = incentive.site_visit?.lead?.name || incentive.site_visit?.customer_name || 'N/A';
                const requestedBy = incentive.user?.name || 'N/A';
                const date = new Date(incentive.created_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });

                return `
                <div class="incentive-card">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                        <div>
                            <h3>${leadName}</h3>
                            <p style="color: #6b7280; font-size: 14px; margin: 4px 0;">Requested by: ${requestedBy}</p>
                            <p style="color: #6b7280; font-size: 14px; margin: 4px 0;">Date: ${date}</p>
                        </div>
                        <div>
                            <span class="badge ${statusBadge}">${statusText}</span>
                        </div>
                    </div>
                    <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #6b7280;">Type:</span>
                            <span style="font-weight: 500;">${incentive.type === 'closer' ? 'Closer' : 'Site Visit'}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #6b7280;">Amount:</span>
                            <span style="font-weight: 600; color: #059669; font-size: 18px;">₹${parseFloat(incentive.amount).toFixed(2)}</span>
                        </div>
                        ${incentive.site_visit?.property_name ? `
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280;">Property:</span>
                            <span style="font-weight: 500;">${incentive.site_visit.property_name}</span>
                        </div>
                        ` : ''}
                    </div>
                    ${incentive.status === 'pending_finance_manager' ? `
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-success" style="flex: 1;" onclick="approveIncentive(${incentive.id})">
                            <i class="fas fa-check mr-2"></i>Approve
                        </button>
                        <button class="btn btn-danger" style="flex: 1;" onclick="showRejectModal(${incentive.id})">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                    </div>
                    ` : ''}
                    ${incentive.rejection_reason ? `
                    <div style="margin-top: 12px; padding: 12px; background: #fee2e2; border-radius: 8px; border-left: 4px solid #ef4444;">
                        <strong style="color: #991b1b;">Rejection Reason:</strong>
                        <p style="color: #991b1b; margin-top: 4px;">${incentive.rejection_reason}</p>
                    </div>
                    ` : ''}
                </div>
                `;
            }).join('');

            container.innerHTML = html;

            // Update pending count badge
            const pendingCount = incentives.filter(i => i.status === 'pending_finance_manager').length;
            document.getElementById('pendingIncentivesBadge').textContent = pendingCount;
        } catch (error) {
            console.error('Error loading incentives:', error);
            container.innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 12px;"></i><p>Error loading incentives</p></div>';
        }
    }

    async function approveIncentive(incentiveId) {
        if (!confirm('Are you sure you want to approve this incentive?')) return;

        try {
            const response = await fetch(`${API_BASE_URL}/finance-manager/incentives/${incentiveId}/verify`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();
            if (result && result.success) {
                alert('Incentive approved successfully!');
                loadIncentives();
            } else {
                alert(result.message || 'Failed to approve incentive');
            }
        } catch (error) {
            console.error('Error approving incentive:', error);
            alert('Network error. Please try again.');
        }
    }

    function showRejectModal(incentiveId) {
        currentRejectIncentiveId = incentiveId;
        document.getElementById('rejectModal').classList.add('show');
        document.getElementById('rejectionReason').value = '';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.remove('show');
        currentRejectIncentiveId = null;
        document.getElementById('rejectionReason').value = '';
    }

    async function submitReject() {
        if (!currentRejectIncentiveId) return;

        const reason = document.getElementById('rejectionReason').value.trim();
        if (!reason) {
            alert('Please enter a rejection reason');
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}/finance-manager/incentives/${currentRejectIncentiveId}/reject`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${getToken()}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ reason: reason }),
            });

            const result = await response.json();
            if (result && result.success) {
                alert('Incentive rejected successfully!');
                closeRejectModal();
                loadIncentives();
            } else {
                alert(result.message || 'Failed to reject incentive');
            }
        } catch (error) {
            console.error('Error rejecting incentive:', error);
            alert('Network error. Please try again.');
        }
    }

    // Close modal on outside click
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });

    loadIncentives();
    setInterval(loadIncentives, 60000); // Refresh every minute
</script>
@endpush
@endsection
