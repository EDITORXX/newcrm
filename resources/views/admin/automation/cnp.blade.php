@extends('layouts.app')

@section('title', 'ASM CNP Automation')

@section('content')
<style>
    .cnp-grid { display:grid; gap:1.5rem; grid-template-columns: 1.2fr .8fr; }
    .cnp-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 10px 30px rgba(6,58,28,.06); }
    .cnp-card-header { padding:1.25rem 1.5rem; border-bottom:1px solid #eef2f7; display:flex; justify-content:space-between; align-items:center; }
    .cnp-card-body { padding:1.5rem; }
    .cnp-form-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:1rem; }
    .cnp-field label { display:block; font-size:.875rem; font-weight:600; color:#0f172a; margin-bottom:.45rem; }
    .cnp-field input, .cnp-field select { width:100%; border:1px solid #cbd5e1; border-radius:12px; padding:.8rem .9rem; }
    .cnp-pill { display:inline-flex; align-items:center; gap:.4rem; border-radius:999px; padding:.35rem .7rem; font-size:.75rem; font-weight:700; }
    .cnp-pill.green { background:#dcfce7; color:#166534; }
    .cnp-pill.slate { background:#e2e8f0; color:#334155; }
    .cnp-user-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
    .cnp-check { border:1px solid #dbe4ee; border-radius:12px; padding:.8rem .9rem; display:flex; gap:.65rem; align-items:flex-start; }
    .cnp-table { width:100%; border-collapse:collapse; }
    .cnp-table th, .cnp-table td { text-align:left; padding:.75rem; border-bottom:1px solid #eef2f7; font-size:.875rem; vertical-align:top; }
    .cnp-actions { display:flex; justify-content:flex-end; gap:.75rem; margin-top:1.25rem; }
    .cnp-btn { border:none; border-radius:12px; padding:.85rem 1.1rem; font-weight:700; cursor:pointer; }
    .cnp-btn.primary { background:linear-gradient(135deg,#063A1C,#205A44); color:#fff; }
    .cnp-btn.secondary { background:#f8fafc; color:#334155; border:1px solid #dbe4ee; }
    .cnp-meta-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
    .cnp-stat { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:1rem; }
    .cnp-override-row { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-bottom:.75rem; }
    @media (max-width: 960px) {
        .cnp-grid, .cnp-form-grid, .cnp-meta-grid, .cnp-user-grid, .cnp-override-row { grid-template-columns:1fr; }
    }
</style>

@if(session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">{{ session('success') }}</div>
@endif

<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">ASM Fresh Lead CNP Automation</h1>
        <p class="mt-2 text-sm text-slate-500">Fresh lead CNP retries aur auto-transfer yahin se control honge. Existing source lead distribution rules untouched rahenge.</p>
    </div>
    <span class="cnp-pill {{ $config->is_enabled && $config->is_active ? 'green' : 'slate' }}">
        {{ $config->is_enabled && $config->is_active ? 'Active' : 'Disabled' }}
    </span>
</div>

<div class="cnp-grid">
    <div class="cnp-card">
        <div class="cnp-card-header">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Automation Rules</h2>
                <p class="text-sm text-slate-500">Retry delay, transfer threshold, pool aur override mapping.</p>
            </div>
        </div>
        <div class="cnp-card-body">
            <form method="POST" action="{{ route('admin.automation.cnp.update') }}">
                @csrf
                <div class="cnp-form-grid">
                    <div class="cnp-field">
                        <label><input type="checkbox" name="is_enabled" value="1" {{ $config->is_enabled ? 'checked' : '' }}> Enable automation</label>
                    </div>
                    <div class="cnp-field">
                        <label><input type="checkbox" name="is_active" value="1" {{ $config->is_active ? 'checked' : '' }}> Allow transfers</label>
                    </div>
                    <div class="cnp-field">
                        <label for="retry_delay_minutes">Retry delay (minutes)</label>
                        <input id="retry_delay_minutes" type="number" name="retry_delay_minutes" min="5" value="{{ old('retry_delay_minutes', $config->retry_delay_minutes) }}">
                    </div>
                    <div class="cnp-field">
                        <label for="transfer_threshold_hours">Transfer threshold (hours)</label>
                        <input id="transfer_threshold_hours" type="number" name="transfer_threshold_hours" min="1" value="{{ old('transfer_threshold_hours', $config->transfer_threshold_hours) }}">
                    </div>
                    <div class="cnp-field">
                        <label for="max_cnp_attempts">Max fresh-lead CNP attempts</label>
                        <input id="max_cnp_attempts" type="number" name="max_cnp_attempts" min="1" max="10" value="{{ old('max_cnp_attempts', $config->max_cnp_attempts) }}">
                    </div>
                    <div class="cnp-field">
                        <label for="fallback_routing">Fallback routing</label>
                        <select id="fallback_routing" name="fallback_routing">
                            <option value="round_robin" {{ old('fallback_routing', $config->fallback_routing) === 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Eligible ASM Pool</h3>
                    <div class="cnp-user-grid">
                        @foreach($asmUsers as $user)
                            <label class="cnp-check">
                                <input type="checkbox" name="pool_user_ids[]" value="{{ $user->id }}" {{ $config->poolUsers->contains('user_id', $user->id) ? 'checked' : '' }}>
                                <span>
                                    <span class="block font-semibold text-slate-900">{{ $user->name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $user->email }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Per-user override</h3>
                    @php $existingOverrides = $config->overrides->values(); @endphp
                    @for($i = 0; $i < max(4, $existingOverrides->count()); $i++)
                        <div class="cnp-override-row">
                            <div class="cnp-field">
                                <label>From ASM</label>
                                <select name="overrides[{{ $i }}][from_user_id]">
                                    <option value="">Select user</option>
                                    @foreach($asmUsers as $user)
                                        <option value="{{ $user->id }}" {{ (string) old("overrides.$i.from_user_id", $existingOverrides[$i]->from_user_id ?? '') === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="cnp-field">
                                <label>Transfer to</label>
                                <select name="overrides[{{ $i }}][to_user_id]">
                                    <option value="">Select user</option>
                                    @foreach($asmUsers as $user)
                                        <option value="{{ $user->id }}" {{ (string) old("overrides.$i.to_user_id", $existingOverrides[$i]->to_user_id ?? '') === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="cnp-actions">
                    <a href="{{ route('admin.automation.index') }}" class="cnp-btn secondary">Back</a>
                    <button type="submit" class="cnp-btn primary">Save CNP Automation</button>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="cnp-card">
            <div class="cnp-card-header">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Live Summary</h2>
                    <p class="text-sm text-slate-500">Current operational snapshot.</p>
                </div>
            </div>
            <div class="cnp-card-body">
                <div class="cnp-meta-grid">
                    <div class="cnp-stat">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Retry Delay</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $config->retry_delay_minutes }}m</div>
                    </div>
                    <div class="cnp-stat">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transfer Threshold</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $config->transfer_threshold_hours }}h</div>
                    </div>
                    <div class="cnp-stat">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Max CNP</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $config->max_cnp_attempts }}</div>
                    </div>
                    <div class="cnp-stat">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pool Size</div>
                        <div class="mt-2 text-2xl font-bold text-slate-900">{{ $config->poolUsers->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cnp-card">
            <div class="cnp-card-header">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Recent States</h2>
                    <p class="text-sm text-slate-500">Pending, cancelled, transferred lifecycles.</p>
                </div>
            </div>
            <div class="cnp-card-body" style="max-height:360px; overflow:auto;">
                <table class="cnp-table">
                    <thead>
                        <tr><th>Lead</th><th>Status</th><th>CNP</th></tr>
                    </thead>
                    <tbody>
                        @forelse($activeStates as $state)
                            <tr>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ $state->lead->name ?? 'Lead' }}</div>
                                    <div class="text-xs text-slate-500">{{ $state->currentAssignee->name ?? '-' }}</div>
                                </td>
                                <td><span class="cnp-pill {{ $state->status === 'active' ? 'green' : 'slate' }}">{{ ucfirst($state->status) }}</span></td>
                                <td>{{ $state->cnp_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-slate-500">No automation states yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="cnp-card">
            <div class="cnp-card-header">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Admin Audit</h2>
                    <p class="text-sm text-slate-500">Previous-owner history sirf yahin visible hai.</p>
                </div>
            </div>
            <div class="cnp-card-body" style="max-height:360px; overflow:auto;">
                <table class="cnp-table">
                    <thead>
                        <tr><th>Action</th><th>Lead</th><th>Users</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentAudits as $audit)
                            <tr>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ ucwords(str_replace('_', ' ', $audit->action)) }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($audit->acted_at)->format('d M Y, h:i A') }}</div>
                                </td>
                                <td>{{ $audit->lead->name ?? 'Lead' }}</td>
                                <td class="text-xs text-slate-600">
                                    From: {{ $audit->fromUser->name ?? '-' }}<br>
                                    To: {{ $audit->toUser->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-slate-500">No audit entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
