@extends('layouts.app')

@section('title', 'Send Broadcast - Admin')
@section('page-title', 'Send Broadcast Message')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form id="broadcastForm">
        @csrf
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Title <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="broadcastTitle" 
                    name="title" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#063A1C]"
                    placeholder="Enter broadcast title"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Message <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="broadcastMessage" 
                    name="message" 
                    required
                    rows="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#063A1C]"
                    placeholder="Enter your message here..."
                ></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Target Audience <span class="text-red-500">*</span>
                </label>
                <select 
                    id="targetType" 
                    name="target_type" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#063A1C]"
                    onchange="toggleRoleSelection()"
                >
                    <option value="all_users">All Users</option>
                    <option value="role_based">Specific Roles</option>
                </select>
            </div>

            <div id="roleSelection" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Roles
                </label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="target_roles[]" value="admin" class="mr-2">
                        <span>Admin</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="target_roles[]" value="crm" class="mr-2">
                        <span>CRM</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="target_roles[]" value="sales_manager" class="mr-2">
                        <span>Senior Manager</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="target_roles[]" value="sales_executive" class="mr-2">
                        <span>Sales Executive</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="target_roles[]" value="telecaller" class="mr-2">
                        <span>Sales Executive</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-4">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium"
                >
                    Send Broadcast
                </button>
                <button 
                    type="button" 
                    onclick="previewBroadcast()"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium"
                >
                    Preview
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden" onclick="closePreview()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6" onclick="event.stopPropagation()">
            <h3 class="text-xl font-bold mb-4">Preview Broadcast</h3>
            <div id="previewContent" class="space-y-4"></div>
            <div class="mt-6 flex justify-end">
                <button 
                    onclick="closePreview()"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';

function toggleRoleSelection() {
    const targetType = document.getElementById('targetType').value;
    const roleSelection = document.getElementById('roleSelection');
    roleSelection.style.display = targetType === 'role_based' ? 'block' : 'none';
}

function previewBroadcast() {
    const title = document.getElementById('broadcastTitle').value;
    const message = document.getElementById('broadcastMessage').value;
    const targetType = document.getElementById('targetType').value;
    
    if (!title || !message) {
        alert('Please fill in title and message');
        return;
    }
    
    const previewContent = document.getElementById('previewContent');
    let targetText = targetType === 'all_users' ? 'All Users' : 'Selected Roles';
    
    if (targetType === 'role_based') {
        const selectedRoles = Array.from(document.querySelectorAll('input[name="target_roles[]"]:checked'))
            .map(cb => cb.nextElementSibling.textContent);
        targetText = selectedRoles.length > 0 ? selectedRoles.join(', ') : 'No roles selected';
    }
    
    previewContent.innerHTML = `
        <div>
            <strong>Title:</strong>
            <p class="mt-1">${title}</p>
        </div>
        <div>
            <strong>Message:</strong>
            <p class="mt-1 whitespace-pre-wrap">${message}</p>
        </div>
        <div>
            <strong>Target:</strong>
            <p class="mt-1">${targetText}</p>
        </div>
    `;
    
    document.getElementById('previewModal').classList.remove('hidden');
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}

document.getElementById('broadcastForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        title: document.getElementById('broadcastTitle').value,
        message: document.getElementById('broadcastMessage').value,
        target_type: document.getElementById('targetType').value,
    };
    
    if (formData.target_type === 'role_based') {
        const selectedRoles = Array.from(document.querySelectorAll('input[name="target_roles[]"]:checked'))
            .map(cb => cb.value);
        if (selectedRoles.length === 0) {
            alert('Please select at least one role');
            return;
        }
        formData.target_roles = selectedRoles;
    }
    
    try {
        const response = await fetch('/api/broadcast/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${API_TOKEN}`,
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(formData),
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Broadcast sent successfully to ' + data.data.sent_to + ' users!');
            document.getElementById('broadcastForm').reset();
            toggleRoleSelection();
        } else {
            alert('Error: ' + (data.message || 'Failed to send broadcast'));
        }
    } catch (error) {
        console.error('Error sending broadcast:', error);
        alert('Error sending broadcast. Please try again.');
    }
});
</script>
@endsection
