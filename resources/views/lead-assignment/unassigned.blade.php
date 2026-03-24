@extends('layouts.app')

@section('title', 'Unassigned Leads - Base CRM')
@section('page-title', 'Unassigned Leads')
@section('page-subtitle', 'Assign leads to sales executives, sales managers, or sales executives')

@section('header-actions')
    <a href="{{ route('lead-assignment.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 text-sm font-medium">
        ← Back
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <!-- Filters -->
        <div class="mb-6">
            <form method="GET" action="{{ route('lead-assignment.unassigned') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                    <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                </select>
                <select name="source" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Sources</option>
                    <option value="website" {{ request('source') === 'website' ? 'selected' : '' }}>Website</option>
                    <option value="referral" {{ request('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                    <option value="walk_in" {{ request('source') === 'walk_in' ? 'selected' : '' }}>Walk In</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">Filter</button>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="selectAll()" class="text-sm text-indigo-600 hover:text-indigo-800">Select All</button>
                <button onclick="deselectAll()" class="text-sm text-gray-600 hover:text-gray-800">Deselect All</button>
                <span id="selected-count" class="text-sm text-gray-600">0 selected</span>
            </div>
            <div>
                <select id="bulk-telecaller" class="px-4 py-2 border border-gray-300 rounded-lg mr-2">
                    <option value="">Select User</option>
                    @if(isset($eligibleUsers))
                        @foreach($eligibleUsers as $roleName => $users)
                            <optgroup label="{{ $roleName }}">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @endif
                </select>
                <button onclick="bulkAssign()" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">Assign Selected</button>
                <button onclick="bulkDelete()" class="ml-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete Selected</button>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input type="checkbox" class="lead-checkbox" value="{{ $lead->id }}" onchange="updateSelectedCount()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $lead->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lead->phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lead->email ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $lead->status === 'new' ? 'bg-blue-100 text-blue-800' : 
                                       ($lead->status === 'contacted' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($lead->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $lead->source ?? 'N/A')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-3">
                                    <button type="button" data-lead-id="{{ $lead->id }}" class="js-assign-lead text-indigo-600 hover:text-indigo-900">Assign</button>
                                    <button type="button" data-lead-id="{{ $lead->id }}" class="js-delete-lead text-red-600 hover:text-red-800">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No unassigned leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $leads->links() }}
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assign-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign Lead</h3>
            <select id="modal-telecaller" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4">
                <option value="">Select User</option>
                @if(isset($eligibleUsers))
                    @foreach($eligibleUsers as $roleName => $users)
                        <optgroup label="{{ $roleName }}">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @endif
            </select>
            <div class="flex justify-end gap-3">
                <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button onclick="confirmAssign()" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">Assign</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        let selectedLeadId = null;

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.js-assign-lead').forEach(btn => {
                btn.addEventListener('click', () => {
                    const leadId = parseInt(btn.dataset.leadId);
                    assignSingle(leadId);
                });
            });

            document.querySelectorAll('.js-delete-lead').forEach(btn => {
                btn.addEventListener('click', () => {
                    const leadId = parseInt(btn.dataset.leadId);
                    deleteSingle(leadId);
                });
            });
        });

        function selectAll() {
            document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = true);
            document.getElementById('select-all').checked = true;
            updateSelectedCount();
        }

        function deselectAll() {
            document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('select-all').checked = false;
            updateSelectedCount();
        }

        function toggleSelectAll(checkbox) {
            document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = checkbox.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.lead-checkbox:checked').length;
            document.getElementById('selected-count').textContent = count + ' selected';
        }

        function assignSingle(leadId) {
            selectedLeadId = leadId;
            document.getElementById('assign-modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('assign-modal').classList.add('hidden');
            selectedLeadId = null;
        }

        function confirmAssign() {
            const userId = document.getElementById('modal-telecaller').value;
            if (!userId) {
                alert('Please select a user');
                return;
            }

            assignLeads([selectedLeadId], userId);
        }

        function bulkAssign() {
            const selected = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => parseInt(cb.value));
            const userId = document.getElementById('bulk-telecaller').value;

            if (selected.length === 0) {
                alert('Please select at least one lead');
                return;
            }

            if (!userId) {
                alert('Please select a user');
                return;
            }

            assignLeads(selected, userId);
        }

        function deleteSingle(leadId) {
            if (!confirm('Delete this lead?')) return;
            deleteLeads([leadId]);
        }

        function bulkDelete() {
            const selected = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => parseInt(cb.value));
            if (selected.length === 0) {
                alert('Please select at least one lead');
                return;
            }
            if (!confirm(`Delete ${selected.length} selected lead(s)?`)) return;
            deleteLeads(selected);
        }

        function assignLeads(leadIds, telecallerId) {
            axios.post('{{ route("lead-assignment.assign") }}', {
                lead_ids: leadIds,
                telecaller_id: telecallerId
            })
            .then(response => {
                alert(response.data.message);
                window.location.reload();
            })
            .catch(error => {
                alert('Error: ' + (error.response?.data?.message || 'Failed to assign leads'));
            });
        }

        function deleteLeads(leadIds) {
            axios.post('{{ route("lead-assignment.delete") }}', {
                lead_ids: leadIds
            })
            .then(response => {
                alert(response.data.message);
                window.location.reload();
            })
            .catch(error => {
                alert('Error: ' + (error.response?.data?.message || 'Failed to delete leads'));
            });
        }
    </script>
    @endpush
@endsection

