@extends('layouts.app')

@section('title', 'Facebook Lead Ads - Base CRM')
@section('page-title', 'Facebook Lead Ads')

@section('header-actions')
    <a href="{{ route('integrations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back to Integrations
    </a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-5">

    @foreach(['warning'=>'amber','error'=>'red','success'=>'green'] as $type => $color)
        @if(session($type))
            <div class="p-4 bg-{{$color}}-50 border border-{{$color}}-200 rounded-lg text-{{$color}}-800">{{ session($type) }}</div>
        @endif
    @endforeach

    {{-- ── TOP CARD ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fab fa-facebook text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Facebook Lead Ads</h2>
                    <p class="text-xs text-gray-500">Webhook + Graph API • Leads auto-sync to CRM</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button onclick="openAddPageModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-plus"></i> Add New Page
                </button>
                <a href="{{ route('integrations.facebook-lead-ads.settings') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
        </div>

        {{-- Webhook URL --}}
        <div class="bg-gray-50 rounded-lg p-3 flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 mb-0.5">Webhook URL (paste in Meta App Dashboard)</p>
                <p class="text-sm font-mono text-gray-700 truncate">{{ $webhookUrl }}</p>
            </div>
            <button onclick="navigator.clipboard.writeText('{{ $webhookUrl }}');this.innerHTML='<i class=\'fas fa-check\'></i> Copied';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i> Copy',2000)"
                class="flex-shrink-0 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs text-gray-600 hover:bg-gray-100">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
    </div>

    {{-- ── PAGES & FORMS ── --}}
    @if($addedPages->isNotEmpty())
        @php $formsByPage = $forms->groupBy('fb_page_id'); @endphp

        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide px-1">Connected Pages & Forms</h3>

            @foreach($addedPages as $page)
            @php $pageForms = $formsByPage->get($page->id, collect()); @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Page Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fab fa-facebook text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ $page->page_name ?: $page->page_id }}</p>
                            <p class="text-xs text-gray-400">Page ID: {{ $page->page_id }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Connected
                        </span>
                        <a href="{{ route('integrations.facebook-lead-ads.forms', ['page_id' => $page->page_id]) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">
                            <i class="fas fa-plus"></i> Add Form
                        </a>
                        <button onclick="removePage('{{ $page->page_id }}', this)"
                            class="px-3 py-1.5 text-xs text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>

                {{-- Forms List --}}
                <div class="px-5 py-3">
                    @if($pageForms->isNotEmpty())
                        <div class="divide-y divide-gray-50">
                            @foreach($pageForms as $form)
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-wpforms text-gray-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $form->form_name ?: $form->form_id }}</p>
                                        <p class="text-xs text-gray-400">Form ID: {{ $form->form_id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $form->is_enabled ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $form->is_enabled ? 'Enabled' : 'Disabled' }}
                                    </span>
                                    <a href="{{ route('integrations.facebook-lead-ads.mapping', ['formId' => $form->form_id, 'form_name' => $form->form_name, 'page_id' => $page->page_id]) }}"
                                        class="text-xs text-blue-600 hover:underline font-medium">
                                        Edit Mapping
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-5 text-center">
                            <p class="text-sm text-gray-400 mb-3">No forms configured for this page yet.</p>
                            <a href="{{ route('integrations.facebook-lead-ads.forms', ['page_id' => $page->page_id]) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm hover:bg-blue-100">
                                <i class="fas fa-plus"></i> Select a Form to Configure
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @else
        {{-- No pages yet --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fab fa-facebook text-blue-400 text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-700 mb-1">No pages connected yet</h3>
            <p class="text-sm text-gray-400 mb-5">Add your Facebook page to start receiving leads.</p>
            <button onclick="openAddPageModal()"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                <i class="fas fa-plus"></i> Add Your First Page
            </button>
        </div>
    @endif

    {{-- ── WEBHOOK EVENTS LOG ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-satellite-dish text-purple-500"></i> Webhook Events Log
                <span class="text-xs text-gray-400 font-normal">(last 30)</span>
            </h3>
            @if(isset($webhookEvents) && $webhookEvents->isNotEmpty())
            @php
                $failedCount    = $webhookEvents->where('status','failed')->count();
                $processedCount = $webhookEvents->where('status','processed')->count();
                $receivedCount  = $webhookEvents->where('status','received')->count();
            @endphp
            <div class="flex items-center gap-2 text-xs">
                @if($processedCount) <span class="bg-green-50 text-green-600 px-2 py-1 rounded-full">✓ {{ $processedCount }} processed</span> @endif
                @if($receivedCount)  <span class="bg-yellow-50 text-yellow-600 px-2 py-1 rounded-full">⏳ {{ $receivedCount }} pending</span> @endif
                @if($failedCount)    <span class="bg-red-50 text-red-600 px-2 py-1 rounded-full">✗ {{ $failedCount }} failed</span> @endif
            </div>
            @endif
        </div>
        <div class="p-5">
            @if(isset($webhookEvents) && $webhookEvents->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">
                            <th class="pb-3 pr-4">Time</th>
                            <th class="pb-3 pr-4">Leadgen ID</th>
                            <th class="pb-3 pr-4">Status</th>
                            <th class="pb-3">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($webhookEvents as $event)
                        <tr class="hover:bg-gray-50 {{ $event->status === 'failed' ? 'bg-red-50' : '' }}">
                            <td class="py-3 pr-4 text-xs text-gray-500 whitespace-nowrap">{{ $event->created_at->format('d M, H:i:s') }}</td>
                            <td class="py-3 pr-4 font-mono text-xs text-gray-700">{{ $event->leadgen_id }}</td>
                            <td class="py-3 pr-4">
                                @if($event->status === 'processed')
                                    <span class="inline-flex items-center gap-1 text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fas fa-check-circle"></i> Processed
                                    </span>
                                @elseif($event->status === 'failed')
                                    <span class="inline-flex items-center gap-1 text-xs bg-red-50 text-red-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fas fa-times-circle"></i> Failed
                                    </span>
                                @elseif($event->status === 'received')
                                    <span class="inline-flex items-center gap-1 text-xs bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-full font-medium">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">{{ $event->status }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-xs text-red-600 max-w-xs">
                                {{ $event->error ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-6">
                <i class="fas fa-satellite-dish text-gray-200 text-3xl mb-2"></i>
                <p class="text-gray-400 text-sm">No webhook events yet.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── RECENT LEADS ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Recent Leads from Meta
                <span class="text-xs text-gray-400 font-normal">(last 20)</span>
            </h3>
            @if(isset($recentLeads) && $recentLeads->isNotEmpty())
            <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded-full">{{ $recentLeads->count() }} leads</span>
            @endif
        </div>
        <div class="p-5">
            @if(isset($recentLeads) && $recentLeads->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">
                            <th class="pb-3 pr-4">Date</th>
                            <th class="pb-3 pr-4">Form</th>
                            <th class="pb-3 pr-4">Name</th>
                            <th class="pb-3 pr-4">Email</th>
                            <th class="pb-3 pr-4">Phone</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentLeads as $lead)
                        @php
                            $data  = $lead->field_data_json ?? [];
                            $name  = $data['name'] ?? $data['full_name'] ?? '-';
                            $email = $data['email'] ?? '-';
                            $phone = $data['phone'] ?? $data['phone_number'] ?? '-';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 pr-4 text-gray-500 text-xs">{{ $lead->created_at->format('d M Y, H:i') }}</td>
                            <td class="py-3 pr-4 font-medium text-gray-800">{{ $lead->form?->form_name ?: '-' }}</td>
                            <td class="py-3 pr-4">{{ $name }}</td>
                            <td class="py-3 pr-4 text-gray-500">{{ $email }}</td>
                            <td class="py-3 pr-4 text-gray-500">{{ $phone }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-gray-200 text-4xl mb-3"></i>
                <p class="text-gray-400 text-sm">No leads yet. Leads will appear here once the webhook receives submissions.</p>
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════
     ADD NEW PAGE MODAL
══════════════════════════════════════════════ --}}
<div id="addPageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 px-4" style="display:none!important">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-screen overflow-y-auto">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                <i class="fab fa-facebook text-blue-600"></i> Add New Facebook Page
            </h3>
            <button onclick="closeAddPageModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="px-6 py-5 space-y-4">

            <div id="modal-alert" class="hidden p-3 rounded-lg text-sm"></div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Page Access Token <span class="text-red-500">*</span>
                </label>
                <textarea id="modal-token" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Paste your Page Access Token from Meta Graph Explorer..."></textarea>
                <p class="text-xs text-gray-400 mt-1">Get from: developers.facebook.com/tools/explorer → GET /me/accounts</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Graph API Version</label>
                <input type="text" id="modal-graph-version" value="{{ $settings->graph_version ?? 'v18.0' }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <button onclick="testModalConnection()"
                id="btn-modal-test"
                class="w-full py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center justify-center gap-2">
                <i class="fas fa-plug"></i> Test Connection & Fetch Pages
            </button>

            {{-- Pages Result --}}
            <div id="modal-pages-section" class="hidden">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Pages found — click to add:</h4>
                <ul id="modal-pages-list" class="space-y-2"></ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Modal open/close ─────────────────────────
function openAddPageModal() {
    document.getElementById('addPageModal').style.cssText = 'display:flex!important';
    document.getElementById('modal-token').value = '';
    document.getElementById('modal-alert').className = 'hidden p-3 rounded-lg text-sm';
    document.getElementById('modal-pages-section').classList.add('hidden');
    document.getElementById('modal-pages-list').innerHTML = '';
}
function closeAddPageModal() {
    document.getElementById('addPageModal').style.cssText = 'display:none!important';
}
document.getElementById('addPageModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddPageModal();
});

// ── Test connection inside modal ─────────────
function testModalConnection() {
    const token = document.getElementById('modal-token').value.trim();
    const ver   = document.getElementById('modal-graph-version').value.trim() || 'v18.0';
    if (!token) { showModalAlert('Please enter a Page Access Token.', 'red'); return; }

    const btn = document.getElementById('btn-modal-test');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

    fetch('{{ route("integrations.facebook-lead-ads.test-connection") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ page_access_token: token, graph_version: ver })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection & Fetch Pages';
        if (data.success) {
            showModalAlert('Connected! Select pages to add below.', 'green');
            renderModalPages(data.pages || [], token);
        } else {
            showModalAlert(data.error || 'Connection failed.', 'red');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug"></i> Test Connection & Fetch Pages';
        showModalAlert('Network error. Try again.', 'red');
    });
}

function renderModalPages(pages, token) {
    const section = document.getElementById('modal-pages-section');
    const list    = document.getElementById('modal-pages-list');

    if (!pages.length) {
        list.innerHTML = '<li class="text-sm text-gray-500">No pages found for this token.</li>';
        section.classList.remove('hidden');
        return;
    }

    list.innerHTML = pages.map(p => `
        <li class="flex items-center justify-between p-3 border border-gray-100 rounded-lg bg-gray-50">
            <div>
                <p class="text-sm font-medium text-gray-800">${p.name || p.id}</p>
                <p class="text-xs text-gray-400">ID: ${p.id}</p>
            </div>
            <button type="button"
                class="add-modal-page-btn px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700"
                data-page-id="${p.id}"
                data-page-name="${(p.name || p.id).replace(/"/g,'&quot;')}"
                data-access-token="${(p.access_token || '').replace(/"/g,'&quot;')}">
                <i class="fas fa-plus mr-1"></i> Add Page
            </button>
        </li>
    `).join('');

    section.classList.remove('hidden');

    list.querySelectorAll('.add-modal-page-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const pageId    = this.dataset.pageId;
            const pageName  = this.dataset.pageName;
            const pageToken = this.dataset.accessToken;
            if (!pageToken) { showModalAlert('No token for this page.', 'red'); return; }
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch('{{ route("integrations.facebook-lead-ads.add-page") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ page_id: pageId, page_name: pageName, page_access_token: pageToken })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    this.innerHTML = '<i class="fas fa-check"></i> Added';
                    this.classList.remove('bg-blue-600','hover:bg-blue-700');
                    this.classList.add('bg-green-500');
                    showModalAlert(pageName + ' added! Refreshing...', 'green');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-plus mr-1"></i> Add Page';
                    showModalAlert(res.message || 'Failed to add page.', 'red');
                }
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-plus mr-1"></i> Add Page';
                showModalAlert('Network error.', 'red');
            });
        });
    });
}

function showModalAlert(msg, color) {
    const el = document.getElementById('modal-alert');
    const map = { green: 'bg-green-50 border border-green-200 text-green-800', red: 'bg-red-50 border border-red-200 text-red-800' };
    el.className = 'p-3 rounded-lg text-sm ' + (map[color] || '');
    el.textContent = msg;
}

// ── Remove Page ──────────────────────────────
function removePage(pageId, btn) {
    if (!confirm('Remove this page? Configured forms will stay but token will be cleared.')) return;
    btn.disabled = true;
    fetch('{{ route("integrations.facebook-lead-ads.remove-page") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ page_id: pageId })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) location.reload();
        else { btn.disabled = false; alert(res.message || 'Failed'); }
    })
    .catch(() => { btn.disabled = false; alert('Network error.'); });
}
</script>
@endpush
@endsection
