@extends('sales-manager.layout')

@section('title', 'Download Leads - Assistant Sales Manager')
@section('page-title', 'Download Leads')

@push('styles')
<style>
    .download-shell { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(320px,.8fr); gap:24px; }
    .download-card { background:rgba(255,255,255,.94); border:1px solid #dbe6df; border-radius:24px; box-shadow:0 18px 48px rgba(16,24,20,.08); padding:24px; }
    .download-kicker { font-size:12px; text-transform:uppercase; letter-spacing:.16em; color:#5d7267; font-weight:700; margin-bottom:8px; }
    .download-title { font-size:28px; font-weight:700; color:#0c2b20; margin-bottom:10px; line-height:1.1; }
    .download-copy { color:#5f6f67; font-size:14px; line-height:1.7; }
    .download-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; margin-top:22px; }
    .download-field { display:flex; flex-direction:column; gap:8px; }
    .download-field.full { grid-column:1 / -1; }
    .download-field label, .download-checkbox-label { font-size:13px; font-weight:700; color:#0f2d22; }
    .download-field input, .download-field select { width:100%; border:1px solid #d4e0d8; border-radius:14px; padding:12px 14px; background:#fff; color:#173128; font-size:14px; }
    .download-segmented { display:flex; gap:10px; flex-wrap:wrap; }
    .download-segmented label { position:relative; cursor:pointer; }
    .download-segmented input { position:absolute; opacity:0; pointer-events:none; }
    .download-segmented span { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:999px; border:1px solid #d3e2da; background:#f7faf8; color:#385146; font-size:13px; font-weight:600; transition:all .2s ease; }
    .download-segmented input:checked + span { background:#0f5132; border-color:#0f5132; color:#fff; box-shadow:0 10px 20px rgba(15,81,50,.18); }
    .download-checkbox-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; padding:16px; border:1px solid #d4e0d8; border-radius:18px; background:#fbfdfc; }
    .download-multi-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; padding:16px; border:1px solid #d4e0d8; border-radius:18px; background:#fbfdfc; max-height:220px; overflow:auto; }
    .download-helper { font-size:12px; color:#6b7b73; margin-top:4px; }
    .download-checkbox-item { display:flex; align-items:center; gap:10px; color:#324840; font-size:13px; }
    .download-checkbox-item input { width:16px; height:16px; }
    .download-preview { margin-top:20px; background:linear-gradient(135deg,#0e2d22 0%,#184837 100%); color:#fff; border-radius:22px; padding:20px; }
    .download-preview h3 { font-size:16px; font-weight:700; margin-bottom:14px; }
    .download-preview-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .download-preview-item { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.1); border-radius:16px; padding:12px 14px; }
    .download-preview-item strong { display:block; font-size:11px; letter-spacing:.12em; text-transform:uppercase; color:rgba(255,255,255,.68); margin-bottom:6px; }
    .download-preview-item span { font-size:14px; line-height:1.5; }
    .download-actions { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-top:24px; }
    .download-btn { display:inline-flex; align-items:center; justify-content:center; gap:10px; border:none; border-radius:14px; padding:12px 18px; cursor:pointer; font-size:14px; font-weight:700; text-decoration:none; }
    .download-btn.primary { background:linear-gradient(135deg,#0f5132 0%,#1a6b47 100%); color:#fff; box-shadow:0 16px 32px rgba(15,81,50,.18); }
    .download-alert { padding:14px 16px; border-radius:16px; margin-bottom:18px; font-size:14px; }
    .download-alert.success { background:#eaf8ef; color:#166534; border:1px solid #ccebd6; }
    .download-alert.error { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; }
    .request-list { display:grid; gap:14px; margin-top:20px; }
    .request-item { border:1px solid #dce7e0; border-radius:18px; padding:16px; background:#fcfdfc; }
    .request-top { display:flex; justify-content:space-between; gap:12px; margin-bottom:14px; }
    .request-status { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
    .status-pending, .status-approved, .status-processing { background:#fff7ed; color:#b45309; }
    .status-completed { background:#ecfdf3; color:#15803d; }
    .status-rejected, .status-expired { background:#fff1f2; color:#be123c; }
    .request-meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; font-size:13px; color:#51645b; }
    .request-meta strong { display:block; color:#0f2d22; margin-bottom:4px; }
    .request-note { margin-top:12px; padding:12px 14px; border-radius:14px; background:#f6faf8; color:#3c5147; font-size:13px; line-height:1.6; white-space:pre-line; }
    @media (max-width:991px) { .download-shell { grid-template-columns:1fr; } }
    @media (max-width:767px) { .download-card { padding:18px; border-radius:20px; } .download-grid, .download-preview-list, .request-meta, .download-checkbox-grid { grid-template-columns:1fr; } .download-actions, .request-top { flex-direction:column; align-items:stretch; } }
</style>
@endpush

@section('content')
<div class="download-shell">
    <section class="download-card">
        @if(session('success')) <div class="download-alert success">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="download-alert error">{{ session('error') }}</div> @endif
        @if($errors->any()) <div class="download-alert error">{{ $errors->first() }}</div> @endif

        <div class="download-kicker">Approval Based Export</div>
        <h1 class="download-title">Request a lead download</h1>
        <p class="download-copy">Choose the format, scope, filters, and fields. Admin approval ke baad export portal aur mail dono par available ho jayega.</p>

        <form method="POST" action="{{ route('sales-manager.lead-downloads.store') }}" id="leadDownloadRequestForm">
            @csrf
            <div class="download-grid">
                <div class="download-field full">
                    <label>Export Format</label>
                    <div class="download-segmented">
                        <label><input type="radio" name="format" value="csv" {{ old('format', 'csv') === 'csv' ? 'checked' : '' }}><span><i class="fas fa-file-csv"></i> CSV</span></label>
                        <label><input type="radio" name="format" value="pdf" {{ old('format') === 'pdf' ? 'checked' : '' }}><span><i class="fas fa-file-pdf"></i> PDF</span></label>
                    </div>
                </div>

                <div class="download-field">
                    <label for="assigned_scope">Assigned Scope</label>
                    <select id="assigned_scope" name="assigned_scope">
                        <option value="my_team" {{ old('assigned_scope', 'my_team') === 'my_team' ? 'selected' : '' }}>Own + Team Leads</option>
                        <option value="own" {{ old('assigned_scope') === 'own' ? 'selected' : '' }}>Only My Leads</option>
                        <option value="specific_user" {{ old('assigned_scope') === 'specific_user' ? 'selected' : '' }}>Specific User</option>
                    </select>
                </div>

                <div class="download-field" id="specificUserField" style="{{ old('assigned_scope') === 'specific_user' ? '' : 'display:none;' }}">
                    <label for="user_id">Specific User</label>
                    <select id="user_id" name="user_id">
                        <option value="">Select user</option>
                        @foreach($availableUsers as $availableUser)
                            <option value="{{ $availableUser->id }}" {{ (string) old('user_id') === (string) $availableUser->id ? 'selected' : '' }}>{{ $availableUser->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="download-field">
                    <label for="date_range">Date Range</label>
                    <select id="date_range" name="date_range">
                        @foreach($dateRanges as $key => $label)
                            <option value="{{ $key }}" {{ old('date_range', 'all_time') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="download-field">
                    <label for="search">Search Keyword</label>
                    <input id="search" type="text" name="search" value="{{ old('search') }}" placeholder="Name, phone, or email">
                </div>

                <div class="download-field" id="fromDateField" style="{{ old('date_range') === 'custom' ? '' : 'display:none;' }}">
                    <label for="from_date">From Date</label>
                    <input id="from_date" type="date" name="from_date" value="{{ old('from_date') }}">
                </div>

                <div class="download-field" id="toDateField" style="{{ old('date_range') === 'custom' ? '' : 'display:none;' }}">
                    <label for="to_date">To Date</label>
                    <input id="to_date" type="date" name="to_date" value="{{ old('to_date') }}">
                </div>

                <div class="download-field full">
                    <label>Lead Status</label>
                    <div class="download-multi-grid">
                        @foreach($statuses as $status)
                            <label class="download-checkbox-item">
                                <input type="checkbox" name="status[]" value="{{ $status }}" @checked(collect(old('status', []))->contains($status))>
                                <span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="download-helper">Multiple statuses select kar sakte ho.</div>
                </div>

                <div class="download-field full">
                    <label>Lead Type</label>
                    <div class="download-multi-grid">
                        @foreach($leadTypes as $key => $label)
                            <label class="download-checkbox-item">
                                <input type="checkbox" name="lead_type[]" value="{{ $key }}" @checked(collect(old('lead_type', []))->contains($key))>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="download-helper">Ek se zyada lead type choose kar sakte ho.</div>
                </div>

                <div class="download-field full">
                    <label>Interested Projects</label>
                    <div class="download-multi-grid">
                        @foreach($interestedProjects as $project)
                            <label class="download-checkbox-item">
                                <input type="checkbox" name="interested_projects[]" value="{{ $project->id }}" @checked(collect(old('interested_projects', []))->contains($project->id))>
                                <span>{{ $project->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="download-helper">Project filters me bhi multiple selection rahega.</div>
                </div>

                <div class="download-field full">
                    <div class="download-checkbox-label">Fields to include</div>
                    <div class="download-checkbox-grid">
                        @foreach($fields as $key => $label)
                            <label class="download-checkbox-item">
                                <input type="checkbox" name="fields[]" value="{{ $key }}" @checked(collect(old('fields', ['name', 'phone', 'status', 'assigned_to', 'created_at']))->contains($key))>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="download-preview">
                <h3>Request Preview</h3>
                <div class="download-preview-list">
                    <div class="download-preview-item"><strong>Format</strong><span id="previewFormat">CSV</span></div>
                    <div class="download-preview-item"><strong>Scope</strong><span id="previewScope">Own + Team Leads</span></div>
                    <div class="download-preview-item"><strong>Filters</strong><span id="previewFilters">No filters applied</span></div>
                    <div class="download-preview-item"><strong>Fields</strong><span id="previewFields">5 selected</span></div>
                </div>
            </div>

            <div class="download-actions">
                <span class="download-copy">Admin review ke baad file mail aur portal dono me milega.</span>
                <button type="submit" class="download-btn primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
            </div>
        </form>
    </section>

    <aside class="download-card">
        <div class="download-kicker">Portal History</div>
        <h2 class="download-title" style="font-size:24px;">Recent requests</h2>
        <p class="download-copy">Pending, approved, rejected, aur completed exports yahin track honge.</p>
        <div class="request-list">
            @forelse($requests as $requestItem)
                <article class="request-item">
                    <div class="request-top">
                        <div>
                            <div style="font-size:16px;font-weight:700;color:#0f2d22;">{{ strtoupper($requestItem->format) }} lead export</div>
                            <div style="font-size:13px;color:#5e7267;margin-top:4px;">Requested {{ $requestItem->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                        <span class="request-status status-{{ $requestItem->status }}">{{ $requestItem->statusLabel() }}</span>
                    </div>
                    <div class="request-meta">
                        <div><strong>Scope</strong>{{ ucfirst(str_replace('_', ' ', $requestItem->filters['assigned_scope'] ?? 'my team')) }}</div>
                        <div><strong>Fields</strong>{{ count($requestItem->fields ?? []) }} selected</div>
                        <div><strong>Records</strong>{{ $requestItem->exported_records_count ?? 'Pending' }}</div>
                        <div><strong>Reviewed By</strong>{{ $requestItem->reviewer->name ?? 'Awaiting review' }}</div>
                    </div>
                    @if($requestItem->rejection_reason)<div class="request-note"><strong>Rejection Reason:</strong> {{ $requestItem->rejection_reason }}</div>@endif
                    @if($requestItem->admin_note)<div class="request-note"><strong>Admin Note:</strong> {{ $requestItem->admin_note }}</div>@endif
                    @if($requestItem->isDownloadReady())
                        <div style="margin-top:14px;"><a href="{{ route('sales-manager.lead-downloads.download', $requestItem) }}" class="download-btn primary"><i class="fas fa-download"></i> Download File</a></div>
                    @elseif($requestItem->status === \App\Models\LeadDownloadRequest::STATUS_APPROVED)
                        <div class="request-note"><strong>Approved:</strong> Download file abhi ready nahi hai. Admin generation retry ya complete hone par button yahin dikhega.</div>
                    @elseif($requestItem->expires_at && $requestItem->expires_at->isPast())
                        <div class="request-note"><strong>Expired:</strong> Please raise a fresh request to generate a new file.</div>
                    @endif
                </article>
            @empty
                <div class="request-item">
                    <div style="font-size:15px;font-weight:700;color:#173128;margin-bottom:6px;">No requests yet</div>
                    <div style="font-size:13px;color:#5f6f67;">Submit your first approval-based lead export from the form.</div>
                </div>
            @endforelse
        </div>
    </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('leadDownloadRequestForm');
    const scopeSelect = document.getElementById('assigned_scope');
    const specificUserField = document.getElementById('specificUserField');
    const dateRangeSelect = document.getElementById('date_range');
    const fromDateField = document.getElementById('fromDateField');
    const toDateField = document.getElementById('toDateField');
    const previewFormat = document.getElementById('previewFormat');
    const previewScope = document.getElementById('previewScope');
    const previewFilters = document.getElementById('previewFilters');
    const previewFields = document.getElementById('previewFields');

    function syncConditionalFields() {
        specificUserField.style.display = scopeSelect.value === 'specific_user' ? '' : 'none';
        fromDateField.style.display = dateRangeSelect.value === 'custom' ? '' : 'none';
        toDateField.style.display = dateRangeSelect.value === 'custom' ? '' : 'none';
    }

    function syncPreview() {
        const selectedFormat = form.querySelector('input[name="format"]:checked');
        const statuses = Array.from(form.querySelectorAll('input[name="status[]"]:checked')).map(option => option.parentElement.textContent.trim());
        const leadTypes = Array.from(form.querySelectorAll('input[name="lead_type[]"]:checked')).map(option => option.parentElement.textContent.trim());
        const projects = Array.from(form.querySelectorAll('input[name="interested_projects[]"]:checked')).map(option => option.parentElement.textContent.trim());
        const selectedFields = form.querySelectorAll('input[name="fields[]"]:checked').length;
        const activeFilters = [];
        previewFormat.textContent = selectedFormat ? selectedFormat.value.toUpperCase() : 'CSV';
        previewScope.textContent = scopeSelect.options[scopeSelect.selectedIndex].text;
        if (statuses.length) activeFilters.push(`${statuses.length} status`);
        if (leadTypes.length) activeFilters.push(`${leadTypes.length} types`);
        if (projects.length) activeFilters.push(`${projects.length} projects`);
        if ((form.querySelector('input[name="search"]').value || '').trim()) activeFilters.push('Search keyword');
        if (dateRangeSelect.value !== 'all_time') activeFilters.push(dateRangeSelect.options[dateRangeSelect.selectedIndex].text);
        previewFilters.textContent = activeFilters.length ? activeFilters.join(', ') : 'No filters applied';
        previewFields.textContent = `${selectedFields} selected`;
    }

    form.addEventListener('change', function () { syncConditionalFields(); syncPreview(); });
    form.addEventListener('input', syncPreview);
    syncConditionalFields();
    syncPreview();
});
</script>
@endsection
