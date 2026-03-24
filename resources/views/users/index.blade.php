@php
    $user = auth()->user();
    if ($user && !$user->relationLoaded('role')) {
        $user->load('role');
    }
@endphp
@extends('layouts.app')

@section('title', 'Users - Base CRM')
@section('page-title', 'Users')

@section('header-actions')
    @if(auth()->user()->isAdmin())
    <button onclick="document.getElementById('hierarchyModal').style.display='flex'"
        class="px-4 py-2 border border-[#205A44] text-[#205A44] rounded-lg hover:bg-[#f0fdf4] transition-colors duration-200 text-sm font-medium flex items-center gap-2">
        <i class="fas fa-sitemap"></i> View Hierarchy
    </button>
    @endif
    @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
    <a href="{{ route('users.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        Create User
    </a>
    @endif
@endsection

@push('styles')
<style>
.uc-stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px; }
.uc-stat-card {
    background:#fff; border-radius:14px; padding:20px 24px;
    border:1px solid #e5e7eb; display:flex; align-items:center; gap:16px;
    box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.uc-stat-icon { width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.uc-stat-val { font-size:26px;font-weight:700;color:#111827;line-height:1; }
.uc-stat-lbl { font-size:12px;color:#6b7280;margin-top:3px;font-weight:500; }

.uc-toolbar {
    background:#fff; border-radius:14px; border:1px solid #e5e7eb;
    padding:14px 20px; margin-bottom:22px; display:flex; gap:12px; align-items:center;
    box-shadow:0 1px 4px rgba(0,0,0,.05); flex-wrap:wrap;
}
.uc-search-wrap { flex:1;min-width:200px;position:relative; }
.uc-search-wrap i { position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:14px; }
.uc-search-input {
    width:100%;padding:9px 12px 9px 36px;border:1.5px solid #e5e7eb;border-radius:9px;
    font-size:13.5px;color:#111827;outline:none;transition:.2s;background:#f9fafb;
}
.uc-search-input:focus { border-color:#205A44;background:#fff;box-shadow:0 0 0 3px rgba(32,90,68,.08); }
.uc-role-select {
    padding:9px 14px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:13.5px;
    color:#374151;outline:none;background:#f9fafb;min-width:160px;cursor:pointer;transition:.2s;
}
.uc-role-select:focus { border-color:#205A44;background:#fff; }
.uc-btn-search {
    padding:9px 22px;background:linear-gradient(135deg,#063A1C,#205A44);color:#fff;
    border:none;border-radius:9px;font-size:13.5px;font-weight:600;cursor:pointer;
    display:flex;align-items:center;gap:7px;transition:.2s;white-space:nowrap;
}
.uc-btn-search:hover { opacity:.9;transform:translateY(-1px); }
.uc-btn-clear {
    padding:9px 16px;background:#f3f4f6;color:#374151;border:none;border-radius:9px;
    font-size:13.5px;font-weight:500;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:6px;transition:.2s;
}
.uc-btn-clear:hover { background:#e5e7eb; }

.uc-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:20px; }
.uc-card {
    background:#fff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;
    box-shadow:0 2px 8px rgba(0,0,0,.06);transition:all .25s;cursor:default;
}
.uc-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.12);transform:translateY(-3px);border-color:#c8e6c9; }
.uc-card-banner { height:68px;position:relative; }
.uc-card-avatar {
    position:absolute;bottom:-24px;left:20px;
    width:52px;height:52px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;font-weight:700;color:#fff;
    border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,.18);
}
.uc-card-status-dot {
    position:absolute;bottom:-24px;right:18px;
    background:#fff;border-radius:20px;padding:3px 10px 3px 8px;
    font-size:11px;font-weight:600;display:flex;align-items:center;gap:5px;
    border:1.5px solid;box-shadow:0 2px 6px rgba(0,0,0,.08);
}
.uc-card-body { padding:36px 20px 16px; }
.uc-card-name { font-size:16px;font-weight:700;color:#111827;margin-bottom:3px;line-height:1.3;word-break:break-word; }
.uc-role-pill {
    display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;
    font-size:11px;font-weight:600;margin-bottom:14px;border:1px solid;
}
.uc-info-row { display:flex;align-items:center;gap:8px;font-size:12.5px;color:#6b7280;margin-bottom:8px; }
.uc-info-row i { width:14px;text-align:center;color:#9ca3af;flex-shrink:0; }
.uc-info-row span { color:#374151; }
.uc-card-footer {
    border-top:1px solid #f3f4f6;padding:14px 16px;
    display:flex;gap:8px;background:#fafafa;
}
.uc-action { flex:1;padding:8px 6px;border-radius:8px;font-size:12.5px;font-weight:600;
    border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;
    text-decoration:none;transition:.2s;
}
.uc-action:hover { opacity:.88;transform:translateY(-1px); }
.uc-action-view  { background:linear-gradient(135deg,#063A1C,#205A44);color:#fff; }
.uc-action-edit  { background:#f0fdf4;color:#065f46;border:1.5px solid #bbf7d0 !important; }
.uc-action-del   { background:#fff1f2;color:#be123c;border:1.5px solid #fecdd3 !important; }
.uc-action-login { background:#eff6ff;color:#1d4ed8;border:1.5px solid #bfdbfe !important; }

/* Impersonation Banner */
#impersonation-banner {
    display:none;position:fixed;top:0;left:0;right:0;z-index:99999;
    background:linear-gradient(135deg,#92400e,#b45309);color:#fff;
    padding:10px 24px;font-size:13px;font-weight:600;
    display:none;align-items:center;justify-content:center;gap:16px;
    box-shadow:0 3px 12px rgba(0,0,0,0.3);
}
#impersonation-banner .back-btn {
    background:#fff;color:#b45309;border:none;border-radius:8px;
    padding:6px 16px;font-weight:700;cursor:pointer;font-size:12px;
    display:flex;align-items:center;gap:6px;transition:.2s;
}
#impersonation-banner .back-btn:hover { background:#fef3c7; }

.uc-empty {
    grid-column:1/-1;background:#fff;border-radius:16px;border:1px solid #e5e7eb;
    padding:70px 20px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.uc-pagination { margin-top:22px;background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:12px 20px;box-shadow:0 1px 4px rgba(0,0,0,.04); }

@media(max-width:640px){
    .uc-stats-grid{grid-template-columns:1fr 1fr;}
    .uc-toolbar{flex-direction:column;align-items:stretch;}
    .uc-role-select,.uc-btn-search{width:100%;}
}
</style>
@endpush

@section('content')
@php
    $totalCount    = $users->total();
    $activeCount   = $users->getCollection()->where('is_active', true)->count();
    $inactiveCount = $users->getCollection()->where('is_active', false)->count();

    $roleColorMap = [
        'admin'                   => ['bg'=>'#7c3aed','light'=>'#ede9fe','border'=>'#c4b5fd','text'=>'#5b21b6'],
        'crm'                     => ['bg'=>'#1d4ed8','light'=>'#dbeafe','border'=>'#93c5fd','text'=>'#1e40af'],
        'hr_manager'              => ['bg'=>'#0369a1','light'=>'#e0f2fe','border'=>'#7dd3fc','text'=>'#075985'],
        'finance_manager'         => ['bg'=>'#0369a1','light'=>'#e0f2fe','border'=>'#7dd3fc','text'=>'#075985'],
        'sales_head'              => ['bg'=>'#be185d','light'=>'#fce7f3','border'=>'#f9a8d4','text'=>'#9d174d'],
        'sales_manager'           => ['bg'=>'#065f46','light'=>'#d1fae5','border'=>'#6ee7b7','text'=>'#064e3b'],
        'senior_manager'          => ['bg'=>'#15803d','light'=>'#dcfce7','border'=>'#86efac','text'=>'#14532d'],
        'assistant_sales_manager' => ['bg'=>'#b45309','light'=>'#fef3c7','border'=>'#fcd34d','text'=>'#92400e'],
        'sales_executive'         => ['bg'=>'#c2410c','light'=>'#ffedd5','border'=>'#fdba74','text'=>'#9a3412'],
    ];
    $defaultColor = ['bg'=>'#4b5563','light'=>'#f3f4f6','border'=>'#d1d5db','text'=>'#374151'];
@endphp

{{-- Impersonation Banner --}}
<div id="impersonation-banner" style="{{ session('impersonating_original_id') ? 'display:flex' : 'display:none' }}">
    <i class="fas fa-user-secret" style="font-size:15px;"></i>
    <span>⚠️ Admin mode: Aap <strong>{{ auth()->user()->name }}</strong> ke roop mein dekh rahe hain</span>
    <a href="/impersonate/stop" class="back-btn">
        <i class="fas fa-arrow-left"></i> Wapas Admin
    </a>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 18px;border-radius:10px;margin-bottom:18px;display:flex;align-items:center;gap:10px;font-size:14px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 18px;border-radius:10px;margin-bottom:18px;display:flex;align-items:center;gap:10px;font-size:14px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- Stats Bar --}}
<div class="uc-stats-grid">
    <div class="uc-stat-card">
        <div class="uc-stat-icon" style="background:linear-gradient(135deg,#063A1C,#205A44);">
            <i class="fas fa-users" style="color:#fff;font-size:18px;"></i>
        </div>
        <div>
            <div class="uc-stat-val">{{ $totalCount }}</div>
            <div class="uc-stat-lbl">Total Users</div>
        </div>
    </div>
    <div class="uc-stat-card">
        <div class="uc-stat-icon" style="background:linear-gradient(135deg,#065f46,#15803d);">
            <i class="fas fa-user-check" style="color:#fff;font-size:18px;"></i>
        </div>
        <div>
            <div class="uc-stat-val" style="color:#065f46;">{{ $activeCount }}</div>
            <div class="uc-stat-lbl">Active (this page)</div>
        </div>
    </div>
    <div class="uc-stat-card">
        <div class="uc-stat-icon" style="background:linear-gradient(135deg,#991b1b,#dc2626);">
            <i class="fas fa-user-times" style="color:#fff;font-size:18px;"></i>
        </div>
        <div>
            <div class="uc-stat-val" style="color:#991b1b;">{{ $inactiveCount }}</div>
            <div class="uc-stat-lbl">Inactive (this page)</div>
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="uc-toolbar">
    <form method="GET" action="{{ route('users.index') }}" style="display:flex;gap:10px;flex:1;align-items:center;flex-wrap:wrap;">
        <div class="uc-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="uc-search-input"
                   value="{{ request('search') }}"
                   placeholder="Search by name, email, or phone...">
        </div>
        <select name="role" class="uc-role-select">
            <option value="">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="uc-btn-search">
            <i class="fas fa-filter"></i> Filter
        </button>
        @if(request('search') || request('role'))
        <a href="{{ route('users.index') }}" class="uc-btn-clear">
            <i class="fas fa-times"></i> Clear
        </a>
        @endif
    </form>
    <div style="font-size:12px;color:#9ca3af;white-space:nowrap;padding-left:4px;">
        {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $totalCount }} users
    </div>
</div>

{{-- User Cards --}}
<div class="uc-grid">
    @forelse($users as $user)
    @php
        $slug   = $user->role->slug ?? 'default';
        $color  = $roleColorMap[$slug] ?? $defaultColor;
        $initial = strtoupper(substr($user->name, 0, 1));
    @endphp
    <div class="uc-card">
        {{-- Banner --}}
        <div class="uc-card-banner" style="background:linear-gradient(135deg, {{ $color['bg'] }}, {{ $color['bg'] }}cc);">
            <div class="uc-card-avatar" style="background:{{ $color['bg'] }};">{{ $initial }}</div>
            @if($user->is_active)
            <div class="uc-card-status-dot" style="color:#065f46;border-color:#6ee7b7;background:#fff;">
                <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 2s infinite;"></span> Active
            </div>
            @else
            <div class="uc-card-status-dot" style="color:#991b1b;border-color:#fca5a5;background:#fff;">
                <span style="width:7px;height:7px;border-radius:50%;background:#ef4444;display:inline-block;"></span> Inactive
            </div>
            @endif
        </div>

        {{-- Body --}}
        <div class="uc-card-body">
            <div class="uc-card-name">{{ $user->name }}</div>
            <span class="uc-role-pill" style="background:{{ $color['light'] }};color:{{ $color['text'] }};border-color:{{ $color['border'] }};">
                <i class="fas fa-shield-alt" style="font-size:9px;"></i> {{ $user->getDisplayRoleName() }}
            </span>

            <div class="uc-info-row">
                <i class="fas fa-envelope"></i>
                <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;" title="{{ $user->email }}">{{ $user->email }}</span>
            </div>
            <div class="uc-info-row">
                <i class="fas fa-phone"></i>
                <span>{{ $user->phone ?? '—' }}</span>
            </div>
            @if($user->manager)
            <div class="uc-info-row">
                <i class="fas fa-user-tie"></i>
                <span>Reports to: <strong>{{ $user->manager->name }}</strong></span>
            </div>
            @endif
            <div class="uc-info-row" style="margin-top:4px;">
                <i class="fas fa-clock"></i>
                <span>Joined {{ $user->created_at->format('d M Y') }}</span>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="uc-card-footer" style="flex-direction:column;gap:8px;">
            <div style="display:flex;gap:8px;width:100%;">
                <a href="{{ route('users.show', $user) }}" class="uc-action uc-action-view">
                    <i class="fas fa-eye"></i> View
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isCrm())
                <a href="{{ route('users.edit', $user) }}" class="uc-action uc-action-edit">
                    <i class="fas fa-pen"></i> Edit
                </a>
                @endif
                @if(auth()->user()->isAdmin())
                <form action="{{ route('users.destroy', $user) }}" method="POST"
                      onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');" style="flex:1;">
                    @csrf @method('DELETE')
                    <button type="submit" class="uc-action uc-action-del" style="width:100%;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                @endif
            </div>
            @if(auth()->user()->isAdmin())
            <a href="/impersonate/{{ $user->id }}"
                onclick="return confirm('Login as {{ addslashes($user->name) }}?')"
                class="uc-action uc-action-login"
                title="Login as {{ $user->name }}"
                style="width:100%;justify-content:center;">
                <i class="fas fa-key"></i> Login as {{ $user->name }}
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="uc-empty">
        <div style="width:64px;height:64px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <i class="fas fa-users" style="font-size:26px;color:#d1d5db;"></i>
        </div>
        <div style="font-size:16px;font-weight:600;color:#374151;margin-bottom:6px;">No users found</div>
        <div style="font-size:13px;color:#9ca3af;">Try adjusting your search or filter criteria.</div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($users->hasPages())
<div class="uc-pagination">{{ $users->links() }}</div>
@endif

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
@endsection

@push('scripts')
{{-- Hierarchy Modal --}}
@if(auth()->user()->isAdmin())
<div id="hierarchyModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9999;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:1150px;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.3);">

        {{-- Header --}}
        <div style="padding:18px 28px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:linear-gradient(135deg,#063A1C,#205A44);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-sitemap" style="color:#fff;font-size:16px;"></i>
                </div>
                <div>
                    <div style="font-size:17px;font-weight:700;color:#111827;">User Hierarchy</div>
                    <div style="font-size:12px;color:#6b7280;" id="hierUserCount"></div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                {{-- Toggle --}}
                <div style="background:#f3f4f6;border-radius:10px;padding:4px;display:flex;gap:4px;">
                    <button id="btnDemo" onclick="switchMode('demo')"
                        style="padding:6px 16px;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;background:#063A1C;color:#fff;transition:all .2s;">
                        <i class="fas fa-eye" style="margin-right:4px;"></i> Demo
                    </button>
                    <button id="btnActual" onclick="switchMode('actual')"
                        style="padding:6px 16px;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;background:transparent;color:#6b7280;transition:all .2s;">
                        <i class="fas fa-users" style="margin-right:4px;"></i> Actual
                    </button>
                </div>
                <button onclick="document.getElementById('hierarchyModal').style.display='none'"
                    style="width:32px;height:32px;border:none;background:#f3f4f6;border-radius:8px;cursor:pointer;font-size:18px;color:#6b7280;display:flex;align-items:center;justify-content:center;">×</button>
            </div>
        </div>

        {{-- Demo notice banner --}}
        <div id="demoBanner" style="background:#fefce8;border-bottom:1px solid #fde68a;padding:8px 28px;font-size:12px;color:#92400e;display:flex;align-items:center;gap:8px;flex-shrink:0;">
            <i class="fas fa-info-circle"></i>
            <strong>Demo Mode:</strong> Yeh ek sample hierarchy hai jo dikhata hai ki CRM mein user structure kaisa hona chahiye. Actual data dekhne ke liye <strong>Actual</strong> tab click karein.
        </div>

        {{-- Legend --}}
        <div style="padding:10px 28px;border-bottom:1px solid #f3f4f6;display:flex;gap:14px;flex-wrap:wrap;flex-shrink:0;align-items:center;">
            <span style="font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;">Roles:</span>
            @foreach([['#7c3aed','Admin'],['#1d4ed8','CRM'],['#0369a1','HR / Finance'],['#be185d','Sales Head'],['#065f46','Sales Manager'],['#15803d','Senior Manager'],['#ca8a04','Asst. SM'],['#b45309','Sales Executive']] as $l)
            <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:#374151;">
                <span style="width:10px;height:10px;border-radius:3px;background:{{ $l[0] }};display:inline-block;"></span> {{ $l[1] }}
            </div>
            @endforeach
        </div>

        {{-- Org Chart --}}
        <div id="orgChartContainer" style="overflow:auto;padding:28px;flex:1;"></div>
    </div>
</div>

<script>
const hierUsers = @json($hierarchyUsers);
let currentMode = 'demo';

// ── Demo data ──────────────────────────────────────────────
const demoUsers = [
    { id:1,  name:'Rahul Sharma',   role:'Admin',                   role_slug:'admin',                   manager_id:null, avatar:'R', children:[] },
    { id:2,  name:'Priya Singh',    role:'CRM Manager',             role_slug:'crm',                     manager_id:1,    avatar:'P', children:[] },
    { id:3,  name:'Anita Verma',    role:'HR Manager',              role_slug:'hr_manager',              manager_id:1,    avatar:'A', children:[] },
    { id:4,  name:'Suresh Gupta',   role:'Finance Manager',         role_slug:'finance_manager',         manager_id:1,    avatar:'S', children:[] },
    { id:5,  name:'Arjun Kapoor',   role:'Sales Head',              role_slug:'sales_head',              manager_id:1,    avatar:'A', children:[] },
    { id:14, name:'Mohan Yadav',    role:'Sales Manager',           role_slug:'sales_manager',           manager_id:5,    avatar:'M', children:[] },
    { id:6,  name:'Deepak Kumar',   role:'Senior Manager',          role_slug:'senior_manager',          manager_id:14,   avatar:'D', children:[] },
    { id:7,  name:'Neha Joshi',     role:'Senior Manager',          role_slug:'senior_manager',          manager_id:14,   avatar:'N', children:[] },
    { id:8,  name:'Amit Patel',     role:'Asst. Sales Manager',     role_slug:'assistant_sales_manager', manager_id:6,    avatar:'A', children:[] },
    { id:9,  name:'Kavita Rao',     role:'Asst. Sales Manager',     role_slug:'assistant_sales_manager', manager_id:7,    avatar:'K', children:[] },
    { id:10, name:'Ravi Mehta',     role:'Sales Executive',         role_slug:'sales_executive',         manager_id:8,    avatar:'R', children:[] },
    { id:11, name:'Sunita Das',     role:'Sales Executive',         role_slug:'sales_executive',         manager_id:8,    avatar:'S', children:[] },
    { id:12, name:'Vijay Tiwari',   role:'Sales Executive',         role_slug:'sales_executive',         manager_id:9,    avatar:'V', children:[] },
    { id:13, name:'Pooja Mishra',   role:'Sales Executive',         role_slug:'sales_executive',         manager_id:9,    avatar:'P', children:[] },
];

// ── Colors ─────────────────────────────────────────────────
const roleColors = {
    'admin':                   { bg:'#ede9fe', border:'#7c3aed', text:'#5b21b6' },
    'crm':                     { bg:'#dbeafe', border:'#1d4ed8', text:'#1e40af' },
    'hr_manager':              { bg:'#e0f2fe', border:'#0369a1', text:'#075985' },
    'finance_manager':         { bg:'#e0f2fe', border:'#0369a1', text:'#075985' },
    'sales_head':              { bg:'#fce7f3', border:'#be185d', text:'#9d174d' },
    'sales_manager':           { bg:'#d1fae5', border:'#065f46', text:'#064e3b' },
    'senior_manager':          { bg:'#d1fae5', border:'#15803d', text:'#14532d' },
    'assistant_sales_manager': { bg:'#fef9c3', border:'#ca8a04', text:'#92400e' },
    'sales_executive':         { bg:'#ffedd5', border:'#b45309', text:'#92400e' },
};
function getColor(slug) {
    return roleColors[slug] || { bg:'#f3f4f6', border:'#6b7280', text:'#374151' };
}

// ── Build tree from flat list ───────────────────────────────
function buildTree(users, forceAdminRoot = false) {
    const map = {};
    users.forEach(u => { map[u.id] = { ...u, children: [] }; });

    if (forceAdminRoot) {
        const admins   = users.filter(u => u.role_slug === 'admin');
        const adminIds = new Set(admins.map(u => u.id));
        // CRM, HR Manager, Finance Manager, Sales Manager are auto-placed under Admin when they have no manager.
        // Everyone else with no manager_id → floating child of their natural parent role (no line).
        const adminAutoSlugs = new Set(['crm', 'hr_manager', 'finance_manager', 'sales_manager', 'sales_head']);

        // Natural parent role for unconnected users (used for visual placement, no line drawn)
        const naturalParentSlug = {
            'sales_manager':          null,
            'senior_manager':         'sales_manager',
            'assistant_sales_manager':'senior_manager',
            'sales_executive':        'assistant_sales_manager',
        };

        const placedIds = new Set(admins.map(u => u.id));

        users.forEach(u => {
            if (adminIds.has(u.id)) return;
            if (u.manager_id && map[u.manager_id]) {
                // Real manager relationship → connected child
                map[u.manager_id].children.push(map[u.id]);
                placedIds.add(u.id);
            } else if (adminAutoSlugs.has(u.role_slug)) {
                // CRM / HR / Finance → always under Admin
                const firstAdmin = admins[0];
                if (firstAdmin) { map[firstAdmin.id].children.push(map[u.id]); placedIds.add(u.id); }
            } else {
                // No manager → place as floating child of natural parent role (no connecting line)
                const pSlug = naturalParentSlug[u.role_slug];
                const parentNode = pSlug ? users.find(p => p.role_slug === pSlug) : null;
                if (parentNode && map[parentNode.id]) {
                    if (!map[parentNode.id].floatingChildren) map[parentNode.id].floatingChildren = [];
                    map[parentNode.id].floatingChildren.push(map[u.id]);
                    placedIds.add(u.id);
                }
                // If no natural parent exists in this org → remains as independent root
            }
        });

        const independentRoots = users.filter(u => !placedIds.has(u.id)).map(u => map[u.id]);
        return [...admins.map(a => map[a.id]), ...independentRoots];
    }

    // Default: standard tree build
    const roots = [];
    users.forEach(u => {
        if (u.manager_id && map[u.manager_id]) {
            map[u.manager_id].children.push(map[u.id]);
        } else {
            roots.push(map[u.id]);
        }
    });
    return roots;
}

// ── Render single node ──────────────────────────────────────
function renderNode(node) {
    const c = getColor(node.role_slug);
    const hasChildren = node.children && node.children.length > 0;
    const LINE = '#94a3b8';

    const card = `
        <div style="background:${c.bg};border:2px solid ${c.border};border-radius:12px;
            padding:12px 16px;text-align:center;min-width:124px;max-width:154px;
            cursor:default;transition:transform .15s,box-shadow .15s;
            box-shadow:0 2px 10px rgba(0,0,0,0.08);"
            onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 22px rgba(0,0,0,0.15)'"
            onmouseout="this.style.transform='';this.style.boxShadow='0 2px 10px rgba(0,0,0,0.08)'">
            <div style="width:40px;height:40px;border-radius:50%;background:${c.border};color:#fff;
                font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                ${node.avatar}
            </div>
            <div style="font-size:12px;font-weight:700;color:#111827;line-height:1.3;margin-bottom:5px;word-break:break-word;">${node.name}</div>
            <div style="font-size:10px;font-weight:600;color:${c.text};background:#fff;border-radius:20px;padding:2px 8px;display:inline-block;border:1px solid ${c.border};">${node.role}</div>
            ${hasChildren ? `<div style="font-size:10px;color:${c.text};margin-top:4px;opacity:0.75;">${node.children.length} direct report${node.children.length>1?'s':''}</div>` : ''}
        </div>`;

    // Build floating children section (no manager — no connecting line)
    const floatingSection = (node.floatingChildren && node.floatingChildren.length)
        ? `<div style="margin-top:16px;padding-top:12px;border-top:1.5px dashed #cbd5e1;display:flex;flex-direction:column;align-items:center;">
               <div style="font-size:10px;font-weight:600;color:#94a3b8;margin-bottom:10px;display:flex;align-items:center;gap:5px;">
                   <i class="fas fa-unlink" style="font-size:9px;"></i> No manager assigned
               </div>
               <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;align-items:flex-start;">
                   ${node.floatingChildren.map(fc => renderNode(fc)).join('')}
               </div>
           </div>`
        : '';

    if (!hasChildren) {
        // No real children — but may still have floating ones
        return `<div style="display:inline-flex;flex-direction:column;align-items:center;">${card}${floatingSection}</div>`;
    }

    const n = node.children.length;

    // Single child: simple vertical line
    if (n === 1) {
        return `
            <div style="display:inline-flex;flex-direction:column;align-items:center;">
                ${card}
                <div style="width:2px;height:28px;background:${LINE};"></div>
                ${renderNode(node.children[0])}
                ${floatingSection}
            </div>`;
    }

    // Multiple children: proper connected bus line
    // Each child column gets an "arm" that forms the horizontal bus:
    //   first child  → only right half filled (line starts at child center, goes right)
    //   middle children → full width filled (continuous)
    //   last child   → only left half filled (line ends at child center)
    // Then a short vertical drop from the bus down to each child card.
    const childrenHtml = node.children.map((ch, i) => {
        const isFirst = i === 0;
        const isLast  = i === n - 1;

        let arm;
        if (isFirst) {
            arm = `<div style="width:100%;height:2px;display:flex;">
                       <div style="flex:1;"></div>
                       <div style="flex:1;background:${LINE};"></div>
                   </div>`;
        } else if (isLast) {
            arm = `<div style="width:100%;height:2px;display:flex;">
                       <div style="flex:1;background:${LINE};"></div>
                       <div style="flex:1;"></div>
                   </div>`;
        } else {
            arm = `<div style="width:100%;height:2px;background:${LINE};"></div>`;
        }

        return `
            <div style="display:inline-flex;flex-direction:column;align-items:center;flex:1;min-width:160px;">
                ${arm}
                <div style="width:2px;height:20px;background:${LINE};"></div>
                ${renderNode(ch)}
            </div>`;
    }).join('');

    return `
        <div style="display:inline-flex;flex-direction:column;align-items:center;">
            ${card}
            <div style="width:2px;height:18px;background:${LINE};"></div>
            <div style="display:flex;align-items:flex-start;gap:0;">
                ${childrenHtml}
            </div>
            ${floatingSection}
        </div>`;
}

// ── Switch toggle ───────────────────────────────────────────
function switchMode(mode) {
    currentMode = mode;
    const btnDemo   = document.getElementById('btnDemo');
    const btnActual = document.getElementById('btnActual');
    const banner    = document.getElementById('demoBanner');

    if (mode === 'demo') {
        btnDemo.style.background   = '#063A1C';
        btnDemo.style.color        = '#fff';
        btnActual.style.background = 'transparent';
        btnActual.style.color      = '#6b7280';
        banner.style.display       = 'flex';
    } else {
        btnActual.style.background = '#063A1C';
        btnActual.style.color      = '#fff';
        btnDemo.style.background   = 'transparent';
        btnDemo.style.color        = '#6b7280';
        banner.style.display       = 'none';
    }
    renderHierarchy();
}

// ── Main render ─────────────────────────────────────────────
function renderHierarchy() {
    const data = currentMode === 'demo' ? demoUsers : hierUsers;
    const container = document.getElementById('orgChartContainer');
    const countEl   = document.getElementById('hierUserCount');

    if (currentMode === 'demo') {
        countEl.textContent = 'Sample hierarchy — ' + data.length + ' demo users';
    } else {
        countEl.textContent = data.length + ' active users';
    }

    // Both modes use forceAdminRoot=true so hierarchy is always:
    // Admin → [CRM, HR, Finance, Sales Manager] → [Senior Manager] → [Asst. SM] → [Sales Executive]
    // Users with no manager_id get placed under their natural parent role.
    const tree = buildTree(JSON.parse(JSON.stringify(data)), true);

    if (tree.length === 0) {
        container.innerHTML = `<div style="text-align:center;padding:60px;color:#6b7280;">
            <i class="fas fa-users" style="font-size:40px;margin-bottom:12px;opacity:.3;"></i>
            <div style="font-size:15px;font-weight:600;">No users found</div>
            <div style="font-size:13px;margin-top:4px;">Add users to see the hierarchy here.</div>
        </div>`;
        return;
    }

    container.innerHTML = `
        <div style="display:flex;gap:32px;justify-content:center;align-items:flex-start;min-width:max-content;margin:0 auto;padding-bottom:16px;">
            ${tree.map(root => renderNode(root)).join('')}
        </div>`;
}

// ── Events ──────────────────────────────────────────────────
document.getElementById('hierarchyModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
document.querySelector('[onclick*="hierarchyModal"]').addEventListener('click', function() {
    currentMode = 'demo';
    // reset toggle visuals
    document.getElementById('btnDemo').style.background   = '#063A1C';
    document.getElementById('btnDemo').style.color        = '#fff';
    document.getElementById('btnActual').style.background = 'transparent';
    document.getElementById('btnActual').style.color      = '#6b7280';
    document.getElementById('demoBanner').style.display   = 'flex';
    setTimeout(renderHierarchy, 50);
});
</script>
@endif
<script>
// ── Impersonation ───────────────────────────────────────────
function getApiToken() {
    return document.querySelector('meta[name="api-token"]')?.content || '';
}

function loginAsUser(userId, userName) {
    if (!confirm('Login as ' + userName + '?\nAap admin ke roop mein unka account dekh sakte hain.')) return;

    const adminToken = getApiToken();
    if (!adminToken) { alert('❌ API token nahi mila. Page refresh karo.'); return; }

    fetch('/api/users/' + userId + '/impersonate', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + adminToken,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Admin token save karo
            localStorage.setItem('admin_original_token', adminToken);
            localStorage.setItem('impersonate_token', data.impersonate_token);
            localStorage.setItem('is_impersonating', 'true');
            localStorage.setItem('impersonate_user_name', data.target_user.name);
            showImpersonationBanner(data.target_user.name);
            alert('✅ Ab aap ' + data.target_user.name + ' ke roop mein hain!');
        } else {
            alert('❌ Error: ' + (data.message || 'Kuch galat hua'));
        }
    })
    .catch(e => alert('❌ Network error: ' + e.message));
}

function stopImpersonation() {
    const impToken = localStorage.getItem('impersonate_token');
    fetch('/api/impersonate/stop', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + impToken,
            'Content-Type': 'application/json',
        }
    }).finally(() => {
        localStorage.removeItem('admin_original_token');
        localStorage.removeItem('impersonate_token');
        localStorage.removeItem('is_impersonating');
        localStorage.removeItem('impersonate_user_name');
        window.location.reload();
    });
}

function showImpersonationBanner(name) {
    const banner = document.getElementById('impersonation-banner');
    if (banner) {
        document.getElementById('impersonate-user-name').textContent = name;
        banner.style.display = 'flex';
        document.body.style.paddingTop = '48px';
    }
}

// Page load pe check karo impersonation chal raha hai kya
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('is_impersonating') === 'true') {
        const name = localStorage.getItem('impersonate_user_name') || 'User';
        showImpersonationBanner(name);
    }
});
</script>
@endpush

