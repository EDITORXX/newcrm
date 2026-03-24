@extends('layouts.app')
@section('title', isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule')

@push('styles')
<style>
/* ─── STEP BAR ─────────────────────────────── */
.stepbar { display:flex; align-items:center; margin-bottom:24px; }
.sb-dot {
    width:32px; height:32px; border-radius:50%;
    background: linear-gradient(135deg,#063A1C,#205A44);
    color:#fff; font-size:12px; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
    box-shadow: 0 2px 6px rgba(6,58,28,.25);
}
.sb-lbl { font-size:13px; font-weight:600; color:#111827; margin-left:8px; white-space:nowrap; }
.sb-line { flex:1; height:2px; background:#e5e7eb; margin:0 12px; }

/* ─── SECTION CARD ─────────────────────────── */
.acard {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:12px;
    margin-bottom:18px;
    overflow:hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,.06);
}
.acard:hover { box-shadow: 0 4px 12px rgba(0,0,0,.10); }
.acard-head {
    display:flex; align-items:center; gap:12px;
    padding:14px 20px;
    background:#f9fafb;
    border-bottom:1px solid #e5e7eb;
}
.acard-num {
    width:28px; height:28px; border-radius:8px;
    background: linear-gradient(135deg,#063A1C,#205A44);
    color:#fff; font-size:12px; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.acard-ttl { font-size:14px; font-weight:700; color:#111827; margin:0; line-height:1.2; }
.acard-sub { font-size:12px; color:#6b7280; margin:0; }
.acard-body { padding:20px; }

/* ─── SOURCE CARDS ─────────────────────────── */
.src-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:10px; }
@media(max-width:1100px){ .src-grid { grid-template-columns:repeat(3,1fr); } }
@media(max-width:600px){  .src-grid { grid-template-columns:repeat(2,1fr); } }
.src-item { position:relative; cursor:pointer; }
.src-item input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
.src-card {
    border:2px solid #e5e7eb; border-radius:12px;
    padding:16px 10px 13px; text-align:center;
    background:#fff; transition:all .18s; cursor:pointer;
}
.src-card:hover { border-color:#9ca3af; transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,.08); }
.src-item input:checked+.src-card { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.11); }
.src-ico {
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 8px; font-size:1.25rem;
}
.src-lbl { font-size:11px; font-weight:600; color:#111827; margin:0; }
.src-tick {
    position:absolute; top:8px; right:8px;
    width:18px; height:18px; border-radius:50%;
    display:none; align-items:center; justify-content:center;
    font-size:9px; color:#fff;
}
.src-item input:checked~.src-tick { display:flex; }

/* ─── SUB PANELS ────────────────────────────── */
.sub-panel {
    border-radius:10px; padding:14px 16px; margin-top:14px;
}
.fb-panel  { background:#eff6ff; border:1px solid #bfdbfe; }
.gs-panel  { background:#ecfdf5; border:1px solid #6ee7b7; }

/* ─── METHOD CARDS ─────────────────────────── */
.mth-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:0; }
@media(max-width:768px){ .mth-grid { grid-template-columns:repeat(2,1fr); } }
.mth-item { position:relative; cursor:pointer; }
.mth-item input[type=radio] { position:absolute; opacity:0; width:0; height:0; }
.mth-card {
    border:2px solid #e5e7eb; border-radius:11px;
    padding:14px; background:#fff; transition:all .18s;
    cursor:pointer; height:100%;
}
.mth-card:hover { border-color:#9ca3af; background:#f9fafb; }
.mth-item input:checked+.mth-card { border-color:#063A1C; background:#f0fdf4; }
.mth-ico {
    width:34px; height:34px; border-radius:9px;
    background:#f3f4f6; display:flex; align-items:center; justify-content:center;
    font-size:13px; color:#6b7280; margin-bottom:8px; transition:all .18s;
}
.mth-item input:checked+.mth-card .mth-ico { background:linear-gradient(135deg,#063A1C,#205A44); color:#fff; }
.mth-ttl { font-size:12px; font-weight:700; color:#111827; margin:0 0 3px; }
.mth-dsc { font-size:11px; color:#6b7280; margin:0; line-height:1.4; }

/* ─── USERS PANEL ───────────────────────────── */
.users-panel {
    background:#f9fafb; border:1px solid #e5e7eb;
    border-radius:12px; padding:16px; margin-top:14px;
}
.panel-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.panel-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#6b7280; margin:0; }

/* ─── USERS TABLE ───────────────────────────── */
.tbl-wrap { border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff; }
.usr-tbl { width:100%; border-collapse:separate; border-spacing:0; }
.usr-tbl thead th {
    background:#f9fafb; font-size:10px; font-weight:700;
    text-transform:uppercase; letter-spacing:.5px; color:#9ca3af;
    padding:8px 12px; border-bottom:1px solid #e5e7eb;
}
.usr-tbl tbody td { padding:8px 12px; vertical-align:middle; border-bottom:1px solid #f3f4f6; }
.usr-tbl tbody tr:last-child td { border-bottom:none; }
.usr-tbl tbody tr:hover td { background:#fafafa; }

/* ─── FIELD BOX ─────────────────────────────── */
.field-box { background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:14px 15px; }
.field-box-label {
    font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:.6px; color:#6b7280; margin-bottom:6px;
}
.field-box-hint { font-size:11px; color:#9ca3af; margin-top:5px; }

/* ─── FORM CONTROLS (Tailwind override for consistency) ── */
.f-input, .f-select {
    width:100%; padding:8px 12px;
    border:1px solid #d1d5db; border-radius:8px;
    font-size:13px; color:#111827; background:#fff;
    transition: border-color .15s, box-shadow .15s;
    outline:none;
}
.f-input:focus, .f-select:focus {
    border-color:#063A1C;
    box-shadow: 0 0 0 3px rgba(6,58,28,.1);
}
.f-input.is-invalid, .f-select.is-invalid { border-color:#dc2626; }
.f-input-sm { padding:6px 10px; font-size:12px; }
.input-addon {
    display:flex; align-items:stretch;
}
.input-addon .f-input { border-radius:8px 0 0 8px; }
.input-addon-text {
    padding:0 10px; background:#f3f4f6; border:1px solid #d1d5db;
    border-left:none; border-radius:0 8px 8px 0;
    display:flex; align-items:center;
    font-size:12px; color:#6b7280; white-space:nowrap;
}

/* ─── TOGGLE ROW ────────────────────────────── */
.tog {
    display:flex; align-items:flex-start; gap:14px;
    padding:14px 15px; border-radius:10px;
    border:1px solid #e5e7eb; background:#fff;
    cursor:pointer; transition:background .15s;
}
.tog:hover { background:#f9fafb; }
.tog+.tog { margin-top:10px; }
.tog-ttl { font-size:13px; font-weight:600; color:#111827; margin:0 0 2px; }
.tog-dsc { font-size:12px; color:#6b7280; margin:0; }

/* ─── SUBMIT ────────────────────────────────── */
.submit-row {
    display:flex; gap:10px;
    padding-top:18px; border-top:1px solid #e5e7eb; margin-top:18px;
}

/* ─── BACK BUTTON ───────────────────────────── */
.btn-back {
    width:36px; height:36px; border-radius:50%;
    border:1px solid #d1d5db; background:#fff;
    display:flex; align-items:center; justify-content:center;
    color:#374151; font-size:12px; cursor:pointer;
    text-decoration:none; transition:all .15s; flex-shrink:0;
}
.btn-back:hover { background:#f3f4f6; border-color:#9ca3af; color:#111827; text-decoration:none; }

/* ─── BUTTONS ───────────────────────────────── */
.btn-primary-green {
    display:inline-flex; align-items:center; gap:8px;
    padding:9px 20px;
    background:linear-gradient(to right,#063A1C,#205A44);
    color:#fff; border:none; border-radius:8px;
    font-size:13px; font-weight:600; cursor:pointer;
    transition:opacity .15s, transform .15s;
    box-shadow:0 2px 6px rgba(6,58,28,.25);
    text-decoration:none;
}
.btn-primary-green:hover { opacity:.9; transform:translateY(-1px); color:#fff; text-decoration:none; }

.btn-outline-cancel {
    display:inline-flex; align-items:center; gap:8px;
    padding:9px 20px;
    background:#fff; color:#374151;
    border:1px solid #d1d5db; border-radius:8px;
    font-size:13px; font-weight:500; cursor:pointer;
    transition:background .15s; text-decoration:none;
}
.btn-outline-cancel:hover { background:#f3f4f6; color:#111827; text-decoration:none; }

.btn-add-user {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px;
    background:linear-gradient(to right,#063A1C,#205A44);
    color:#fff; border:none; border-radius:7px;
    font-size:12px; font-weight:600; cursor:pointer;
    transition:opacity .15s;
}
.btn-add-user:hover { opacity:.88; }

.btn-remove-row {
    background:none; border:none; cursor:pointer;
    color:#9ca3af; padding:4px; border-radius:5px;
    transition:color .15s, background .15s;
    line-height:1;
}
.btn-remove-row:hover { color:#dc2626; background:#fef2f2; }

/* ─── PCT BAR ───────────────────────────────── */
.pct-progress {
    height:7px; border-radius:6px;
    background:#e5e7eb; overflow:hidden; flex:1;
}
.pct-bar { height:100%; border-radius:6px; transition:width .3s; }

/* ─── ERROR ALERT ───────────────────────────── */
.error-alert {
    background:#fef2f2; border:1px solid #fca5a5;
    border-radius:10px; padding:12px 16px; margin-bottom:18px;
}
.error-alert strong { color:#dc2626; font-size:13px; }
.error-alert ul { color:#b91c1c; font-size:12px; margin:4px 0 0; padding-left:16px; }
</style>
@endpush

@section('content')
<div style="width:100%; padding:0 0 40px;">

    {{-- Header --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <a href="{{ route('admin.automation.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h2 style="font-size:1.1rem; font-weight:700; color:#111827; margin:0; line-height:1.3;">
                {{ isset($rule) ? 'Edit Automation Rule' : 'New Automation Rule' }}
            </h2>
            <p style="font-size:12px; color:#6b7280; margin:0;">Lead source → distribution → task settings</p>
        </div>
    </div>

    {{-- Step Bar --}}
    <div class="stepbar">
        <div class="sb-dot">1</div><span class="sb-lbl">Lead Source</span>
        <div class="sb-line"></div>
        <div class="sb-dot">2</div><span class="sb-lbl">Distribution Logic</span>
        <div class="sb-line"></div>
        <div class="sb-dot">3</div><span class="sb-lbl">Task & Settings</span>
    </div>

    {{-- Errors --}}
    @if($errors->any())
    <div class="error-alert">
        <strong><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>Kuch errors hain:</strong>
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @php
        $selSrc    = old('source', $rule->source ?? '');
        $selMth    = old('assignment_method', $rule->assignment_method ?? 'round_robin');
        $existUsers = old('users', isset($rule) ? $rule->users->map(fn($ru)=>[
            'user_id'=>$ru->user_id,'percentage'=>$ru->percentage
        ])->toArray() : [['user_id'=>'','percentage'=>'']]);
        $isSingleLoad = $selMth === 'single_user';
        $isPctLoad    = $selMth === 'percentage';
        $sources = [
            'facebook_lead_ads'=>['label'=>'Facebook Lead Ads','icon'=>'fab fa-facebook','color'=>'#1877f2','bg'=>'#e7f0fd'],
            'pabbly'           =>['label'=>'Pabbly',           'icon'=>'fas fa-bolt',    'color'=>'#ff6600','bg'=>'#fff0e6'],
            'mcube'            =>['label'=>'MCube',            'icon'=>'fas fa-phone',   'color'=>'#6f42c1','bg'=>'#f0ebfd'],
            'google_sheets'    =>['label'=>'Google Sheets',    'icon'=>'fas fa-table',   'color'=>'#0f9d58','bg'=>'#e6f7ee'],
            'csv'              =>['label'=>'CSV Import',       'icon'=>'fas fa-file-csv','color'=>'#0077b6','bg'=>'#e6f2fb'],
            'all'              =>['label'=>'All Sources',      'icon'=>'fas fa-globe',   'color'=>'#16a34a','bg'=>'#ecfdf5'],
        ];
        $methods = [
            'round_robin'    =>['title'=>'Round Robin',    'desc'=>'Baari baari — A→B→C→A',        'icon'=>'fas fa-sync-alt'],
            'first_available'=>['title'=>'First Available','desc'=>'Jiske paas kam leads ho usko',  'icon'=>'fas fa-user-check'],
            'percentage'     =>['title'=>'Percentage',     'desc'=>'A ko 40%, B ko 30%, C ko 30%', 'icon'=>'fas fa-percent'],
            'single_user'    =>['title'=>'Single User',    'desc'=>'Sab leads ek hi user ko',      'icon'=>'fas fa-user'],
        ];
    @endphp

    <form method="POST"
          action="{{ isset($rule) ? route('admin.automation.update', $rule) : route('admin.automation.store') }}">
        @csrf
        @if(isset($rule)) @method('PUT') @endif

        {{-- ══════════════════════════════
             1  LEAD SOURCE
        ══════════════════════════════ --}}
        <div class="acard">
            <div class="acard-head">
                <div class="acard-num">1</div>
                <div>
                    <p class="acard-ttl">Lead Source</p>
                    <p class="acard-sub">Kahan se aaye leads ko is rule se handle karna hai?</p>
                </div>
            </div>
            <div class="acard-body">

                <div class="src-grid">
                    @foreach($sources as $val => $s)
                    <label class="src-item">
                        <input type="radio" name="source" value="{{ $val }}" class="src-radio"
                               {{ $selSrc === $val ? 'checked' : '' }}>
                        <div class="src-card"
                             style="{{ $selSrc===$val ? "border-color:{$s['color']};background:{$s['bg']};" : '' }}">
                            <div class="src-ico" style="background:{{ $s['bg'] }};">
                                <i class="{{ $s['icon'] }}" style="color:{{ $s['color'] }};"></i>
                            </div>
                            <p class="src-lbl">{{ $s['label'] }}</p>
                        </div>
                        <div class="src-tick" style="background:{{ $s['color'] }};">
                            <i class="fas fa-check"></i>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Facebook Panel --}}
                <div id="fbPanel" class="sub-panel fb-panel"
                     style="{{ $selSrc !== 'facebook_lead_ads' ? 'display:none;' : '' }}">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <i class="fab fa-facebook" style="color:#1877f2; font-size:14px;"></i>
                        <span style="font-size:12px; font-weight:600; color:#111827;">
                            Facebook Form <span style="color:#6b7280; font-weight:400;">(optional)</span>
                        </span>
                    </div>
                    <select name="fb_form_id" class="f-select">
                        <option value="">-- Kisi bhi Facebook form se aaye lead --</option>
                        @foreach($fbForms as $form)
                        <option value="{{ $form->id }}"
                            {{ old('fb_form_id', $rule->fb_form_id ?? '') == $form->id ? 'selected':'' }}>
                            {{ $form->page->page_name ?? 'Page' }} — {{ $form->form_name ?? $form->form_id }}
                        </option>
                        @endforeach
                    </select>
                    <p style="font-size:11px; color:#3b82f6; margin:6px 0 0;">
                        <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                        Specific form select karo to sirf us form ke leads is rule se assign honge.
                    </p>
                </div>

                {{-- Google Sheets Panel --}}
                <div id="gsPanel" class="sub-panel gs-panel"
                     style="{{ $selSrc !== 'google_sheets' ? 'display:none;' : '' }}">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <i class="fas fa-table" style="color:#0f9d58; font-size:14px;"></i>
                        <span style="font-size:12px; font-weight:600; color:#111827;">
                            Google Sheet <span style="color:#6b7280; font-weight:400;">(optional)</span>
                        </span>
                    </div>
                    <select name="google_sheet_config_id" class="f-select">
                        <option value="">-- Kisi bhi sheet se aaye lead --</option>
                        @foreach($googleSheets as $sheet)
                        <option value="{{ $sheet->id }}"
                            {{ old('google_sheet_config_id', $rule->google_sheet_config_id ?? '') == $sheet->id ? 'selected':'' }}>
                            {{ $sheet->sheet_name ?: ('Sheet #' . $sheet->id) }}
                        </option>
                        @endforeach
                    </select>
                    <p style="font-size:11px; color:#0f9d58; margin:6px 0 0;">
                        <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                        Specific sheet select karo to sirf us sheet ke leads is rule se assign honge.
                    </p>
                </div>

            </div>
        </div>

        {{-- ══════════════════════════════
             2  DISTRIBUTION
        ══════════════════════════════ --}}
        <div class="acard">
            <div class="acard-head">
                <div class="acard-num">2</div>
                <div>
                    <p class="acard-ttl">Distribution Logic</p>
                    <p class="acard-sub">Lead kaise aur kisko assign karna hai?</p>
                </div>
            </div>
            <div class="acard-body">

                <div class="mth-grid">
                    @foreach($methods as $val => $m)
                    <label class="mth-item">
                        <input type="radio" name="assignment_method" value="{{ $val }}"
                               class="mth-radio" {{ $selMth===$val ? 'checked':'' }}>
                        <div class="mth-card">
                            <div class="mth-ico"><i class="{{ $m['icon'] }}"></i></div>
                            <p class="mth-ttl">{{ $m['title'] }}</p>
                            <p class="mth-dsc">{{ $m['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Single User Panel --}}
                <div id="panelSingle" class="users-panel"
                     style="{{ !$isSingleLoad ? 'display:none;' : '' }}">
                    <p class="panel-label" style="margin-bottom:8px;">User select karo</p>
                    <select name="single_user_id" class="f-select">
                        <option value="">-- Select User --</option>
                        @foreach($salesUsers as $u)
                        <option value="{{ $u->id }}"
                            {{ old('single_user_id', $rule->single_user_id ?? '') == $u->id ? 'selected':'' }}>
                            {{ $u->name }} — {{ $u->role->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Multi Users Panel --}}
                <div id="panelMulti" class="users-panel"
                     style="{{ $isSingleLoad ? 'display:none;' : '' }}">
                    <div class="panel-head">
                        <span class="panel-label">Users configure karo</span>
                        <button type="button" id="addUserBtn" class="btn-add-user">
                            <i class="fas fa-plus"></i> User Add Karo
                        </button>
                    </div>

                    <div class="tbl-wrap">
                        <table class="usr-tbl">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th id="pctHead"
                                        style="{{ !$isPctLoad ? 'display:none;' : '' }}width:150px;">
                                        Percentage
                                    </th>
                                    <th style="width:44px;"></th>
                                </tr>
                            </thead>
                            <tbody id="usersBody">
                                @foreach($existUsers as $i => $eu)
                                <tr class="usr-row">
                                    <td>
                                        <select name="users[{{ $i }}][user_id]"
                                                class="f-select f-input-sm">
                                            <option value="">-- Select User --</option>
                                            @foreach($salesUsers as $u)
                                            <option value="{{ $u->id }}"
                                                {{ ($eu['user_id']??'')==$u->id?'selected':'' }}>
                                                {{ $u->name }} — {{ $u->role->name ?? '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="pct-col"
                                        style="{{ !$isPctLoad ? 'display:none;' : '' }}">
                                        <div class="input-addon">
                                            <input type="number"
                                                   name="users[{{ $i }}][percentage]"
                                                   class="f-input f-input-sm pct-inp"
                                                   placeholder="0" min="0" max="100" step="0.1"
                                                   value="{{ $eu['percentage'] ?? '' }}">
                                            <span class="input-addon-text">%</span>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" class="btn-remove-row">
                                            <i class="fas fa-trash-alt" style="font-size:11px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Percentage Progress --}}
                    <div id="pctWrap"
                         style="{{ !$isPctLoad ? 'display:none;' : '' }}margin-top:12px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="pct-progress">
                                <div id="pctBar" class="pct-bar"
                                     style="width:0%; background:#f59e0b;"></div>
                            </div>
                            <span style="font-size:12px; font-weight:700; min-width:80px; color:#374151;">
                                Total: <span id="pctVal">0</span>%
                            </span>
                        </div>
                        <p style="font-size:11px; color:#9ca3af; margin:4px 0 0;">
                            100% hona chahiye — abhi <span id="pctRemain">100</span>% baaki hai
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ══════════════════════════════
             3  TASK & SETTINGS
        ══════════════════════════════ --}}
        <div class="acard">
            <div class="acard-head">
                <div class="acard-num">3</div>
                <div>
                    <p class="acard-ttl">Task & Settings</p>
                    <p class="acard-sub">Rule ka naam, limits aur task configuration</p>
                </div>
            </div>
            <div class="acard-body">

                {{-- Fields row --}}
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:18px;">

                    {{-- Rule Name --}}
                    <div class="field-box" style="grid-column: 1 / 2;">
                        <div class="field-box-label">Rule Name <span style="color:#dc2626;">*</span></div>
                        <input type="text" name="name"
                               class="f-input @error('name') is-invalid @enderror"
                               placeholder="e.g. Facebook Form A → Round Robin"
                               value="{{ old('name', $rule->name ?? '') }}" required>
                        @error('name')
                            <p style="font-size:11px; color:#dc2626; margin:4px 0 0;">{{ $message }}</p>
                        @enderror
                        <p class="field-box-hint">Is rule ko ek clear naam do</p>
                    </div>

                    {{-- Daily Limit --}}
                    <div class="field-box">
                        <div class="field-box-label">Daily Limit</div>
                        <div class="input-addon">
                            <input type="number" name="daily_limit"
                                   class="f-input" placeholder="∞ Unlimited" min="1"
                                   value="{{ old('daily_limit', $rule->daily_limit ?? '') }}">
                            <span class="input-addon-text">/day</span>
                        </div>
                        <p class="field-box-hint">Max kitne leads assign honge aaj</p>
                    </div>

                    {{-- Fallback User --}}
                    <div class="field-box">
                        <div class="field-box-label">Fallback User</div>
                        <select name="fallback_user_id" class="f-select">
                            <option value="">-- None --</option>
                            @foreach($salesUsers as $u)
                            <option value="{{ $u->id }}"
                                {{ old('fallback_user_id', $rule->fallback_user_id ?? '')==$u->id?'selected':'' }}>
                                {{ $u->name }}
                            </option>
                            @endforeach
                        </select>
                        <p class="field-box-hint">Jab koi user available na ho</p>
                    </div>

                </div>

             

                {{-- Toggles --}}
                <label class="tog">
                    <div style="padding-top:2px; flex-shrink:0;">
                        <input type="checkbox" name="auto_create_task" value="1"
                               style="width:42px; height:22px; cursor:pointer; accent-color:#063A1C;"
                               {{ old('auto_create_task', $rule->auto_create_task ?? true) ? 'checked':'' }}>
                    </div>
                    <div>
                        <p class="tog-ttl">
                            <i class="fas fa-tasks" style="color:#16a34a; margin-right:6px;"></i>
                            Auto-create Calling Task
                        </p>
                        <p class="tog-dsc">
                            Lead assign hone ke baad telecaller dashboard pe calling task automatically create hoga
                        </p>
                    </div>
                </label>

                <label class="tog">
                    <div style="padding-top:2px; flex-shrink:0;">
                        <input type="checkbox" name="is_active" value="1"
                               style="width:42px; height:22px; cursor:pointer; accent-color:#063A1C;"
                               {{ old('is_active', $rule->is_active ?? true) ? 'checked':'' }}>
                    </div>
                    <div>
                        <p class="tog-ttl">
                            <i class="fas fa-circle" style="color:#2563eb; margin-right:6px;"></i>
                            Rule Active
                        </p>
                        <p class="tog-dsc">
                            Band karo to is source ke leads unassigned rahenge — koi task bhi nahi banega
                        </p>
                    </div>
                </label>

                <div class="submit-row">
                    <button type="submit" class="btn-primary-green">
                        <i class="fas fa-save"></i>
                        {{ isset($rule) ? 'Update Rule' : 'Create Rule' }}
                    </button>
                    <a href="{{ route('admin.automation.index') }}" class="btn-outline-cancel">
                        Cancel
                    </a>
                </div>

            </div>
        </div>

    </form>
</div>

{{-- Responsive grid fix --}}
<style>
@media(max-width:768px){
    div[style*="grid-template-columns:1fr 1fr 1fr"]{
        grid-template-columns:1fr 1fr !important;
    }
    div[style*="grid-template-columns:1fr 1fr 1fr"] .field-box:first-child{
        grid-column: 1 / -1 !important;
    }
}
@media(max-width:480px){
    div[style*="grid-template-columns:1fr 1fr 1fr"]{
        grid-template-columns:1fr !important;
    }
    div[style*="grid-template-columns:1fr 1fr 1fr"] .field-box:first-child{
        grid-column:auto !important;
    }
}
</style>
@endsection

@push('scripts')
<script>
/* ── Source colors ── */
const SC = {facebook_lead_ads:'#1877f2',pabbly:'#ff6600',mcube:'#6f42c1',google_sheets:'#0f9d58',csv:'#0077b6',all:'#16a34a'};
const SB = {facebook_lead_ads:'#e7f0fd',pabbly:'#fff0e6',mcube:'#f0ebfd',google_sheets:'#e6f7ee',csv:'#e6f2fb',all:'#ecfdf5'};

document.querySelectorAll('.src-radio').forEach(r => {
    r.addEventListener('change', function () {
        document.querySelectorAll('.src-card').forEach(c => { c.style.borderColor='#e5e7eb'; c.style.background='#fff'; });
        const card = this.closest('.src-item').querySelector('.src-card');
        card.style.borderColor = SC[this.value] || '#063A1C';
        card.style.background  = SB[this.value] || '#f0fdf4';
        document.getElementById('fbPanel').style.display = this.value === 'facebook_lead_ads' ? 'block' : 'none';
        document.getElementById('gsPanel').style.display = this.value === 'google_sheets'      ? 'block' : 'none';
    });
});

/* ── Method switch ── */
function showPanel(method) {
    const isSingle = method === 'single_user';
    const isPct    = method === 'percentage';
    document.getElementById('panelSingle').style.display = isSingle ? 'block' : 'none';
    document.getElementById('panelMulti').style.display  = isSingle ? 'none'  : 'block';
    document.getElementById('pctHead').style.display     = isPct ? '' : 'none';
    document.querySelectorAll('.pct-col').forEach(td => { td.style.display = isPct ? '' : 'none'; });
    document.getElementById('pctWrap').style.display     = isPct ? 'block' : 'none';
    updatePct();
}
document.querySelectorAll('.mth-radio').forEach(r => {
    r.addEventListener('change', function () { showPanel(this.value); });
});

/* ── Add user row ── */
const OPTS = `<option value="">-- Select User --</option>` +
    @json($salesUsers->map(fn($u)=>['id'=>$u->id,'n'=>$u->name.' — '.($u->role->name??'')]))
    .map(u => `<option value="${u.id}">${u.n}</option>`).join('');

let RI = {{ count($existUsers) }};
const isPct = () => document.querySelector('.mth-radio:checked')?.value === 'percentage';

document.getElementById('addUserBtn').addEventListener('click', () => {
    const pctHide = isPct() ? '' : 'none';
    const tr = document.createElement('tr');
    tr.className = 'usr-row';
    tr.innerHTML = `
        <td>
            <select name="users[${RI}][user_id]" class="f-select f-input-sm">${OPTS}</select>
        </td>
        <td class="pct-col" style="display:${pctHide}">
            <div class="input-addon">
                <input type="number" name="users[${RI}][percentage]"
                       class="f-input f-input-sm pct-inp"
                       placeholder="0" min="0" max="100" step="0.1">
                <span class="input-addon-text">%</span>
            </div>
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn-remove-row">
                <i class="fas fa-trash-alt" style="font-size:11px;"></i>
            </button>
        </td>`;
    document.getElementById('usersBody').appendChild(tr);
    tr.querySelector('.btn-remove-row').addEventListener('click', () => { tr.remove(); updatePct(); });
    RI++;
});

document.querySelectorAll('.btn-remove-row').forEach(b => {
    b.addEventListener('click', () => { b.closest('tr').remove(); updatePct(); });
});

/* ── Pct bar ── */
function updatePct() {
    let sum = 0;
    document.querySelectorAll('.pct-inp').forEach(i => { sum += parseFloat(i.value) || 0; });
    sum = Math.round(sum * 10) / 10;
    const ok = Math.abs(sum - 100) < 0.1;
    const ov = sum > 100;
    const valEl = document.getElementById('pctVal');
    valEl.textContent = sum;
    valEl.style.color = ok ? '#16a34a' : ov ? '#dc2626' : '#f59e0b';
    document.getElementById('pctRemain').textContent = Math.max(0, Math.round((100 - sum) * 10) / 10);
    const bar = document.getElementById('pctBar');
    bar.style.width      = Math.min(sum, 100) + '%';
    bar.style.background = ok ? '#16a34a' : ov ? '#dc2626' : '#f59e0b';
}
document.getElementById('usersBody').addEventListener('input', updatePct);
updatePct();
</script>
@endpush