@extends('layouts.app')

@section('title', 'CRM - Base CRM')
@section('page-title', 'CRM')
@section('header-below-title')
@endsection

@push('styles')
<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    :root {
        --primary-green: #063A1C;
        --secondary-green: #205A44;
        --bg-beige: #F7F6F3;
        --border-color: #E5DED4;
    }
    
    body {
        background-color: var(--bg-beige);
    }
    
    .stats-card-gradient {
        background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
        color: white;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .stats-card-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .stats-card {
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .telecaller-card {
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--secondary-green) 0%, var(--primary-green) 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .telecaller-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .telecaller-card .text-muted {
        color: rgba(255, 255, 255, 0.7) !important;
    }
    
    .telecaller-card .fw-bold {
        color: white;
    }
    
    .telecaller-card .text-success {
        color: #90ee90 !important;
    }
    
    .telecaller-card .text-danger {
        color: #ffb3b3 !important;
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background-color: var(--primary-green);
        border-color: var(--primary-green);
    }
    
    .btn-primary:hover {
        background-color: var(--secondary-green);
        border-color: var(--secondary-green);
    }
    
    /* Phone / small screen responsive – side blank space na aaye */
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 0;
            padding-right: 0;
            max-width: 100%;
            width: 100%;
            overflow-x: hidden;
        }
        .container-fluid .row { margin-left: 0; margin-right: 0; }
        .container-fluid .card { max-width: 100%; }
        .card-body { padding: 0.75rem; overflow-x: hidden; }
        .card-header { padding: 0.75rem 1rem; }
        .card-header h5 { font-size: 1rem; }
    }
    
    /* Sales Executive Performance: role + date filter in one line */
    #perf-role-filter,
    #perf-date-range {
        display: inline-block;
    }
    .card-header .d-flex.flex-nowrap {
        white-space: nowrap;
    }
    
    /* Sales Executive Performance table: scroll andar, page full width */
    .crm-perf-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    .crm-perf-table-wrap .table {
        min-width: 640px;
        margin-bottom: 0;
    }
    .crm-perf-table-wrap .table th,
    .crm-perf-table-wrap .table td {
        white-space: nowrap;
        vertical-align: middle;
    }
    @media (max-width: 768px) {
        .crm-perf-table-wrap .table { font-size: 0.8rem; min-width: 560px; }
        .crm-perf-table-wrap .table th,
        .crm-perf-table-wrap .table td { padding: 0.4rem 0.5rem; }
        .crm-perf-table-wrap .table th:first-child,
        .crm-perf-table-wrap .table td:first-child {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 1;
            box-shadow: 2px 0 4px rgba(0,0,0,0.06);
            min-width: 90px;
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .crm-perf-table-wrap .table thead th:first-child { background: #f8f9fa; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Notification Alert -->
    <div id="notification-alert" class="alert alert-success alert-dismissible fade d-none" role="alert">
        <span id="notification-message"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Sales Executive Performance Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="mb-0">Sales Executive Performance</h5>
                    <div class="d-flex flex-nowrap align-items-center gap-2">
                        <select id="perf-role-filter" class="form-select form-select-sm flex-shrink-0" style="max-width: 180px; width: 180px;" title="User type">
                            <option value="all">All</option>
                        </select>
                        <select id="perf-date-range" class="form-select form-select-sm flex-shrink-0" style="max-width: 160px; width: 160px;" title="Date range">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="this_year">This Year</option>
                            <option value="all_time">All Time</option>
                            <option value="custom">Custom</option>
                        </select>
                        <span id="perf-custom-date-wrap" class="d-none align-middle flex-nowrap">
                            <input type="date" id="perf-date-start" class="form-control form-control-sm d-inline-block" style="max-width: 130px;" title="From">
                            <span class="mx-1">–</span>
                            <input type="date" id="perf-date-end" class="form-control form-control-sm d-inline-block" style="max-width: 130px;" title="To">
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="telecaller-stats-container">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Allocated (75% No Response Yet + 25% Average Response Time) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-none">
                    <h5 class="mb-0">Leads Allocated</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-9">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <h6 class="mb-0 fw-semibold" style="font-size: 1rem;">Leads Allocated</h6>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <select id="leads-allocated-date-range" class="form-select form-select-sm" style="max-width: 160px;" title="Date range">
                                        <option value="today">Today</option>
                                        <option value="yesterday">Yesterday</option>
                                        <option value="this_week">This Week</option>
                                        <option value="this_month" selected>This Month</option>
                                        <option value="this_year">This Year</option>
                                        <option value="all_time">All Time</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                    <span id="leads-allocated-custom-date-wrap" class="d-none align-middle">
                                        <input type="date" id="leads-allocated-date-start" class="form-control form-control-sm d-inline-block" style="max-width: 130px;" title="From">
                                        <span class="mx-1">–</span>
                                        <input type="date" id="leads-allocated-date-end" class="form-control form-control-sm d-inline-block" style="max-width: 130px;" title="To">
                                    </span>
                                </div>
                            </div>
                            <div class="table-responsive crm-perf-table-wrap">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;"></th>
                                            <th>User Name</th>
                                            <th class="text-center">Pending Count</th>
                                            <th>Oldest Assign</th>
                                        </tr>
                                    </thead>
                                    <tbody id="leads-pending-response-tbody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-lg-3">
                            <h6 class="mb-2 fw-semibold" style="font-size: 1rem;">Average Response</h6>
                            <div id="average-response-time-panel" style="min-height: 60px;">
                                <p class="text-muted small mb-0">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger zone: Delete All Leads -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-danger fw-semibold">Danger Zone</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Permanently delete every lead in the system. This cannot be undone.</p>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnDeleteAllLeads" data-bs-toggle="modal" data-bs-target="#modalDeleteAllLeads">
                        Delete All Leads
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('crm.modals.user-management')
@include('crm.modals.transfer-leads')

<!-- Delete All Leads modal -->
<div class="modal fade" id="modalDeleteAllLeads" tabindex="-1" aria-labelledby="modalDeleteAllLeadsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger" id="modalDeleteAllLeadsLabel">Delete All Leads</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">This will permanently delete all leads. This cannot be undone.</p>
                <div class="mb-3">
                    <label for="deleteAllLeadsPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="deleteAllLeadsPassword" placeholder="Enter password" autocomplete="off">
                    <div id="deleteAllLeadsError" class="invalid-feedback"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeleteAllLeads">Delete All Leads</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Bootstrap 5.3.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/crm-dashboard.js') }}"></script>
<script>
(function() {
    var deleteAllLeadsUrl = '{{ route("crm.danger.delete-all-leads") }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('btnConfirmDeleteAllLeads').addEventListener('click', function() {
        var input = document.getElementById('deleteAllLeadsPassword');
        var errorEl = document.getElementById('deleteAllLeadsError');
        var btn = this;
        var password = (input && input.value) ? input.value.trim() : '';
        if (!password) {
            input.classList.add('is-invalid');
            errorEl.textContent = 'Please enter the password.';
            return;
        }
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
        btn.disabled = true;
        btn.textContent = 'Deleting...';

        fetch(deleteAllLeadsUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ password: password })
        }).then(function(res) {
            return res.json().then(function(data) {
                return { ok: res.ok, status: res.status, data: data };
            }).catch(function() {
                return { ok: res.ok, status: res.status, data: { message: 'Invalid response.' } };
            });
        }).then(function(result) {
            btn.disabled = false;
            btn.textContent = 'Delete All Leads';
            if (result.ok && result.data && result.data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalDeleteAllLeads'));
                if (modal) modal.hide();
                input.value = '';
                var msg = result.data.message || 'All leads deleted.';
                if (result.data.deleted_count !== undefined) {
                    msg += ' (' + result.data.deleted_count + ' deleted)';
                }
                var alertEl = document.getElementById('notification-alert');
                if (alertEl) {
                    alertEl.classList.remove('d-none', 'alert-danger');
                    alertEl.classList.add('alert-success');
                    document.getElementById('notification-message').textContent = msg;
                }
                setTimeout(function() { window.location.reload(); }, 1500);
            } else {
                input.classList.add('is-invalid');
                errorEl.textContent = (result.data && result.data.message) ? result.data.message : 'Invalid password or action not allowed.';
            }
        }).catch(function(err) {
            btn.disabled = false;
            btn.textContent = 'Delete All Leads';
            input.classList.add('is-invalid');
            errorEl.textContent = 'Request failed. Try again.';
        });
    });

    document.getElementById('modalDeleteAllLeads').addEventListener('show.bs.modal', function() {
        document.getElementById('deleteAllLeadsPassword').value = '';
        document.getElementById('deleteAllLeadsPassword').classList.remove('is-invalid');
        document.getElementById('deleteAllLeadsError').textContent = '';
    });
})();
</script>
@endpush

@endsection

