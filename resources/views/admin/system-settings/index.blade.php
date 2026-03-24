@extends('layouts.app')

@section('title', 'System Settings - Base CRM')
@section('page-title', 'System Settings')

@section('header-actions')
    <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
    </a>
@endsection

@push('styles')
<style>
    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #E5DED4;
        padding: 24px;
        margin-bottom: 24px;
    }
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-color);
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #E5DED4;
        display: flex;
        align-items: center;
    }
    .section-title i {
        margin-right: 10px;
        color: var(--gradient-start);
    }
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 26px; width: 26px;
        left: 4px; bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
    }
    input:checked + .slider:before { transform: translateX(26px); }
    .btn-primary {
        background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        color: white; border: none;
        padding: 10px 20px; border-radius: 8px;
        cursor: pointer; font-weight: 500;
        transition: all 0.3s; font-size: 13px;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
    .btn-cache {
        background: #f3f4f6;
        color: #374151; border: 1px solid #e5e7eb;
        padding: 10px 16px; border-radius: 8px;
        cursor: pointer; font-weight: 500;
        transition: all 0.2s; font-size: 13px;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-cache:hover {
        background: #e5e7eb;
        border-color: #9ca3af;
        transform: translateY(-1px);
    }
    .btn-cache:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-badge.active  { background: #d1fae5; color: #065f46; }
    .status-badge.inactive { background: #fee2e2; color: #991b1b; }
    .alert { padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #86efac; }
    .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .alert-info    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    .command-output {
        background: #1e1e1e; color: #d4d4d4;
        padding: 14px; border-radius: 8px;
        font-family: 'Courier New', monospace;
        font-size: 12px; max-height: 300px;
        overflow-y: auto; white-space: pre-wrap;
        word-wrap: break-word; margin-top: 12px;
    }
</style>
@endpush

@section('content')
<div>

    {{-- Alert --}}
    <div id="message-container" class="mb-5" style="display:none;">
        <div id="message-alert" class="alert"></div>
    </div>

    {{-- 1. Maintenance Mode --}}
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-tools"></i> Maintenance Mode
        </div>

        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 mb-1">System Maintenance</h3>
                <p class="text-sm text-gray-500">
                    When enabled, all users (except admin) will be logged out and unable to access the system.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                <span class="status-badge {{ $maintenanceMode ? 'active' : 'inactive' }}" id="maintenance-badge">
                    {{ $maintenanceMode ? 'ENABLED' : 'DISABLED' }}
                </span>
                <label class="toggle-switch">
                    <input type="checkbox" id="maintenance-toggle" {{ $maintenanceMode ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div id="maintenance-message-section" class="mb-4" style="{{ !$maintenanceMode ? 'display:none;' : '' }}">
            <label for="maintenance-message" class="block text-sm font-medium text-gray-700 mb-1">
                Maintenance Message
            </label>
            <textarea id="maintenance-message" rows="2"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand focus:border-brand text-sm"
                      placeholder="Enter a custom maintenance message...">{{ $maintenanceMessage }}</textarea>
        </div>

        <button onclick="toggleMaintenanceMode()" class="btn-primary" id="maintenance-btn">
            <i class="fas fa-power-off"></i>
            {{ $maintenanceMode ? 'Disable' : 'Enable' }} Maintenance Mode
        </button>
    </div>

    {{-- 2. User & Email Notifications --}}
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-user-plus"></i> User & Email Notifications
        </div>
        <p class="text-sm text-gray-500 mb-4">
            When a new user is created, send them a welcome email with credentials and notify admins.
        </p>

        <div class="space-y-4 mb-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Send welcome email to new user</h3>
                    <p class="text-xs text-gray-500 mt-0.5">New user receives an email with name, password, position, and login link.</p>
                </div>
                <label class="toggle-switch flex-shrink-0 ml-4">
                    <input type="checkbox" id="send-welcome-email-toggle" {{ $sendWelcomeEmailToNewUser ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Notify admin when a new user is created</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Admin gets an in-app notification with the new user's name and email.</p>
                </div>
                <label class="toggle-switch flex-shrink-0 ml-4">
                    <input type="checkbox" id="notify-admin-toggle" {{ $notifyAdminOnNewUser ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="saveUserNotificationSettings()" class="btn-primary" id="user-notifications-btn">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <a href="{{ route('admin.system-settings.test-email') }}"
               class="btn-cache">
                <i class="fas fa-envelope"></i> Test Email
            </a>

        </div>
    </div>

    {{-- 3. Cache --}}
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-broom"></i> Cache Management
        </div>
        <p class="text-sm text-gray-500 mb-4">
            Clear or rebuild Laravel's application cache.
        </p>

        <div class="flex flex-wrap gap-2">
            <button onclick="runCacheCommand('clear-cache')" class="btn-cache" id="btn-clear-cache">
                <i class="fas fa-trash-alt"></i> Clear Cache
            </button>
            <button onclick="runCacheCommand('config-cache')" class="btn-cache" id="btn-config-cache">
                <i class="fas fa-cog"></i> Cache Config
            </button>
            <button onclick="runCacheCommand('route-cache')" class="btn-cache" id="btn-route-cache">
                <i class="fas fa-route"></i> Cache Routes
            </button>
            <button onclick="runCacheCommand('view-cache')" class="btn-cache" id="btn-view-cache">
                <i class="fas fa-eye"></i> Cache Views
            </button>
        </div>

        <div id="cache-output" style="display:none;">
            <div class="command-output" id="cache-output-content"></div>
        </div>
    </div>

</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

/* ── Maintenance Mode ── */
document.getElementById('maintenance-toggle').addEventListener('change', function () {
    document.getElementById('maintenance-message-section').style.display = this.checked ? 'block' : 'none';
});

function toggleMaintenanceMode() {
    const enabled = document.getElementById('maintenance-toggle').checked;
    const message = document.getElementById('maintenance-message').value;
    const btn = document.getElementById('maintenance-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    fetch('{{ route("admin.system-settings.maintenance.toggle") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ enabled, message })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            const badge = document.getElementById('maintenance-badge');
            if (data.maintenance_mode) {
                badge.className = 'status-badge active';
                badge.textContent = 'ENABLED';
                btn.innerHTML = '<i class="fas fa-power-off"></i> Disable Maintenance Mode';
            } else {
                badge.className = 'status-badge inactive';
                badge.textContent = 'DISABLED';
                btn.innerHTML = '<i class="fas fa-power-off"></i> Enable Maintenance Mode';
            }
        } else {
            showMessage(data.message || 'Error toggling maintenance mode', 'error');
        }
    })
    .catch(e => showMessage('Error: ' + e.message, 'error'))
    .finally(() => { btn.disabled = false; });
}

/* ── User Notifications ── */
function saveUserNotificationSettings() {
    const sendWelcome = document.getElementById('send-welcome-email-toggle').checked;
    const notifyAdmin = document.getElementById('notify-admin-toggle').checked;
    const btn = document.getElementById('user-notifications-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    fetch('{{ route("admin.system-settings.user-notifications.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ send_welcome_email_to_new_user: sendWelcome, notify_admin_on_new_user: notifyAdmin })
    })
    .then(r => r.json())
    .then(data => {
        showMessage(data.success ? data.message : (data.message || 'Error saving settings'), data.success ? 'success' : 'error');
    })
    .catch(e => showMessage('Error: ' + e.message, 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Save Settings';
    });
}

/* ── Cache Commands ── */
function runCacheCommand(command) {
    const btn = document.getElementById('btn-' + command);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running...'; }

    fetch('{{ route("admin.system-settings.command.run") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ command })
    })
    .then(r => r.json())
    .then(data => {
        const outputDiv  = document.getElementById('cache-output');
        const contentDiv = document.getElementById('cache-output-content');
        outputDiv.style.display = 'block';
        contentDiv.textContent  = data.output || (data.success ? 'Done.' : (data.message || 'Error.'));
        showMessage(data.success ? (data.message || 'Done.') : (data.message || 'Error'), data.success ? 'success' : 'error');
    })
    .catch(e => showMessage('Error: ' + e.message, 'error'))
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            // restore original label
            const labels = {
                'clear-cache':  '<i class="fas fa-trash-alt"></i> Clear Cache',
                'config-cache': '<i class="fas fa-cog"></i> Cache Config',
                'route-cache':  '<i class="fas fa-route"></i> Cache Routes',
                'view-cache':   '<i class="fas fa-eye"></i> Cache Views',
            };
            btn.innerHTML = labels[command] || command;
        }
    });
}

/* ── Message Helper ── */
function showMessage(message, type) {
    const container = document.getElementById('message-container');
    const alert     = document.getElementById('message-alert');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    container.style.display = 'block';
    setTimeout(() => { container.style.display = 'none'; }, 5000);
}
</script>
@endsection