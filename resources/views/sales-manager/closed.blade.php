@extends('sales-manager.layout')

@section('title', 'Closed Leads - Senior Manager')
@section('page-title', 'Closed')

@push('styles')
<style>
    .closed-page-shell {
        display: grid;
        gap: 20px;
    }
    .closed-hero {
        background: linear-gradient(135deg, #0b4d2b 0%, #17613e 52%, #1f7a50 100%);
        border-radius: 28px;
        padding: 28px;
        color: #fff;
        box-shadow: 0 20px 50px rgba(6, 58, 28, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .closed-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }
    .closed-hero-title {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        margin-bottom: 8px;
    }
    .closed-hero-copy {
        max-width: 640px;
        color: rgba(232, 245, 236, 0.92);
        font-size: 1rem;
        line-height: 1.6;
    }
    .closed-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        color: #f5fff7;
        font-size: 0.86rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .closed-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 22px;
    }
    .closed-stat {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 20px;
        padding: 18px;
        backdrop-filter: blur(12px);
    }
    .closed-stat-label {
        color: rgba(231, 245, 235, 0.9);
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .closed-stat-value {
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .closed-filter-bar {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) repeat(2, minmax(170px, 0.7fr));
        gap: 14px;
        padding: 20px;
        background: linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(248,248,245,0.96) 100%);
        border-radius: 24px;
        border: 1px solid #e6e2d9;
        box-shadow: 0 16px 36px rgba(16, 24, 20, 0.06);
    }
    .closed-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .closed-field label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #325243;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .closed-input,
    .closed-select {
        width: 100%;
        min-height: 52px;
        border-radius: 16px;
        border: 1px solid #d9d4c9;
        background: #fff;
        padding: 0 16px;
        font-size: 0.98rem;
        font-family: inherit;
        color: #163124;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .closed-input:focus,
    .closed-select:focus {
        outline: none;
        border-color: #1f7a50;
        box-shadow: 0 0 0 4px rgba(31, 122, 80, 0.12);
    }
    .closed-results-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .closed-results-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #163124;
    }
    .closed-results-copy {
        font-size: 0.92rem;
        color: #667d70;
    }
    #closedLoadingState,
    #closedEmptyState {
        padding: 48px 24px;
        text-align: center;
        border-radius: 24px;
        background: rgba(255,255,255,0.94);
        border: 1px solid #e8e1d5;
        color: #557062;
    }
    #closedLeadsGrid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }
    .closed-card {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding: 20px;
        border-radius: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfb 100%);
        border: 1px solid #e6e2d9;
        box-shadow: 0 16px 38px rgba(16, 24, 20, 0.07);
        min-height: 100%;
    }
    .closed-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }
    .closed-card-name {
        font-size: 1.16rem;
        font-weight: 800;
        color: #173427;
        line-height: 1.3;
        margin-bottom: 6px;
    }
    .closed-card-sub {
        color: #6b7e73;
        font-size: 0.9rem;
    }
    .closed-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 0.8rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .closed-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .closed-meta-item {
        padding: 12px 14px;
        border-radius: 18px;
        background: #f7faf8;
        border: 1px solid #e7efe9;
    }
    .closed-meta-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #809286;
        margin-bottom: 6px;
    }
    .closed-meta-value {
        color: #1e372b;
        font-size: 0.93rem;
        font-weight: 600;
        line-height: 1.45;
        word-break: break-word;
    }
    .closed-requirements {
        padding: 14px 16px;
        border-radius: 18px;
        background: #f3f7f5;
        border: 1px dashed #d6e1da;
        color: #537062;
        font-size: 0.9rem;
        line-height: 1.55;
        min-height: 74px;
    }
    .closed-actions {
        margin-top: auto;
        display: flex;
        gap: 10px;
    }
    .closed-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        border-radius: 14px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 700;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .closed-btn-primary {
        background: linear-gradient(135deg, #0b4d2b 0%, #17613e 100%);
        color: #fff;
        box-shadow: 0 12px 24px rgba(11, 77, 43, 0.18);
    }
    .closed-btn-secondary {
        background: #fff;
        color: #1e372b;
        border: 1px solid #d5ddd8;
    }
    .closed-btn:hover {
        transform: translateY(-1px);
    }
    .closed-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .closed-pagination-controls {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .closed-page-btn {
        min-width: 42px;
        min-height: 42px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid #d6d0c5;
        background: #fff;
        color: #1a3528;
        font-weight: 700;
        cursor: pointer;
    }
    .closed-page-btn.active {
        background: linear-gradient(135deg, #0b4d2b 0%, #17613e 100%);
        color: #fff;
        border-color: transparent;
    }
    .closed-page-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .closed-stage {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .closed-stage.requested { background: #dbeafe; color: #1d4ed8; }
    .closed-stage.verified { background: #dcfce7; color: #166534; }
    .closed-stage.pending { background: #fef3c7; color: #92400e; }
    .closed-stage.rejected { background: #fee2e2; color: #991b1b; }
    .closed-stage.submitted { background: #ede9fe; color: #6d28d9; }
    .closed-btn-warning {
        background: linear-gradient(135deg, #b45309 0%, #d97706 100%);
        color: #fff;
    }
    .closed-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .closed-form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .closed-form-field.full {
        grid-column: 1 / -1;
    }
    .closed-form-field label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #284538;
    }
    .closed-textarea,
    .closed-file,
    .closed-number {
        width: 100%;
        border-radius: 14px;
        border: 1px solid #d9d4c9;
        background: #fff;
        padding: 12px 14px;
        font-size: 0.96rem;
        font-family: inherit;
    }
    .closed-textarea {
        min-height: 120px;
        resize: vertical;
    }
    .closed-modal-content {
        width: min(780px, 92vw);
        max-height: 88vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 28px 60px rgba(10, 20, 12, 0.22);
    }
    .closed-modal-head {
        padding: 22px 24px;
        background: linear-gradient(135deg, #effaf3 0%, #f7fbf8 100%);
        border-bottom: 1px solid #ebf1ec;
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 16px;
    }
    .closed-modal-body {
        padding: 22px 24px 26px;
        display: grid;
        gap: 18px;
    }
    .closed-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .closed-modal-note {
        padding: 14px 16px;
        border-radius: 16px;
        background: #f6fbf7;
        border: 1px dashed #d8e6dc;
        color: #4f6a5d;
        line-height: 1.55;
    }
    @media (max-width: 1100px) {
        #closedLeadsGrid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .closed-hero {
            padding: 22px 18px;
            border-radius: 24px;
        }
        .closed-hero-title {
            font-size: 1.7rem;
        }
        .closed-stats,
        #closedLeadsGrid,
        .closed-meta,
        .closed-filter-bar {
            grid-template-columns: 1fr;
        }
        .closed-actions,
        .closed-pagination,
        .closed-modal-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .closed-form-grid {
            grid-template-columns: 1fr;
        }
        .closed-pagination-controls {
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="closed-page-shell">
    <section class="closed-hero">
        <div class="closed-hero-top">
            <div>
                <div class="closed-hero-title">Closed Leads</div>
                <p class="closed-hero-copy">ASM ke verified closed leads yahin track honge. Is section se closed conversions ko quickly review karke lead detail page tak ja sakte ho.</p>
            </div>
            <div class="closed-pill">
                <i class="fas fa-circle-check"></i>
                Closed Pipeline
            </div>
        </div>
        <div class="closed-stats">
            <div class="closed-stat">
                <div class="closed-stat-label">Total Closed</div>
                <div class="closed-stat-value" id="closedTotalCount">0</div>
            </div>
            <div class="closed-stat">
                <div class="closed-stat-label">This Page</div>
                <div class="closed-stat-value" id="closedPageCount">0</div>
            </div>
            <div class="closed-stat">
                <div class="closed-stat-label">Last Refresh</div>
                <div class="closed-stat-value" id="closedLastRefresh">-</div>
            </div>
        </div>
    </section>

    <section class="closed-filter-bar">
        <div class="closed-field">
            <label for="closedSearchInput">Search Closed Lead</label>
            <input id="closedSearchInput" class="closed-input" type="text" placeholder="Name, phone, email, location..." />
        </div>
        <div class="closed-field">
            <label for="closedSourceFilter">Source</label>
            <select id="closedSourceFilter" class="closed-select">
                <option value="">All Sources</option>
                <option value="website">Website</option>
                <option value="referral">Referral</option>
                <option value="walk_in">Walk In</option>
                <option value="call">Call</option>
                <option value="social_media">Social Media</option>
                <option value="google_sheets">Google Sheets</option>
                <option value="csv">CSV</option>
                <option value="pabbly">Pabbly</option>
                <option value="facebook_lead_ads">Facebook Lead Ads</option>
                <option value="mcube">Mcube</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="closed-field">
            <label for="closedSortFilter">Sort</label>
            <select id="closedSortFilter" class="closed-select">
                <option value="latest">Latest Updated</option>
                <option value="oldest">Oldest Updated</option>
                <option value="name_asc">Name A-Z</option>
                <option value="name_desc">Name Z-A</option>
            </select>
        </div>
    </section>

    <section class="closed-results-head">
        <div>
            <div class="closed-results-title">Closed Lead Records</div>
            <div class="closed-results-copy">Closed leads list meetings aur visits ki tarah dedicated section me available hai.</div>
        </div>
    </section>

    <div id="closedLoadingState">Closed leads load ho rahi hain...</div>
    <div id="closedEmptyState" hidden>Abhi koi closed lead ya close request nahi mila.</div>
    <div id="closedLeadsGrid" hidden></div>
    <div id="closedPagination" class="closed-pagination" hidden></div>
</div>

<div id="closedKycModal" class="modal">
    <div class="closed-modal-content">
        <div class="closed-modal-head">
            <div>
                <h3 style="font-size:1.45rem;font-weight:800;color:#133025;">Fill KYC Form</h3>
                <p style="margin-top:6px;color:#60786b;">Close verification ke baad full KYC yahin se submit karo.</p>
            </div>
            <button type="button" class="btn btn-danger" onclick="closeClosedKycModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="closed-modal-body">
            <div class="closed-modal-note">Customer, nominee, identity details, KYC documents, aur proof photos required hain. Submit hone ke baad incentive form unlock hoga.</div>
            <form id="closedKycForm" class="closed-form-grid">
                <div class="closed-form-field">
                    <label for="closedCustomerName">Customer Name</label>
                    <input id="closedCustomerName" class="closed-input" type="text" required>
                </div>
                <div class="closed-form-field">
                    <label for="closedNomineeName">Nominee Name</label>
                    <input id="closedNomineeName" class="closed-input" type="text" required>
                </div>
                <div class="closed-form-field">
                    <label for="closedSecondCustomerName">Second Customer Name</label>
                    <input id="closedSecondCustomerName" class="closed-input" type="text">
                </div>
                <div class="closed-form-field">
                    <label for="closedCustomerDob">Customer DOB</label>
                    <input id="closedCustomerDob" class="closed-input" type="date" required>
                </div>
                <div class="closed-form-field">
                    <label for="closedPanCard">PAN Card</label>
                    <input id="closedPanCard" class="closed-input" type="text" required>
                </div>
                <div class="closed-form-field">
                    <label for="closedAadhaarCard">Aadhaar No</label>
                    <input id="closedAadhaarCard" class="closed-input" type="text" required>
                </div>
                <div class="closed-form-field full">
                    <label for="closedKycDocs">KYC Documents</label>
                    <input id="closedKycDocs" class="closed-file" type="file" accept=".jpg,.jpeg,.png,.pdf" multiple required>
                </div>
                <div class="closed-form-field full">
                    <label for="closedProofPhotos">Proof Photos</label>
                    <input id="closedProofPhotos" class="closed-file" type="file" accept=".jpg,.jpeg,.png,.webp" multiple required>
                </div>
            </form>
            <div class="closed-modal-actions">
                <button type="button" class="closed-btn closed-btn-secondary" onclick="closeClosedKycModal()">Cancel</button>
                <button type="button" class="closed-btn closed-btn-primary" onclick="submitClosedKyc()">Submit KYC</button>
            </div>
        </div>
    </div>
</div>

<div id="closedIncentiveModal" class="modal">
    <div class="closed-modal-content">
        <div class="closed-modal-head">
            <div>
                <h3 style="font-size:1.45rem;font-weight:800;color:#133025;">Submit Incentive Request</h3>
                <p style="margin-top:6px;color:#60786b;">KYC submit hone ke baad closer incentive yahin request hoga.</p>
            </div>
            <button type="button" class="btn btn-danger" onclick="closeClosedIncentiveModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="closed-modal-body">
            <div class="closed-modal-note">Incentive request CRM/Admin verification queue me jayega.</div>
            <div class="closed-form-grid">
                <div class="closed-form-field">
                    <label for="closedIncentiveAmount">Incentive Amount</label>
                    <input id="closedIncentiveAmount" class="closed-number" type="number" min="0" step="0.01" required>
                </div>
            </div>
            <div class="closed-modal-actions">
                <button type="button" class="closed-btn closed-btn-secondary" onclick="closeClosedIncentiveModal()">Cancel</button>
                <button type="button" class="closed-btn closed-btn-primary" onclick="submitClosedIncentive()">Submit Incentive</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const CLOSED_API_BASE_URL = '/api';
    let closedLeadPage = 1;
    let closedVisitData = [];
    let closedVisitMeta = null;
    let activeClosedVisitId = null;

    function getClosedAuthHeaders() {
        const token = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || @json($api_token ?? '');
        return {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        };
    }

    function debounce(fn, delay = 300) {
        let timeoutId;
        return (...args) => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn(...args), delay);
        };
    }

    function formatClosedDate(value, withTime = false) {
        if (!value) return 'N/A';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return 'N/A';
        return date.toLocaleString('en-IN', withTime ? {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        } : {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function updateClosedStats(data) {
        document.getElementById('closedTotalCount').textContent = data.total ?? 0;
        document.getElementById('closedPageCount').textContent = data.data?.length ?? 0;
        document.getElementById('closedLastRefresh').textContent = new Date().toLocaleTimeString('en-IN', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function applyClosedClientFilters(visits) {
        const source = document.getElementById('closedSourceFilter').value;
        const sort = document.getElementById('closedSortFilter').value;
        let filtered = Array.isArray(visits) ? [...visits] : [];

        if (source) {
            filtered = filtered.filter((visit) => (visit.lead?.source || '') === source);
        }

        filtered.sort((a, b) => {
            switch (sort) {
                case 'oldest':
                    return new Date(a.updated_at || a.created_at || 0) - new Date(b.updated_at || b.created_at || 0);
                case 'name_asc':
                    return (a.lead?.name || a.customer_name || '').localeCompare(b.lead?.name || b.customer_name || '');
                case 'name_desc':
                    return (b.lead?.name || b.customer_name || '').localeCompare(a.lead?.name || a.customer_name || '');
                case 'latest':
                default:
                    return new Date(b.updated_at || b.created_at || 0) - new Date(a.updated_at || a.created_at || 0);
            }
        });

        return filtered;
    }

    function getClosedStage(visit) {
        if (visit.incentive?.status === 'verified') return { key: 'verified', label: 'Incentive Verified' };
        if (visit.incentive?.status === 'pending_finance_manager') return { key: 'submitted', label: 'Incentive Submitted' };
        if (visit.kyc_documents_count > 0) return { key: 'pending', label: 'Incentive Pending' };
        if (visit.closing_verification_status === 'verified') return { key: 'verified', label: 'KYC Pending' };
        if (visit.closing_verification_status === 'rejected') return { key: 'rejected', label: 'Close Rejected' };
        return { key: 'requested', label: 'Close Requested' };
    }

    function createClosedLeadCard(visit) {
        const lead = visit.lead || {};
        const stage = getClosedStage(visit);
        const canFillKyc = visit.closing_verification_status === 'verified' && !(visit.kyc_documents_count > 0);
        const canFillIncentive = visit.closing_verification_status === 'verified' && visit.kyc_documents_count > 0 && !visit.incentive;
        const wrapper = document.createElement('article');
        wrapper.className = 'closed-card';

        wrapper.innerHTML = `
            <div class="closed-card-top">
                <div>
                    <div class="closed-card-name">${lead.name || visit.customer_name || 'N/A'}</div>
                    <div class="closed-card-sub">${lead.phone || visit.phone || 'No phone'}${lead.email ? ` • ${lead.email}` : ''}</div>
                </div>
                <div class="closed-stage ${stage.key}">
                    <i class="fas fa-circle-check"></i>
                    ${stage.label}
                </div>
            </div>
            <div class="closed-meta">
                <div class="closed-meta-item">
                    <span class="closed-meta-label">Location</span>
                    <span class="closed-meta-value">${lead.preferred_location || visit.property_address || 'N/A'}</span>
                </div>
                <div class="closed-meta-item">
                    <span class="closed-meta-label">Budget</span>
                    <span class="closed-meta-value">${lead.budget || visit.budget_range || 'N/A'}</span>
                </div>
                <div class="closed-meta-item">
                    <span class="closed-meta-label">Project</span>
                    <span class="closed-meta-value">${visit.project || visit.property_name || 'N/A'}</span>
                </div>
                <div class="closed-meta-item">
                    <span class="closed-meta-label">Updated</span>
                    <span class="closed-meta-value">${formatClosedDate(visit.updated_at || visit.created_at, true)}</span>
                </div>
            </div>
            <div class="closed-requirements">${visit.closing_rejection_reason || lead.requirements || visit.visit_notes || lead.notes || 'No additional closing notes available.'}</div>
            <div class="closed-actions">
                ${canFillKyc ? `<button type="button" class="closed-btn closed-btn-primary" onclick="openClosedKycModal(${visit.id})"><i class="fas fa-id-card"></i>Fill KYC Form</button>` : ''}
                ${canFillIncentive ? `<button type="button" class="closed-btn closed-btn-warning" onclick="openClosedIncentiveModal(${visit.id})"><i class="fas fa-wallet"></i>Fill Incentive Form</button>` : ''}
                <a href="/leads/${lead.id}" class="closed-btn closed-btn-primary"><i class="fas fa-eye"></i>View Detail</a>
                <a href="/leads/${lead.id}#timeline" class="closed-btn closed-btn-secondary"><i class="fas fa-clock-rotate-left"></i>Activity</a>
            </div>
        `;

        return wrapper;
    }

    function renderClosedLeads(visits) {
        const grid = document.getElementById('closedLeadsGrid');
        const empty = document.getElementById('closedEmptyState');
        grid.innerHTML = '';

        if (!visits.length) {
            grid.hidden = true;
            empty.hidden = false;
            return;
        }

        visits.forEach((visit) => grid.appendChild(createClosedLeadCard(visit)));
        empty.hidden = true;
        grid.hidden = false;
    }

    function renderClosedPagination(meta) {
        const pagination = document.getElementById('closedPagination');
        if (!meta || (meta.last_page || 1) <= 1) {
            pagination.hidden = true;
            pagination.innerHTML = '';
            return;
        }

        let controls = '';
        const currentPage = meta.current_page || 1;
        const lastPage = meta.last_page || 1;
        controls += `<button class="closed-page-btn" ${currentPage <= 1 ? 'disabled' : ''} onclick="loadClosedLeads(${currentPage - 1})">Prev</button>`;
        for (let i = 1; i <= lastPage; i++) {
            if (i === 1 || i === lastPage || (i >= currentPage - 2 && i <= currentPage + 2)) {
                controls += `<button class="closed-page-btn ${i === currentPage ? 'active' : ''}" onclick="loadClosedLeads(${i})">${i}</button>`;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                controls += `<button class="closed-page-btn" disabled>...</button>`;
            }
        }
        controls += `<button class="closed-page-btn" ${currentPage >= lastPage ? 'disabled' : ''} onclick="loadClosedLeads(${currentPage + 1})">Next</button>`;

        pagination.innerHTML = `<div class="closed-pagination-controls">${controls}</div><div class="closed-results-copy">Showing ${meta.from || 0} to ${meta.to || 0} of ${meta.total || 0} closed pipeline records</div>`;
        pagination.hidden = false;
    }

    function openClosedKycModal(siteVisitId) {
        activeClosedVisitId = siteVisitId;
        document.getElementById('closedKycForm').reset();
        document.getElementById('closedKycModal').classList.add('show');
    }

    function closeClosedKycModal() {
        document.getElementById('closedKycModal').classList.remove('show');
        activeClosedVisitId = null;
    }

    async function submitClosedKyc() {
        if (!activeClosedVisitId) return;
        const formData = new FormData();
        formData.append('customer_name', document.getElementById('closedCustomerName').value.trim());
        formData.append('nominee_name', document.getElementById('closedNomineeName').value.trim());
        formData.append('second_customer_name', document.getElementById('closedSecondCustomerName').value.trim());
        formData.append('customer_dob', document.getElementById('closedCustomerDob').value);
        formData.append('pan_card', document.getElementById('closedPanCard').value.trim().toUpperCase());
        formData.append('aadhaar_card_no', document.getElementById('closedAadhaarCard').value.trim());

        const kycDocs = document.getElementById('closedKycDocs').files;
        const proofPhotos = document.getElementById('closedProofPhotos').files;
        if (!kycDocs.length || !proofPhotos.length) {
            alert('Please upload KYC documents and proof photos.');
            return;
        }
        for (let i = 0; i < kycDocs.length; i++) formData.append('kyc_documents[]', kycDocs[i]);
        for (let i = 0; i < proofPhotos.length; i++) formData.append('proof_photos[]', proofPhotos[i]);

        try {
            const response = await fetch(`${CLOSED_API_BASE_URL}/site-visits/${activeClosedVisitId}/submit-kyc`, {
                method: 'POST',
                headers: getClosedAuthHeaders(),
                body: formData,
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Failed to submit KYC');
            closeClosedKycModal();
            showNotification(result.message || 'KYC submitted successfully.', 'success', 3000);
            loadClosedLeads(closedLeadPage);
        } catch (error) {
            alert(error.message || 'Failed to submit KYC');
        }
    }

    function openClosedIncentiveModal(siteVisitId) {
        activeClosedVisitId = siteVisitId;
        document.getElementById('closedIncentiveAmount').value = '';
        document.getElementById('closedIncentiveModal').classList.add('show');
    }

    function closeClosedIncentiveModal() {
        document.getElementById('closedIncentiveModal').classList.remove('show');
        activeClosedVisitId = null;
    }

    async function submitClosedIncentive() {
        if (!activeClosedVisitId) return;
        const amount = parseFloat(document.getElementById('closedIncentiveAmount').value || '0');
        if (!amount || amount <= 0) {
            alert('Please enter a valid incentive amount.');
            return;
        }
        try {
            const response = await fetch(`${CLOSED_API_BASE_URL}/site-visits/${activeClosedVisitId}/request-incentive`, {
                method: 'POST',
                headers: {
                    ...getClosedAuthHeaders(),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ type: 'closer', amount }),
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Failed to submit incentive request');
            closeClosedIncentiveModal();
            showNotification(result.message || 'Incentive request submitted successfully.', 'success', 3000);
            loadClosedLeads(closedLeadPage);
        } catch (error) {
            alert(error.message || 'Failed to submit incentive request');
        }
    }

    async function loadClosedLeads(page = 1) {
        closedLeadPage = page;
        const loading = document.getElementById('closedLoadingState');
        const grid = document.getElementById('closedLeadsGrid');
        const pagination = document.getElementById('closedPagination');
        const search = document.getElementById('closedSearchInput').value.trim();

        loading.hidden = false;
        grid.hidden = true;
        pagination.hidden = true;

        try {
            const params = new URLSearchParams({
                page: String(page),
                per_page: '15',
                closed_pipeline: '1',
            });
            if (search) params.append('search', search);

            const response = await fetch(`${CLOSED_API_BASE_URL}/site-visits?${params.toString()}`, {
                headers: getClosedAuthHeaders(),
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Failed to load closed pipeline');

            const data = await response.json();
            const incentivesResponse = await fetch(`${CLOSED_API_BASE_URL}/incentives`, {
                headers: getClosedAuthHeaders(),
                credentials: 'same-origin'
            });
            const incentivesPayload = incentivesResponse.ok ? await incentivesResponse.json() : { data: [] };
            const incentivesByVisitId = new Map((incentivesPayload.data || []).map((item) => [Number(item.site_visit_id), item]));
            const decorated = (data.data || []).map((visit) => ({
                ...visit,
                kyc_documents_count: Array.isArray(visit.kyc_documents) ? visit.kyc_documents.length : 0,
                incentive: incentivesByVisitId.get(Number(visit.id)) || null,
            }));

            closedVisitMeta = { ...data, data: decorated };
            closedVisitData = applyClosedClientFilters(decorated);
            updateClosedStats({ ...data, data: decorated });
            renderClosedLeads(closedVisitData);
            renderClosedPagination(data);
        } catch (error) {
            console.error('Closed leads load error:', error);
            document.getElementById('closedEmptyState').hidden = false;
            document.getElementById('closedEmptyState').textContent = 'Closed pipeline load nahi ho payi. Please refresh and try again.';
        } finally {
            loading.hidden = true;
        }
    }

    const reloadClosedLeads = debounce(() => loadClosedLeads(1), 280);

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('closedSearchInput').addEventListener('input', reloadClosedLeads);
        document.getElementById('closedSourceFilter').addEventListener('change', () => {
            if (closedVisitMeta) {
                closedVisitData = applyClosedClientFilters(closedVisitMeta.data || []);
                renderClosedLeads(closedVisitData);
                renderClosedPagination(closedVisitMeta);
            } else {
                loadClosedLeads(1);
            }
        });
        document.getElementById('closedSortFilter').addEventListener('change', () => {
            if (closedVisitMeta) {
                closedVisitData = applyClosedClientFilters(closedVisitMeta.data || []);
                renderClosedLeads(closedVisitData);
            }
        });
        loadClosedLeads(1);
    });
</script>
@endpush
