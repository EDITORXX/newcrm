@extends('layouts.app')

@section('title', 'Meta Sheet Configuration - Base CRM')
@section('page-title', 'Meta Sheet Configuration')

@section('header-actions')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('integrations.google-sheet-import-monitor') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
            <i class="fas fa-heartbeat mr-2"></i>
            Import Monitor
        </a>
        <a href="{{ route('integrations.meta-sheet.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
            <i class="fas fa-plus mr-2"></i>
            Add New Meta Sheet
        </a>
        <button type="button" onclick="document.getElementById('metaSheetGuideModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3 py-2 border border-[#205A44] text-[#063A1C] rounded-lg hover:bg-[#063A1C] hover:text-white transition-colors duration-200 text-sm font-medium" title="Step-by-step guide to connect Meta/Facebook leads via Google Sheets">
            <i class="fas fa-info-circle"></i>
            Connection guide
        </button>
    </div>
@endsection

@section('content')
@include('integrations.meta-sheet-guide-modal')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Configured Meta/Facebook Sheets</h2>
        
        @if($configs->isEmpty())
            <div class="text-center py-12">
                <i class="fab fa-facebook text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500 mb-4">No Meta/Facebook sheet integrations configured yet.</p>
                <a href="{{ route('integrations.meta-sheet.create') }}" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium inline-block">
                    <i class="fas fa-plus mr-2"></i>
                    Add Your First Meta Sheet
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sheet Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mappings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Sync</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($configs as $config)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fab fa-facebook text-blue-600 mr-2"></i>
                                        <span class="text-sm font-medium text-gray-900">{{ $config->sheet_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        @if($config->is_draft)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-save mr-1"></i> Draft
                                            </span>
                                        @elseif($config->is_active)
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($config->columnMappings && $config->columnMappings->count() > 0)
                                        {{ $config->columnMappings->count() }} fields mapped
                                    @else
                                        <span class="text-gray-400">Not mapped yet</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $config->last_sync_at ? $config->last_sync_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($config->is_draft)
                                            <a href="{{ route('integrations.meta-sheet.step' . $config->resume_step, $config->id) }}" 
                                               class="text-blue-600 hover:text-blue-900" 
                                               title="Resume Setup">
                                                <i class="fas fa-play mr-1"></i> Resume
                                            </a>
                                            <button type="button" class="js-meta-delete text-red-600 hover:text-red-800" data-config-id="{{ $config->id }}" title="Delete Sheet">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        @else
                                            <button type="button" class="js-meta-test text-indigo-600 hover:text-indigo-900" data-config-id="{{ $config->id }}" title="Test Integration">
                                                <i class="fas fa-vial"></i> Test
                                            </button>
                                            <a href="{{ route('integrations.meta-sheet.step2', $config->id) }}"
                                               class="text-blue-600 hover:text-blue-900"
                                               title="Edit Configuration">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="js-meta-sync text-green-700 hover:text-green-900" data-config-id="{{ $config->id }}" title="Sync Leads">
                                                <i class="fas fa-sync-alt"></i> Sync
                                            </button>
                                            <button type="button" class="js-meta-toggle text-yellow-600 hover:text-yellow-900" data-config-id="{{ $config->id }}" title="Toggle Status">
                                                <i class="fas fa-toggle-{{ $config->is_active ? 'on' : 'off' }}"></i>
                                            </button>
                                            <button type="button" class="js-meta-delete text-red-600 hover:text-red-800" data-config-id="{{ $config->id }}" title="Delete Sheet">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
const META_SHEET_CSRF_TOKEN = '{{ csrf_token() }}';

function testIntegration(id) {
    if (!confirm('This will send a test lead to CRM. Continue?')) {
        return;
    }
    
    fetch(`/integrations/meta-sheet/test/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': META_SHEET_CSRF_TOKEN,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test successful! Lead ID: ' + (data.lead_id || 'N/A'));
        } else {
            alert('Test failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Test failed: ' + error.message);
    });
}

function toggleIntegration(id) {
    fetch(`/integrations/meta-sheet/toggle/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': META_SHEET_CSRF_TOKEN,
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to toggle integration');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to toggle integration');
    });
}

function syncLeads(id, btnEl) {
    if (!confirm('Sync leads from this sheet now?')) {
        return;
    }

    const originalHtml = btnEl.innerHTML;
    btnEl.disabled = true;
    btnEl.classList.add('opacity-50');
    btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing';

    fetch(`/integrations/meta-sheet/sync/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': META_SHEET_CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Sync failed');
        }

        alert(
            'Sync complete!\n\n' +
            `New leads synced: ${data.imported}\n` +
            `Already existed: ${data.already_exists}\n` +
            `Already synced rows: ${data.already_synced}\n` +
            `Missing name/phone: ${data.missing_required}\n` +
            `Errors: ${data.errors}`
        );

        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Sync failed: ' + error.message);
    })
    .finally(() => {
        btnEl.disabled = false;
        btnEl.classList.remove('opacity-50');
        btnEl.innerHTML = originalHtml;
    });
}

function deleteSheetConfig(id) {
    if (!confirm('Delete this sheet configuration?')) {
        return;
    }

    const deleteLeads = confirm('Also delete the leads imported from this sheet?\n\nOK = Yes, delete leads\nCancel = No, keep leads');

    fetch(`/integrations/meta-sheet/delete/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': META_SHEET_CSRF_TOKEN,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ delete_leads: deleteLeads ? 1 : 0 }),
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Delete failed');
        }

        let msg = 'Deleted successfully.';
        if (deleteLeads) {
            msg += `\n\nLeads deleted: ${data.deleted_leads || 0}`;
            if (typeof data.skipped_leads !== 'undefined') {
                msg += `\nLeads kept (pre-existing/duplicate): ${data.skipped_leads || 0}`;
            }
        }

        alert(msg);
        location.reload();
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('Delete failed: ' + error.message);
    });
}

// Bind UI actions (avoids inline onclick parsing issues)
document.addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-config-id]');
    if (!btn) return;

    const id = btn.getAttribute('data-config-id');
    if (!id) return;

    if (btn.classList.contains('js-meta-test')) {
        testIntegration(id);
        return;
    }
    if (btn.classList.contains('js-meta-sync')) {
        syncLeads(id, btn);
        return;
    }
    if (btn.classList.contains('js-meta-toggle')) {
        toggleIntegration(id);
        return;
    }
    if (btn.classList.contains('js-meta-delete')) {
        deleteSheetConfig(id);
        return;
    }
});
</script>
@endpush
@endsection
