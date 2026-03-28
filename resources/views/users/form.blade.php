@extends('layouts.app')

@section('title', ($user ? 'Edit User' : 'Create User') . ' - Base CRM')
@section('page-title', $user ? 'Edit User' : 'Create User')

@push('styles')
<style>
.uf-wrap { max-width: 640px; margin: 0 auto; }
.uf-card { background:#fff; border-radius:16px; border:1px solid #e5e7eb; padding:32px; box-shadow:0 2px 8px rgba(0,0,0,.06); }
.uf-section-label {
    font-size:11px; font-weight:700; color:#9ca3af; text-transform:uppercase;
    letter-spacing:.6px; margin-bottom:16px; padding-bottom:10px;
    border-bottom:1.5px solid #f3f4f6; display:flex; align-items:center; gap:8px;
}
.uf-field { margin-bottom:20px; }
.uf-label { font-size:13px; font-weight:600; color:#374151; margin-bottom:7px; display:block; }
.uf-label span.req { color:#ef4444; margin-left:2px; }
.uf-label span.opt { color:#9ca3af; font-weight:400; font-size:12px; margin-left:4px; }
.uf-input {
    width:100%; padding:10px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
    font-size:13.5px; color:#111827; outline:none; transition:.2s; background:#f9fafb;
    box-sizing:border-box;
}
.uf-input:focus { border-color:#205A44; background:#fff; box-shadow:0 0 0 3px rgba(32,90,68,.08); }
.uf-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 14px center; padding-right:36px; cursor:pointer; }

/* ── Custom Role Dropdown ─────────────────────── */
.role-picker { position:relative; }
.role-trigger {
    width:100%; padding:10px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
    background:#f9fafb; cursor:pointer; display:flex; align-items:center; gap:10px;
    transition:.2s; user-select:none; box-sizing:border-box;
}
.role-trigger:hover, .role-trigger.open { border-color:#205A44; background:#fff; box-shadow:0 0 0 3px rgba(32,90,68,.08); }
.role-trigger-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px; }
.role-trigger-text { flex:1; }
.role-trigger-name { font-size:13.5px; font-weight:600; color:#111827; }
.role-trigger-desc { font-size:11.5px; color:#6b7280; margin-top:1px; }
.role-trigger-placeholder { font-size:13.5px; color:#9ca3af; }
.role-trigger-arrow { color:#9ca3af; font-size:11px; flex-shrink:0; transition:transform .2s; }
.role-trigger.open .role-trigger-arrow { transform:rotate(180deg); }

.role-dropdown {
    display:none; position:absolute; top:calc(100% + 6px); left:0; right:0;
    background:#fff; border:1.5px solid #e5e7eb; border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.12); z-index:200; overflow:hidden;
    max-height:360px; overflow-y:auto;
}
.role-dropdown.open { display:block; }
.role-group-header {
    padding:8px 14px 5px; font-size:10.5px; font-weight:700; color:#9ca3af;
    text-transform:uppercase; letter-spacing:.6px; background:#f9fafb;
    border-bottom:1px solid #f3f4f6; border-top:1px solid #f3f4f6; margin-top:2px;
}
.role-group-header:first-child { border-top:none; margin-top:0; }
.role-option {
    display:flex; align-items:center; gap:12px; padding:10px 14px; cursor:pointer;
    transition:.15s; border-bottom:1px solid #f9fafb;
}
.role-option:hover { background:#f0fdf4; }
.role-option.selected { background:#f0fdf4; }
.role-option.selected .role-opt-name { color:#065f46; }
.role-opt-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:13px; }
.role-opt-name { font-size:13px; font-weight:600; color:#111827; }
.role-opt-desc { font-size:11.5px; color:#9ca3af; margin-top:1px; }
.role-opt-check { margin-left:auto; color:#205A44; font-size:12px; display:none; }
.role-option.selected .role-opt-check { display:block; }

/* Manager field slide animation */
#manager-field { transition: all .3s; overflow:hidden; }
#manager-field.hidden-field { max-height:0; margin:0; opacity:0; pointer-events:none; }
#manager-field.visible-field { max-height:200px; opacity:1; }

.uf-manager-hint {
    display:flex; align-items:center; gap:7px; background:#f0fdf4;
    border:1px solid #bbf7d0; border-radius:8px; padding:9px 12px;
    font-size:12px; color:#065f46; margin-top:8px;
}

.role-badge {
    display:inline-flex; align-items:center; gap:5px; padding:3px 10px;
    border-radius:20px; font-size:11px; font-weight:600;
}

/* Password toggle */
.uf-pw-wrap { position:relative; }
.uf-pw-toggle { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#9ca3af; padding:2px; }
.uf-pw-toggle:hover { color:#374151; }

/* Status toggle */
.uf-toggle-row { display:flex; align-items:center; justify-content:space-between; background:#f9fafb; border:1.5px solid #e5e7eb; border-radius:10px; padding:12px 16px; }
.uf-toggle-label { font-size:13.5px; font-weight:600; color:#374151; }
.uf-toggle-sub { font-size:12px; color:#6b7280; margin-top:2px; }
.toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; cursor:pointer; transition:.3s; }
.toggle-slider:before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.toggle-switch input:checked + .toggle-slider { background:#205A44; }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(20px); }

.uf-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:28px; padding-top:20px; border-top:1.5px solid #f3f4f6; }
.uf-btn-cancel { padding:10px 22px; border:1.5px solid #e5e7eb; border-radius:10px; color:#374151; font-size:13.5px; font-weight:600; background:#fff; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:.2s; }
.uf-btn-cancel:hover { background:#f3f4f6; }
.uf-btn-save { padding:10px 28px; background:linear-gradient(135deg,#063A1C,#205A44); color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:7px; transition:.2s; }
.uf-btn-save:hover { opacity:.9; transform:translateY(-1px); }
</style>
@endpush

@php
/* Hierarchy order for role dropdown */
$roleOrder = [
    'admin', 'crm', 'hr_manager', 'finance_manager',
    'sales_manager', 'senior_manager', 'assistant_sales_manager', 'sales_executive'
];

$roleGroups = [
    'Management'   => ['admin', 'crm'],
    'Support'      => ['hr_manager', 'finance_manager'],
    'Sales Team'   => ['sales_manager', 'senior_manager', 'assistant_sales_manager', 'sales_executive'],
];

$roleIcons = [
    'admin'                   => 'fa-shield-alt',
    'crm'                     => 'fa-desktop',
    'hr_manager'              => 'fa-users',
    'finance_manager'         => 'fa-coins',
    'sales_manager'           => 'fa-chess-king',
    'senior_manager'          => 'fa-user-tie',
    'assistant_sales_manager' => 'fa-user-cog',
    'sales_executive'         => 'fa-phone-alt',
];

$roleColors = [
    'admin'                   => ['bg'=>'#ede9fe','color'=>'#6d28d9'],
    'crm'                     => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
    'hr_manager'              => ['bg'=>'#e0f2fe','color'=>'#0369a1'],
    'finance_manager'         => ['bg'=>'#fef3c7','color'=>'#b45309'],
    'sales_manager'           => ['bg'=>'#d1fae5','color'=>'#065f46'],
    'senior_manager'          => ['bg'=>'#dcfce7','color'=>'#15803d'],
    'assistant_sales_manager' => ['bg'=>'#fefce8','color'=>'#ca8a04'],
    'sales_executive'         => ['bg'=>'#ffedd5','color'=>'#c2410c'],
];

$roleDescs = [
    'admin'                   => 'Full system access',
    'crm'                     => 'Manage leads & users',
    'hr_manager'              => 'HR tasks & records',
    'finance_manager'         => 'Approve incentives',
    'sales_manager'           => 'Head of sales team',
    'senior_manager'          => 'Manage sales sub-team',
    'assistant_sales_manager' => 'Support sales managers',
    'sales_executive'         => 'Handle assigned leads',
];

/* Sorted roles collection */
$rolesBySlugs = $roles->keyBy('slug');
$sortedRoles = collect($roleOrder)->filter(fn($s) => $rolesBySlugs->has($s))->map(fn($s) => $rolesBySlugs[$s]);

/* Roles that DON'T need a manager */
$noManagerRoles = ['admin', 'crm', 'hr_manager', 'finance_manager', 'sales_manager'];

/* Manager filter rules per role */
$managerRoleMap = [
    'senior_manager'          => ['sales_manager'],
    'assistant_sales_manager' => ['admin', 'crm', 'sales_manager', 'senior_manager'],
    'sales_executive'         => ['assistant_sales_manager'],
];

/* Current selected role slug (for edit) */
$currentRoleSlug = $user ? ($user->role->slug ?? '') : old('role_id_slug', '');
@endphp

@section('content')
<div class="uf-wrap">

    @if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:13.5px;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="uf-card">
        <form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if($user) @method('PUT') @endif

            {{-- ── Basic Info ─────────────────────────────── --}}
            <div class="uf-section-label">
                <i class="fas fa-user" style="color:#205A44;"></i> Basic Information
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="uf-field">
                    <label class="uf-label">Full Name <span class="req">*</span></label>
                    <input type="text" name="name" class="uf-input"
                           value="{{ old('name', $user?->name) }}" required
                           placeholder="Enter full name">
                </div>
                <div class="uf-field">
                    <label class="uf-label">Phone <span class="opt">(optional)</span></label>
                    <input type="text" name="phone" class="uf-input"
                           value="{{ old('phone', $user?->phone) }}"
                           placeholder="+91 XXXXX XXXXX">
                </div>
            </div>

            <div class="uf-field">
                <label class="uf-label">Email Address <span class="req">*</span></label>
                <input type="email" name="email" class="uf-input"
                       value="{{ old('email', $user?->email) }}" required
                       placeholder="user@example.com">
            </div>

            <div class="uf-field">
                <label class="uf-label">
                    Password
                    @if(!$user)<span class="req">*</span>@else<span class="opt">(leave blank to keep current)</span>@endif
                </label>
                <div class="uf-pw-wrap">
                    <input type="password" name="password" id="passwordInput" class="uf-input"
                           @if(!$user) required @endif minlength="8"
                           placeholder="{{ $user ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}">
                    <button type="button" class="uf-pw-toggle" onclick="togglePw()">
                        <i class="fas fa-eye" id="pwEyeIcon"></i>
                    </button>
                </div>
            </div>

            {{-- ── Role & Hierarchy ────────────────────────── --}}
            <div class="uf-section-label" style="margin-top:8px;">
                <i class="fas fa-sitemap" style="color:#205A44;"></i> Role & Hierarchy
            </div>

            <div class="uf-field">
                <label class="uf-label">Role <span class="req">*</span></label>

                {{-- Hidden input for form submission --}}
                <input type="hidden" name="role_id" id="roleIdInput"
                       value="{{ old('role_id', $user?->role_id) }}" required>

                {{-- Custom Role Picker --}}
                <div class="role-picker" id="rolePicker">
                    <div class="role-trigger" id="roleTrigger" onclick="toggleRoleDropdown()">
                        <div class="role-trigger-icon" id="triggerIcon" style="background:#f3f4f6;">
                            <i class="fas fa-user-tag" style="color:#9ca3af;"></i>
                        </div>
                        <div class="role-trigger-text">
                            <div id="triggerContent" class="role-trigger-placeholder">Select a role...</div>
                        </div>
                        <i class="fas fa-chevron-down role-trigger-arrow"></i>
                    </div>

                    <div class="role-dropdown" id="roleDropdown">
                        @foreach($roleGroups as $groupName => $groupSlugs)
                            @php $groupRoles = $sortedRoles->filter(fn($r) => in_array($r->slug, $groupSlugs)); @endphp
                            @if($groupRoles->count())
                            <div class="role-group-header">{{ $groupName }}</div>
                            @foreach($groupRoles as $role)
                            @php
                                $ic  = $roleIcons[$role->slug]  ?? 'fa-user';
                                $clr = $roleColors[$role->slug] ?? ['bg'=>'#f3f4f6','color'=>'#6b7280'];
                                $dsc = $roleDescs[$role->slug]  ?? '';
                                $sel = (old('role_id', $user?->role_id) == $role->id);
                            @endphp
                            <div class="role-option {{ $sel ? 'selected' : '' }}"
                                 onclick="selectRole(this, {{ $role->id }}, '{{ $role->slug }}', '{{ $role->name }}', '{{ $ic }}', '{{ $clr['bg'] }}', '{{ $clr['color'] }}', '{{ $dsc }}')">
                                <div class="role-opt-icon" style="background:{{ $clr['bg'] }};">
                                    <i class="fas {{ $ic }}" style="color:{{ $clr['color'] }};"></i>
                                </div>
                                <div>
                                    <div class="role-opt-name">{{ $role->name }}</div>
                                    <div class="role-opt-desc">{{ $dsc }}</div>
                                </div>
                                <i class="fas fa-check role-opt-check"></i>
                            </div>
                            @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>

                <div id="roleHint" style="margin-top:7px;font-size:12px;color:#6b7280;display:none;padding-left:4px;"></div>
            </div>

            {{-- Manager field — shown/hidden by JS --}}
            <div id="manager-field" class="{{ in_array($currentRoleSlug, $noManagerRoles) ? 'hidden-field' : 'visible-field' }} uf-field">
                <label class="uf-label" id="managerLabel">Manager <span class="opt">(optional)</span></label>
                <select name="manager_id" id="managerSelect" class="uf-input uf-select">
                    <option value="">— No Manager —</option>
                    @foreach($managers as $manager)
                    <option value="{{ $manager->id }}"
                            data-role-slug="{{ $manager->role->slug }}"
                            {{ old('manager_id', $user?->manager_id) == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }}
                        <span style="color:#9ca3af;">· {{ $manager->getDisplayRoleName() }}</span>
                    </option>
                    @endforeach
                </select>
                <div id="managerHint" class="uf-manager-hint" style="display:none;">
                    <i class="fas fa-info-circle"></i>
                    <span id="managerHintText"></span>
                </div>
            </div>

            {{-- ── Status ──────────────────────────────────── --}}
            <div class="uf-section-label" style="margin-top:8px;">
                <i class="fas fa-toggle-on" style="color:#205A44;"></i> Account Status
            </div>

            <div class="uf-toggle-row">
                <div>
                    <div class="uf-toggle-label">Active User</div>
                    <div class="uf-toggle-sub">Inactive users cannot log in to the CRM</div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $user ? $user->is_active : true) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            {{-- ── Actions ─────────────────────────────────── --}}
            <div class="uf-actions">
                <a href="{{ route('users.index') }}" class="uf-btn-cancel">
                    <i class="fas fa-arrow-left"></i> Cancel
                </a>
                <button type="submit" class="uf-btn-save">
                    <i class="fas fa-{{ $user ? 'save' : 'user-plus' }}"></i>
                    {{ $user ? 'Update User' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const noManagerSlugs = @json($noManagerRoles);
const managerRoleMap = @json($managerRoleMap);

const managerHints = {
    'senior_manager':          'Select the Sales Manager this person reports to.',
    'assistant_sales_manager': 'Select any higher manager from Senior Manager up to Admin.',
    'sales_executive':         'Select the Assistant Sales Manager this person reports to.',
};

/* ── Custom Role Dropdown ───────────────────── */
function toggleRoleDropdown() {
    const trigger  = document.getElementById('roleTrigger');
    const dropdown = document.getElementById('roleDropdown');
    const isOpen   = dropdown.classList.contains('open');
    if (isOpen) { closeRoleDropdown(); }
    else { trigger.classList.add('open'); dropdown.classList.add('open'); }
}

function closeRoleDropdown() {
    document.getElementById('roleTrigger').classList.remove('open');
    document.getElementById('roleDropdown').classList.remove('open');
}

function selectRole(el, id, slug, name, icon, bg, color, desc) {
    /* Update hidden input */
    document.getElementById('roleIdInput').value = id;

    /* Update trigger display */
    document.getElementById('triggerIcon').style.background = bg;
    document.getElementById('triggerIcon').innerHTML = `<i class="fas ${icon}" style="color:${color};"></i>`;
    document.getElementById('triggerContent').innerHTML =
        `<div class="role-trigger-name">${name}</div><div class="role-trigger-desc">${desc}</div>`;

    /* Mark selected option */
    document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');

    closeRoleDropdown();
    handleRoleLogic(slug);
}

/* Close on outside click */
document.addEventListener('click', function(e) {
    if (!document.getElementById('rolePicker').contains(e.target)) closeRoleDropdown();
});

/* ── Manager show/hide logic ────────────────── */
function handleRoleLogic(slug) {
    const mField   = document.getElementById('manager-field');
    const mHint    = document.getElementById('managerHint');
    const mHintTxt = document.getElementById('managerHintText');
    const mSelect  = document.getElementById('managerSelect');
    const mLabel   = document.getElementById('managerLabel');
    const roleHint = document.getElementById('roleHint');

    if (!slug || noManagerSlugs.includes(slug)) {
        mField.classList.remove('visible-field');
        mField.classList.add('hidden-field');
        mSelect.value = '';
        mHint.style.display = 'none';
        roleHint.style.display = 'none';
        return;
    }

    mField.classList.remove('hidden-field');
    mField.classList.add('visible-field');

    /* Filter manager options by role */
    const allowed = managerRoleMap[slug] || null;
    Array.from(mSelect.options).forEach(opt => {
        if (!opt.value) return;
        const show = !allowed || allowed.includes(opt.dataset.roleSlug);
        opt.style.display = show ? '' : 'none';
        if (!show && opt.selected) { opt.selected = false; mSelect.value = ''; }
    });

    mLabel.innerHTML = 'Manager <span style="color:#ef4444;margin-left:2px;">*</span>';

    if (managerHints[slug]) {
        mHintTxt.textContent = managerHints[slug];
        mHint.style.display = 'flex';
    } else {
        mHint.style.display = 'none';
    }

    roleHint.style.display = 'none';
}

/* ── Password toggle ────────────────────────── */
function togglePw() {
    const inp  = document.getElementById('passwordInput');
    const icon = document.getElementById('pwEyeIcon');
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
}

/* ── Init (edit mode pre-select) ────────────── */
document.addEventListener('DOMContentLoaded', function () {
    const preSelected = document.querySelector('.role-option.selected');
    if (preSelected) {
        /* Simulate click to populate trigger + run manager logic */
        const fn = preSelected.getAttribute('onclick');
        if (fn) {
            /* Replace 'this' reference with actual element */
            const bound = new Function('el', fn.replace('selectRole(this,', 'selectRole(el,'));
            try { bound.call(preSelected, preSelected); } catch(e) {}
        }
    }
});
</script>
@endsection
