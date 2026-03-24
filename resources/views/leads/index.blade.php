@php
    $user = auth()->user();
    if ($user && !$user->relationLoaded('role')) {
        $user->load('role');
    }
@endphp
@extends('layouts.app')

@section('title', 'All Leads - Base CRM')
@section('page-title', 'All Leads')

@section('header-actions')
<div style="display:flex;align-items:center;gap:10px;">
    {{-- View Toggle --}}
    <div class="lc-view-toggle">
        <button class="lc-toggle-btn active" id="btnCards" onclick="switchView('cards')" title="Card View">
            <i class="fas fa-th-large"></i>
        </button>
        <button class="lc-toggle-btn" id="btnList" onclick="switchView('list')" title="List View">
            <i class="fas fa-list"></i>
        </button>
    </div>
    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
    <a href="{{ route('leads.create') }}" class="lc-btn-add">
        <i class="fas fa-plus"></i> Add Lead
    </a>
    @endif
</div>
@endsection

@push('styles')
<style>
/* ── Variables ── */
:root {
    --lc-green-dark: #063A1C;
    --lc-green:      #205A44;
    --lc-green-light:#d1fae5;
    --lc-radius:     14px;
    --lc-shadow:     0 2px 8px rgba(0,0,0,.06);
    --lc-shadow-hover: 0 8px 24px rgba(0,0,0,.12);
}

