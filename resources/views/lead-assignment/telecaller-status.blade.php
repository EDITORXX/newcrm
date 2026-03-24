@extends('layouts.app')

@section('title', 'Sales Executive Status - Base CRM')
@section('page-title', 'Sales Executive Status')
@section('page-subtitle', 'Manage sales executive availability and status')

@section('header-actions')
    <a href="{{ route('lead-assignment.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 text-sm font-medium">
        ← Back
    </a>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending Leads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Pending</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Can Receive</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $user['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $user['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $user['role'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $user['is_absent'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $user['is_absent'] ? 'Absent' : 'Present' }}
                                </span>
                                @if($user['absent_reason'])
                                    <div class="text-xs text-gray-500 mt-1">{{ $user['absent_reason'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user['is_telecaller'])
                                    {{ $user['pending_count'] }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($user['is_telecaller'] && $user['max_pending_leads'] !== null)
                                    {{ $user['max_pending_leads'] }}
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $user['can_receive'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user['can_receive'] ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="toggleStatus({{ $user['id'] }}, {{ $user['is_absent'] ? 'false' : 'true' }})" 
                                        class="text-indigo-600 hover:text-indigo-900">
                                    Mark {{ $user['is_absent'] ? 'Present' : 'Absent' }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Modal -->
    <div id="status-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Update User Status</h3>
            <form id="status-form">
                <input type="hidden" id="status-user-id">
                <input type="hidden" id="status-is-absent">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Absent Reason (Optional)</label>
                    <textarea id="status-reason" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Enter reason for absence"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Absent Until (Optional)</label>
                    <input type="datetime-local" id="status-until" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">Save</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function toggleStatus(userId, isAbsent) {
            document.getElementById('status-user-id').value = userId;
            document.getElementById('status-is-absent').value = isAbsent;
            document.getElementById('status-modal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('status-modal').classList.add('hidden');
        }

        document.getElementById('status-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const userId = document.getElementById('status-user-id').value;
            const isAbsent = document.getElementById('status-is-absent').value === 'true';
            const reason = document.getElementById('status-reason').value;
            const until = document.getElementById('status-until').value;

            axios.post('{{ route("lead-assignment.telecaller-status.update") }}', {
                user_id: userId,
                is_absent: isAbsent,
                absent_reason: reason || null,
                absent_until: until || null
            })
            .then(response => {
                alert(response.data.message);
                window.location.reload();
            })
            .catch(error => {
                alert('Error: ' + (error.response?.data?.message || 'Failed to update status'));
            });
        });
    </script>
    @endpush
@endsection

