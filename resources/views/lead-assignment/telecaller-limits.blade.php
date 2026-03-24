@extends('layouts.app')

@section('title', 'Sales Executive Daily Limits - Base CRM')
@section('page-title', 'Sales Executive Daily Limits')
@section('page-subtitle', 'Manage daily assignment limits for sales executives')

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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sales Executive</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overall Daily Limit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned Today</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Pending</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($telecallers as $telecaller)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $telecaller['name'] }}</div>
                                <div class="text-sm text-gray-500">{{ $telecaller['email'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $telecaller['overall_daily_limit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $telecaller['assigned_count_today'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ ($telecaller['overall_daily_limit'] - $telecaller['assigned_count_today']) > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $telecaller['overall_daily_limit'] - $telecaller['assigned_count_today'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $telecaller['max_pending_leads'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editLimit({{ $telecaller['id'] }}, {{ $telecaller['overall_daily_limit'] }}, {{ $telecaller['max_pending_leads'] }})" 
                                        class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Daily Limit</h3>
            <form id="edit-form">
                <input type="hidden" id="edit-user-id">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Overall Daily Limit</label>
                    <input type="number" id="edit-daily-limit" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Pending Leads</label>
                    <input type="number" id="edit-max-pending" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">Save</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function editLimit(userId, dailyLimit, maxPending) {
            document.getElementById('edit-user-id').value = userId;
            document.getElementById('edit-daily-limit').value = dailyLimit;
            document.getElementById('edit-max-pending').value = maxPending;
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-modal').classList.add('hidden');
        }

        document.getElementById('edit-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const userId = document.getElementById('edit-user-id').value;
            const dailyLimit = document.getElementById('edit-daily-limit').value;
            const maxPending = document.getElementById('edit-max-pending').value;

            axios.post('{{ route("lead-assignment.telecaller-limits.save") }}', {
                user_id: userId,
                overall_daily_limit: dailyLimit,
                max_pending_leads: maxPending
            })
            .then(response => {
                alert(response.data.message);
                window.location.reload();
            })
            .catch(error => {
                alert('Error: ' + (error.response?.data?.message || 'Failed to update limit'));
            });
        });
    </script>
    @endpush
@endsection

