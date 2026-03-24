@extends('layouts.app')

@section('title', 'Lead Automation Rules')

@section('header-actions')
    <a href="{{ route('admin.automation.create') }}"
       class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-plus mr-1.5"></i> New Rule
    </a>
@endsection

@section('content')

<style>
    .auto-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        max-width: 100%;
    }
    @media (max-width: 640px) {
        .auto-grid { grid-template-columns: 1fr; }
    }

    /* ── Rule Card — mirrors .lead-card exactly ── */
    .rule-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.10);
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        min-height: 240px;
    }
    .rule-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    .rule-card.inactive { opacity: 0.72; }

    .rule-card-header {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        margin-bottom: 0.875rem;
    }

    /* Avatar — same gradient style as lead-card-avatar */
    .rule-avatar {
        width: 48px; height: 48px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        color: white;
    }
    .rule-avatar.facebook { background: linear-gradient(135deg,#1877f2,#0a56c2); }
    .rule-avatar.pabbly   { background: linear-gradient(135deg,#ff6600,#d45200); }
    .rule-avatar.mcube    { background: linear-gradient(135deg,#7c3aed,#5b21b6); }
    .rule-avatar.all      { background: linear-gradient(135deg,#16a34a,#15803d,#166534); }
    .rule-avatar.other    { background: linear-gradient(135deg,#0891b2,#0e7490); }

    .rule-card-body { flex: 1; margin-bottom: 0.875rem; }

    .rule-meta-item {
        display: flex; align-items: center; gap: 0.4rem;
        font-size: 0.8rem; color: #6b7280;
        margin-bottom: 0.3rem;
    }
    .rule-meta-item i { color: #9ca3af; width: 14px; text-align: center; font-size: 0.72rem; }
    .rule-meta-item.task-on  { color: #16a34a; }
    .rule-meta-item.task-on i { color: #16a34a; }

    .user-chips { display: flex; flex-wrap: wrap; gap: 0.3rem; margin-top: 0.5rem; }
    .user-chip {
        display: inline-flex; align-items: center; gap: 0.25rem;
        background: #f3f4f6; border: 1px solid #e5e7eb;
        border-radius: 99px; padding: 0.15rem 0.55rem;
        font-size: 0.7rem; font-weight: 500; color: #374151;
    }
    .user-chip.more { background: #063A1C; border-color: #063A1C; color: white; }

    /* Footer — mirrors .lead-card-footer */
    .rule-card-footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    .rule-card-footer .btn-action {
        display: flex !important;
        align-items: center; justify-content: center;
        gap: 0.35rem;
        padding: 7px 10px;
        border-radius: 8px;
        font-size: 0.75rem; font-weight: 500;
        border: none; cursor: pointer;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        color: white;
    }
    .rule-card-footer .btn-action.full { grid-column: 1 / -1; }
    .btn-toggle-pause  { background: linear-gradient(to right, #d97706, #b45309); }
    .btn-toggle-pause:hover  { background: linear-gradient(to right, #b45309, #92400e); }
    .btn-toggle-play   { background: linear-gradient(to right, #16a34a, #15803d); }
    .btn-toggle-play:hover   { background: linear-gradient(to right, #15803d, #166534); }
    .btn-edit  { background: linear-gradient(to right, #2563eb, #1d4ed8); }
    .btn-edit:hover  { background: linear-gradient(to right, #1d4ed8, #1e40af); }
    .btn-delete { background: linear-gradient(to right, #dc2626, #b91c1c); }
    .btn-delete:hover { background: linear-gradient(to right, #b91c1c, #991b1b); }

    @media (max-width: 767px) {
        .rule-card { padding: 1rem; min-height: auto; }
        .rule-card-footer .btn-action { padding: 6px 4px !important; font-size: 11px !important; border-radius: 6px !important; }
    }
</style>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
            <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 font-bold">&times;</button>
        </div>
    @endif

    @if($rules->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-50 flex items-center justify-center">
                <i class="fas fa-magic text-2xl text-[#205A44]"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-900 mb-2">Koi automation rule nahi hai</h5>
            <p class="text-sm text-gray-500 mb-5">Facebook, Pabbly, MCube ya kisi bhi source ke leads ke liye<br>auto-assignment rule banao.</p>
            <a href="{{ route('admin.automation.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium shadow-md">
                <i class="fas fa-plus"></i> Pehla Rule Banao
            </a>
        </div>

    @else
        <div class="auto-grid mb-6">
            @foreach($rules as $rule)
            @php
                $sourceClass = match($rule->source) {
                    'facebook_lead_ads' => 'facebook',
                    'pabbly'            => 'pabbly',
                    'mcube'             => 'mcube',
                    'all'               => 'all',
                    default             => 'other',
                };
                $sourceIcon = match($rule->source) {
                    'facebook_lead_ads' => 'fab fa-facebook',
                    'pabbly'            => 'fas fa-bolt',
                    'mcube'             => 'fas fa-phone',
                    'all'               => 'fas fa-globe',
                    default             => 'fas fa-table',
                };
            @endphp

            <div class="rule-card {{ !$rule->is_active ? 'inactive' : '' }}">

                {{-- Card Header --}}
                <div class="rule-card-header">
                    <div class="rule-avatar {{ $sourceClass }}">
                        <i class="{{ $sourceIcon }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 truncate">{{ $rule->name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            @if($rule->is_active)
                                <span class="inline-flex items-center gap-1 text-green-600 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-gray-400 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span> Inactive
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="rule-card-body">
                    <div class="rule-meta-item">
                        <i class="fas fa-layer-group"></i>
                        {{ \App\Models\SourceAutomationRule::getSourceLabel($rule->source) }}
                    </div>
                    @if($rule->fbForm)
                    <div class="rule-meta-item">
                        <i class="fas fa-file-alt"></i>
                        {{ $rule->fbForm->form_name ?? $rule->fbForm->form_id }}
                    </div>
                    @endif
                    <div class="rule-meta-item">
                        <i class="fas fa-random"></i>
                        {{ \App\Models\SourceAutomationRule::getMethodLabel($rule->assignment_method) }}
                    </div>
                    <div class="rule-meta-item {{ $rule->auto_create_task ? 'task-on' : '' }}">
                        <i class="fas {{ $rule->auto_create_task ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        Task {{ $rule->auto_create_task ? 'ON' : 'OFF' }}
                    </div>
                    @if($rule->daily_limit)
                    <div class="rule-meta-item">
                        <i class="fas fa-tachometer-alt"></i>
                        Limit: {{ $rule->daily_limit }}/day
                    </div>
                    @endif

                    {{-- User chips --}}
                    @if($rule->assignment_method === 'single_user' && $rule->singleUser)
                        <div class="user-chips">
                            <span class="user-chip">
                                <i class="fas fa-user" style="font-size:0.6rem;"></i>
                                {{ $rule->singleUser->name }}
                            </span>
                        </div>
                    @elseif($rule->users->isNotEmpty())
                        <div class="user-chips">
                            @foreach($rule->users->take(4) as $ru)
                                <span class="user-chip">
                                    {{ $ru->user->name ?? 'Unknown' }}
                                    @if($ru->percentage)<span style="opacity:0.55"> {{ $ru->percentage }}%</span>@endif
                                </span>
                            @endforeach
                            @if($rule->users->count() > 4)
                                <span class="user-chip more">+{{ $rule->users->count() - 4 }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="rule-card-footer">
                    <button class="btn-action btn-toggle-rule {{ $rule->is_active ? 'btn-toggle-pause' : 'btn-toggle-play' }}"
                            data-id="{{ $rule->id }}" data-active="{{ $rule->is_active ? 1 : 0 }}"
                            title="{{ $rule->is_active ? 'Deactivate' : 'Activate' }}">
                        <i class="fas {{ $rule->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                        {{ $rule->is_active ? 'Pause' : 'Activate' }}
                    </button>

                    <a href="{{ route('admin.automation.edit', $rule) }}" class="btn-action btn-edit">
                        <i class="fas fa-pen"></i> Edit
                    </a>

                    <button class="btn-action btn-delete btn-delete-rule full"
                            data-id="{{ $rule->id }}" data-name="{{ $rule->name }}">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                    <a href="{{ route('admin.automation.history', $rule) }}"
                       class="btn-action full"
                       style="background:linear-gradient(to right,#0369a1,#0284c7);color:#fff;text-decoration:none;text-align:center;">
                        <i class="fas fa-history"></i> History
                    </a>
                </div>

            </div>
            @endforeach
        </div>
    @endif

<form id="deleteForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-toggle-rule').forEach(btn => {
    btn.addEventListener('click', function () {
        fetch(`/admin/automation/${this.dataset.id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(() => location.reload())
        .catch(e => alert('Error: ' + e));
    });
});

document.querySelectorAll('.btn-delete-rule').forEach(btn => {
    btn.addEventListener('click', function () {
        if (!confirm(`"${this.dataset.name}" ko delete karo?`)) return;
        const form = document.getElementById('deleteForm');
        form.action = `/admin/automation/${this.dataset.id}`;
        form.submit();
    });
});
</script>
@endpush