/* ── Layout ── */
.lc-stats-grid {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:14px;
    margin-bottom:20px;
}
.lc-stat-card {
    background:#fff;
    border-radius:var(--lc-radius);
    padding:16px 20px;
    border:1px solid #e5e7eb;
    display:flex;
    align-items:center;
    gap:14px;
    box-shadow:var(--lc-shadow);
}
.lc-stat-icon {
    width:42px;height:42px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.lc-stat-val { font-size:24px;font-weight:700;color:#111827;line-height:1; }
.lc-stat-lbl { font-size:11px;color:#6b7280;margin-top:3px;font-weight:500; }

/* ── Toolbar ── */
.lc-toolbar {
    background:#fff;border-radius:var(--lc-radius);
    border:1px solid #e5e7eb;padding:12px 18px;
    margin-bottom:18px;display:flex;gap:10px;align-items:center;
    box-shadow:var(--lc-shadow);flex-wrap:wrap;
}
.lc-search-wrap { flex:1;min-width:180px;position:relative; }
.lc-search-wrap i { position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:13px; }
.lc-search-input {
    width:100%;padding:8px 11px 8px 33px;
    border:1.5px solid #e5e7eb;border-radius:9px;
    font-size:13px;color:#111827;outline:none;
    transition:.2s;background:#f9fafb;
}
.lc-search-input:focus { border-color:#205A44;background:#fff;box-shadow:0 0 0 3px rgba(32,90,68,.08); }
.lc-select {
    padding:8px 13px;border:1.5px solid #e5e7eb;border-radius:9px;
    font-size:13px;color:#374151;outline:none;background:#f9fafb;
    min-width:140px;cursor:pointer;transition:.2s;
}
.lc-select:focus { border-color:#205A44;background:#fff; }
.lc-btn-filter {
    padding:8px 20px;background:linear-gradient(135deg,#063A1C,#205A44);
    color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:600;
    cursor:pointer;display:flex;align-items:center;gap:6px;transition:.2s;white-space:nowrap;
}
.lc-btn-filter:hover { opacity:.9;transform:translateY(-1px); }
.lc-btn-clear {
    padding:8px 14px;background:#f3f4f6;color:#374151;border:none;
    border-radius:9px;font-size:13px;font-weight:500;cursor:pointer;
    text-decoration:none;display:flex;align-items:center;gap:5px;transition:.2s;
}
.lc-btn-clear:hover { background:#e5e7eb; }
.lc-btn-add {
    padding:8px 18px;background:linear-gradient(135deg,#063A1C,#205A44);
    color:#fff;border-radius:9px;font-size:13px;font-weight:600;
    text-decoration:none;display:flex;align-items:center;gap:6px;transition:.2s;
}
.lc-btn-add:hover { opacity:.9;transform:translateY(-1px); }

/* ── View Toggle ── */
.lc-view-toggle {
    display:flex;background:#f3f4f6;border-radius:9px;padding:3px;gap:3px;
}
.lc-toggle-btn {
    padding:6px 11px;border:none;border-radius:7px;font-size:13px;
    cursor:pointer;background:transparent;color:#6b7280;transition:all .2s;
}
.lc-toggle-btn.active { background:#fff;color:#063A1C;box-shadow:0 1px 4px rgba(0,0,0,.1); }

/* ── CARD VIEW ── */
.lc-cards-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
    gap:18px;
}
.lc-card {
    background:#fff;border-radius:16px;border:1px solid #e5e7eb;
    overflow:hidden;box-shadow:var(--lc-shadow);transition:all .25s;
}
.lc-card:hover { box-shadow:var(--lc-shadow-hover);transform:translateY(-3px);border-color:#c8e6c9; }
.lc-card-top {
    padding:16px 16px 12px;
    display:flex;align-items:flex-start;gap:12px;
    border-bottom:1px solid #f3f4f6;
}
.lc-avatar {
    width:44px;height:44px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:18px;font-weight:700;color:#fff;flex-shrink:0;
}
.lc-card-name { font-size:14px;font-weight:700;color:#111827;line-height:1.3;word-break:break-word; }
.lc-card-phone { font-size:12px;color:#6b7280;margin-top:2px; }
.lc-status-badge {
    margin-left:auto;flex-shrink:0;
    padding:3px 9px;border-radius:20px;
    font-size:10px;font-weight:700;
    text-transform:uppercase;letter-spacing:.4px;
    border:1.5px solid;
}
.lc-card-body { padding:12px 16px; }
.lc-info-row {
    display:flex;align-items:center;gap:7px;
    font-size:12px;color:#6b7280;margin-bottom:6px;
}
.lc-info-row i { width:13px;text-align:center;color:#9ca3af;flex-shrink:0; }
.lc-info-row span { color:#374151; }
.lc-source-pill {
    display:inline-flex;align-items:center;gap:4px;
    padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;
    background:#f0fdf4;color:#065f46;border:1px solid #bbf7d0;
}
.lc-card-footer {
    border-top:1px solid #f3f4f6;padding:10px 12px;
    display:flex;gap:7px;background:#fafafa;
}
.lc-action {
    flex:1;padding:7px 5px;border-radius:8px;font-size:12px;font-weight:600;
    border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;
    gap:4px;text-decoration:none;transition:.2s;
}
.lc-action:hover { opacity:.88;transform:translateY(-1px); }
.lc-action-view  { background:linear-gradient(135deg,#063A1C,#205A44);color:#fff; }
.lc-action-short { background:#f0fdf4;color:#065f46;border:1.5px solid #bbf7d0 !important; }
.lc-action-del   { background:#fff1f2;color:#be123c;border:1.5px solid #fecdd3 !important; }

/* ── LIST VIEW ── */
.lc-list-wrap {
    display:none;
    background:#fff;border-radius:var(--lc-radius);
    border:1px solid #e5e7eb;box-shadow:var(--lc-shadow);
    overflow:hidden;
}
.lc-list-wrap { display:none; }
.lc-list-wrap.active { display:block !important; }
.lc-cards-grid { display:none; }
.lc-cards-grid.active { display:grid !important; }
.lc-table { width:100%;border-collapse:collapse; }
.lc-table thead tr { background:#f9fafb;border-bottom:2px solid #e5e7eb; }
.lc-table th {
    padding:11px 14px;text-align:left;font-size:11px;
    font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;
    white-space:nowrap;
}
.lc-table th input[type=checkbox] { cursor:pointer; }
.lc-table tbody tr {
    border-bottom:1px solid #f3f4f6;transition:background .15s;
}
.lc-table tbody tr:hover { background:#f9fafb; }
.lc-table tbody tr:last-child { border-bottom:none; }
.lc-table td { padding:11px 14px;font-size:13px;color:#374151;vertical-align:middle; }
.lc-list-avatar {
    width:32px;height:32px;border-radius:50%;
    display:inline-flex;align-items:center;justify-content:center;
    font-size:13px;font-weight:700;color:#fff;flex-shrink:0;
}
.lc-list-name { font-weight:600;color:#111827;font-size:13px; }
.lc-list-phone { font-size:11px;color:#9ca3af;margin-top:1px; }
.lc-list-actions { display:flex;gap:6px;align-items:center; }
.lc-list-btn {
    padding:5px 10px;border-radius:7px;font-size:11px;font-weight:600;
    border:none;cursor:pointer;text-decoration:none;display:inline-flex;
    align-items:center;gap:4px;transition:.2s;white-space:nowrap;
}
.lc-list-btn:hover { opacity:.85;transform:translateY(-1px); }
.lc-list-btn-view  { background:linear-gradient(135deg,#063A1C,#205A44);color:#fff; }
.lc-list-btn-short { background:#f0fdf4;color:#065f46;border:1.5px solid #bbf7d0 !important; }
.lc-list-btn-del   { background:#fff1f2;color:#be123c;border:1.5px solid #fecdd3 !important; }

/* ── Status Colors ── */
.s-new             { background:#eff6ff;color:#1d4ed8;border-color:#93c5fd; }
.s-connected       { background:#f0fdf4;color:#15803d;border-color:#86efac; }
.s-verified_prospect { background:#fefce8;color:#a16207;border-color:#fde047; }
.s-meeting_scheduled,.s-meeting_completed { background:#fdf4ff;color:#7e22ce;border-color:#d8b4fe; }
.s-visit_scheduled,.s-visit_done,.s-revisited_scheduled,.s-revisited_completed { background:#fff7ed;color:#c2410c;border-color:#fdba74; }
.s-closed          { background:#f0fdf4;color:#065f46;border-color:#6ee7b7; }
.s-dead            { background:#fef2f2;color:#991b1b;border-color:#fca5a5; }
.s-on_hold         { background:#f8fafc;color:#475569;border-color:#cbd5e1; }

/* ── Empty ── */
.lc-empty {
    grid-column:1/-1;background:#fff;border-radius:16px;
    border:1px solid #e5e7eb;padding:60px 20px;text-align:center;
    box-shadow:var(--lc-shadow);
}

/* ── Pagination ── */
.lc-pagination {
    margin-top:18px;background:#fff;border-radius:12px;
    border:1px solid #e5e7eb;padding:11px 18px;
    box-shadow:0 1px 4px rgba(0,0,0,.04);
}

/* ── Bulk bar ── */
.lc-bulk-bar {
    display:none;background:#063A1C;color:#fff;
    border-radius:10px;padding:10px 18px;margin-bottom:14px;
    align-items:center;gap:12px;font-size:13px;
}
.lc-bulk-bar.show { display:flex; }
.lc-bulk-btn {
    padding:6px 14px;border-radius:7px;font-size:12px;font-weight:600;
    border:none;cursor:pointer;transition:.2s;
}

@media(max-width:768px){
    .lc-stats-grid { grid-template-columns:repeat(2,1fr); }
    .lc-toolbar { flex-direction:column;align-items:stretch; }
    .lc-select,.lc-btn-filter { width:100%; }
    .lc-table th:nth-child(4),
    .lc-table td:nth-child(4),
    .lc-table th:nth-child(5),
    .lc-table td:nth-child(5) { display:none; }
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
@endpush

@section('content')
@php
    $statusLabels = [
        'new'                  => 'New',
        'connected'            => 'Connected',
        'verified_prospect'    => 'Prospect',
        'meeting_scheduled'    => 'Mtg Scheduled',
        'meeting_completed'    => 'Mtg Done',
        'visit_scheduled'      => 'Visit Scheduled',
        'visit_done'           => 'Visit Done',
        'revisited_scheduled'  => 'Revisit Sched.',
        'revisited_completed'  => 'Revisit Done',
        'closed'               => 'Closed',
        'dead'                 => 'Dead',
        'on_hold'              => 'On Hold',
    ];
    $sourceLabels = [
        'website'          => 'Website',
        'referral'         => 'Referral',
        'walk_in'          => 'Walk In',
        'call'             => 'Call',
        'social_media'     => 'Social',
        'google_sheets'    => 'Google Sheets',
        'csv'              => 'CSV',
        'pabbly'           => 'Pabbly',
        'facebook_lead_ads'=> 'Facebook',
        'mcube'            => 'MCube',
        'other'            => 'Other',
    ];
    $avatarColors = ['#063A1C','#205A44','#065f46','#1d4ed8','#7c3aed','#be185d','#c2410c','#b45309'];
@endphp

{{-- Flash --}}
@if(session('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:11px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:9px;font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:11px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:9px;font-size:13px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- Stats --}}
<div class="lc-stats-grid">
    <div class="lc-stat-card">
        <div class="lc-stat-icon" style="background:linear-gradient(135deg,#063A1C,#205A44);">
            <i class="fas fa-users" style="color:#fff;font-size:16px;"></i>
        </div>
        <div>
            <div class="lc-stat-val">{{ $leads->total() }}</div>
            <div class="lc-stat-lbl">Total Leads</div>
        </div>
    </div>
    <div class="lc-stat-card">
        <div class="lc-stat-icon" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);">
            <i class="fas fa-user-plus" style="color:#fff;font-size:16px;"></i>
        </div>
        <div>
            <div class="lc-stat-val" style="color:#1d4ed8;">{{ $leads->getCollection()->where('status','new')->count() }}</div>
            <div class="lc-stat-lbl">New (this page)</div>
        </div>
    </div>
    <div class="lc-stat-card">
        <div class="lc-stat-icon" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);">
            <i class="fas fa-calendar-check" style="color:#fff;font-size:16px;"></i>
        </div>
        <div>
            <div class="lc-stat-val" style="color:#7c3aed;">{{ $leads->getCollection()->whereIn('status',['meeting_scheduled','meeting_completed','visit_scheduled','visit_done'])->count() }}</div>
            <div class="lc-stat-lbl">In Pipeline</div>
        </div>
    </div>
    <div class="lc-stat-card">
        <div class="lc-stat-icon" style="background:linear-gradient(135deg,#065f46,#10b981);">
            <i class="fas fa-check-circle" style="color:#fff;font-size:16px;"></i>
        </div>
        <div>
            <div class="lc-stat-val" style="color:#065f46;">{{ $leads->getCollection()->where('status','closed')->count() }}</div>
            <div class="lc-stat-lbl">Closed</div>
        </div>
    </div>
</div>

{{-- Bulk Action Bar --}}
<div class="lc-bulk-bar" id="bulkBar">
    <i class="fas fa-check-square"></i>
    <span id="bulkCount">0</span> leads selected
    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
    <button class="lc-bulk-btn" style="background:#fff;color:#063A1C;" onclick="bulkAssign()">
        <i class="fas fa-user-plus"></i> Assign
    </button>
    <button class="lc-bulk-btn" style="background:#ef4444;color:#fff;" onclick="bulkDelete()">
        <i class="fas fa-trash"></i> Delete
    </button>
    @endif
    <button class="lc-bulk-btn" style="background:rgba(255,255,255,.2);color:#fff;margin-left:auto;" onclick="clearSelection()">
        <i class="fas fa-times"></i> Clear
    </button>
</div>

{{-- Toolbar --}}
<div class="lc-toolbar">
    <form method="GET" action="{{ route('leads.index') }}" style="display:flex;gap:9px;flex:1;align-items:center;flex-wrap:wrap;" id="filterForm">
        <div class="lc-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="lc-search-input"
                   value="{{ request('search') }}"
                   placeholder="Search name, phone, email...">
        </div>
        <select name="status" class="lc-select">
            <option value="">All Status</option>
            @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="source" class="lc-select">
            <option value="">All Sources</option>
            @foreach($sourceLabels as $val => $label)
                <option value="{{ $val }}" {{ request('source') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
        <select name="assigned_to" class="lc-select">
            <option value="">All Agents</option>
            @foreach($filterUsers ?? [] as $tc)
                <option value="{{ $tc->id }}" {{ request('assigned_to') == $tc->id ? 'selected' : '' }}>{{ $tc->name }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="lc-btn-filter">
            <i class="fas fa-filter"></i> Filter
        </button>
        @if(request()->hasAny(['search','status','source','assigned_to']))
        <a href="{{ route('leads.index') }}" class="lc-btn-clear">
            <i class="fas fa-times"></i> Clear
        </a>
        @endif
    </form>
    <div style="font-size:12px;color:#9ca3af;white-space:nowrap;padding-left:4px;">
        {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }}
    </div>
</div>

{{-- ═══ CARD VIEW ═══ --}}
<div class="lc-cards-grid active" id="cardsView">
    @forelse($leads as $lead)
    @php
        $initial = strtoupper(substr($lead->name ?? 'L', 0, 1));
        $bgColor = $avatarColors[crc32($lead->name ?? '') % count($avatarColors)];
        $statusClass = 's-' . ($lead->status ?? 'new');
        $statusLabel = $statusLabels[$lead->status ?? 'new'] ?? ucfirst($lead->status ?? 'new');
        $sourceLabel = $sourceLabels[$lead->source ?? 'other'] ?? ucfirst($lead->source ?? 'other');
        $assignedName = $lead->assignedUser->name ?? $lead->assignments->first()?->assignedTo?->name ?? null;
    @endphp
    <div class="lc-card" data-id="{{ $lead->id }}">
        {{-- Top --}}
        <div class="lc-card-top">
            <div style="display:flex;align-items:center;gap:2px;margin-top:2px;">
                <input type="checkbox" class="lead-checkbox" value="{{ $lead->id }}"
                       onchange="updateBulkBar()" style="cursor:pointer;margin-right:4px;">
            </div>
            <div class="lc-avatar" style="background:{{ $bgColor }};">{{ $initial }}</div>
            <div style="flex:1;min-width:0;">
                <div class="lc-card-name" title="{{ $lead->name }}">{{ Str::limit($lead->name ?? 'Unknown', 24) }}</div>
                <div class="lc-card-phone">
                    <i class="fas fa-phone" style="font-size:10px;margin-right:3px;"></i>
                    {{ $lead->phone ?? '—' }}
                </div>
            </div>
            <span class="lc-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>

        {{-- Body --}}
        <div class="lc-card-body">
            @if($lead->email)
            <div class="lc-info-row">
                <i class="fas fa-envelope"></i>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:190px;" title="{{ $lead->email }}">{{ $lead->email }}</span>
            </div>
            @endif
            @if($lead->city)
            <div class="lc-info-row">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ $lead->city }}</span>
            </div>
            @endif
            <div class="lc-info-row">
                <i class="fas fa-user-tie"></i>
                <span>{{ $assignedName ?? 'Unassigned' }}</span>
            </div>
            <div class="lc-info-row" style="justify-content:space-between;">
                <span class="lc-source-pill">
                    <i class="fas fa-tag" style="font-size:9px;"></i> {{ $sourceLabel }}
                </span>
                <span style="font-size:11px;color:#9ca3af;">{{ $lead->created_at?->format('d M Y') }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="lc-card-footer">
            <a href="{{ route('leads.show', $lead) }}" class="lc-action lc-action-view">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('leads.show', $lead) }}#shortinfo" class="lc-action lc-action-short">
                <i class="fas fa-info-circle"></i> Short...
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
            <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                  onsubmit="return confirm('Delete {{ addslashes($lead->name ?? '') }}?');" style="flex:1;">
                @csrf @method('DELETE')
                <button type="submit" class="lc-action lc-action-del" style="width:100%;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="lc-empty">
        <div style="width:56px;height:56px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i class="fas fa-users" style="font-size:22px;color:#d1d5db;"></i>
        </div>
        <div style="font-size:15px;font-weight:600;color:#374151;margin-bottom:5px;">No leads found</div>
        <div style="font-size:12px;color:#9ca3af;">Try adjusting your filters or add a new lead.</div>
    </div>
    @endforelse
</div>

{{-- ═══ LIST VIEW ═══ --}}
<div class="lc-list-wrap" id="listView">
    <table class="lc-table">
        <thead>
            <tr>
                <th style="width:36px;">
                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" style="cursor:pointer;">
                </th>
                <th>Lead</th>
                <th>Status</th>
                <th>Source</th>
                <th>Assigned To</th>
                <th>Created</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            @php
                $initial = strtoupper(substr($lead->name ?? 'L', 0, 1));
                $bgColor = $avatarColors[crc32($lead->name ?? '') % count($avatarColors)];
                $statusClass = 's-' . ($lead->status ?? 'new');
                $statusLabel = $statusLabels[$lead->status ?? 'new'] ?? ucfirst($lead->status ?? 'new');
                $sourceLabel = $sourceLabels[$lead->source ?? 'other'] ?? ucfirst($lead->source ?? 'other');
                $assignedName = $lead->assignedUser->name ?? $lead->assignments->first()?->assignedTo?->name ?? null;
            @endphp
            <tr>
                <td>
                    <input type="checkbox" class="lead-checkbox" value="{{ $lead->id }}"
                           onchange="updateBulkBar()" style="cursor:pointer;">
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="lc-list-avatar" style="background:{{ $bgColor }};">{{ $initial }}</div>
                        <div>
                            <div class="lc-list-name">{{ Str::limit($lead->name ?? 'Unknown', 28) }}</div>
                            <div class="lc-list-phone">{{ $lead->phone ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="lc-status-badge {{ $statusClass }}" style="font-size:10px;">{{ $statusLabel }}</span>
                </td>
                <td>
                    <span class="lc-source-pill">{{ $sourceLabel }}</span>
                </td>
                <td style="font-size:12px;color:#374151;">{{ $assignedName ?? '—' }}</td>
                <td style="font-size:12px;color:#9ca3af;white-space:nowrap;">{{ $lead->created_at?->format('d M Y') }}</td>
                <td>
                    <div class="lc-list-actions" style="justify-content:flex-end;">
                        <a href="{{ route('leads.show', $lead) }}" class="lc-list-btn lc-list-btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('leads.show', $lead) }}#shortinfo" class="lc-list-btn lc-list-btn-short">
                            <i class="fas fa-info-circle"></i> Short
                        </a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                        <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                              onsubmit="return confirm('Delete {{ addslashes($lead->name ?? '') }}?');" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="lc-list-btn lc-list-btn-del">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:50px;color:#9ca3af;">
                    <i class="fas fa-users" style="font-size:28px;margin-bottom:10px;display:block;color:#e5e7eb;"></i>
                    No leads found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($leads->hasPages())
<div class="lc-pagination">{{ $leads->appends(request()->query())->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
// ── View Switch ──
function switchView(mode) {
    const cards = document.getElementById('cardsView');
    const list  = document.getElementById('listView');
    const btnC  = document.getElementById('btnCards');
    const btnL  = document.getElementById('btnList');

    if (mode === 'cards') {
        cards.classList.add('active');
        list.classList.remove('active');
        btnC.classList.add('active');
        btnL.classList.remove('active');
    } else {
        list.classList.add('active');
        cards.classList.remove('active');
        btnL.classList.add('active');
        btnC.classList.remove('active');
    }
    localStorage.setItem('leadsView', mode);
}

// Restore saved view
(function() {
    const saved = localStorage.getItem('leadsView');
    if (saved === 'list') switchView('list');
})();

// ── Bulk Select ──
function updateBulkBar() {
    const checked = document.querySelectorAll('.lead-checkbox:checked');
    const bar = document.getElementById('bulkBar');
    const cnt = document.getElementById('bulkCount');
    if (checked.length > 0) {
        bar.classList.add('show');
        cnt.textContent = checked.length;
    } else {
        bar.classList.remove('show');
    }
}

function toggleSelectAll(cb) {
    document.querySelectorAll('.lead-checkbox').forEach(c => {
        c.checked = cb.checked;
    });
    updateBulkBar();
}

function clearSelection() {
    document.querySelectorAll('.lead-checkbox').forEach(c => c.checked = false);
    const sa = document.getElementById('selectAll');
    if (sa) sa.checked = false;
    document.getElementById('bulkBar').classList.remove('show');
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(c => c.value);
}

function bulkAssign() {
    const ids = getSelectedIds();
    if (!ids.length) return;
    alert('Bulk assign: ' + ids.length + ' leads selected. Implement bulk assign modal here.');
}

function bulkDelete() {
    const ids = getSelectedIds();
    if (!ids.length) return;
    if (!confirm('Delete ' + ids.length + ' selected leads? This cannot be undone.')) return;
    // Submit bulk delete form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("leads.index") }}/bulk-delete';
    form.innerHTML = '@csrf @method("DELETE")';
    ids.forEach(id => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
        form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush