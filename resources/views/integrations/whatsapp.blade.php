@extends('layouts.app')

@section('title', 'WhatsApp API Integration - Base CRM')
@section('page-title', 'WhatsApp API Integration')

@section('header-actions')
    <a href="{{ route('integrations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back to Integrations
    </a>
@endsection

@push('styles')
<style>
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-badge.active {
        background: #d1fae5;
        color: #065f46;
    }
    .status-badge.configured {
        background: #fef3c7;
        color: #92400e;
    }
    .status-badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    .alert {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #86efac;
    }
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Success/Error Messages -->
    <div id="message-container" class="mb-6" style="display: none;">
        <div id="message-alert" class="alert"></div>
    </div>

    <!-- Configuration Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
            <i class="fab fa-whatsapp text-green-500 text-2xl mr-3"></i>
            WhatsApp API Configuration
        </h2>

        <form id="whatsapp-config-form">
            @csrf
            
            <!-- Base URL -->
            <div class="mb-6">
                <label for="base_url" class="block text-sm font-medium text-gray-700 mb-2">
                    Base URL <span class="text-red-500">*</span>
                </label>
                <input type="url" 
                       id="base_url" 
                       name="base_url" 
                       value="{{ $settings->base_url ?? 'https://rengage.mcube.com' }}"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="https://rengage.mcube.com">
                <p class="text-xs text-gray-500 mt-1">Base URL for all API endpoints</p>
            </div>

            <!-- Legacy API Endpoint (for backward compatibility) -->
            <div class="mb-6">
                <label for="api_endpoint" class="block text-sm font-medium text-gray-700 mb-2">
                    Legacy API Endpoint (Optional)
                </label>
                <input type="url" 
                       id="api_endpoint" 
                       name="api_endpoint" 
                       value="{{ $settings->api_endpoint }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       placeholder="https://engage-api-eta.vercel.app/">
                <p class="text-xs text-gray-500 mt-1">Legacy endpoint (for backward compatibility)</p>
            </div>

            <!-- API Token -->
            <div class="mb-6">
                <label for="api_token" class="block text-sm font-medium text-gray-700 mb-2">
                    API Token <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" 
                           id="api_token" 
                           name="api_token" 
                           value="{{ $settings->api_token }}"
                           required
                           class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Enter your API token">
                    <button type="button" 
                            onclick="toggleTokenVisibility()" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-eye" id="token-eye-icon"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Enter your WhatsApp API authentication token</p>
            </div>

            <!-- Active Toggle -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active"
                           {{ $settings->is_active ? 'checked' : '' }}
                           class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <span class="ml-2 text-sm text-gray-700">Activate WhatsApp API Integration</span>
                </label>
            </div>

            <!-- Status Badge -->
            @if($settings->is_verified)
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                    <span class="text-sm font-medium text-green-800">
                        API Connection Verified
                        @if($settings->verified_at)
                            ({{ $settings->verified_at->format('M d, Y H:i') }})
                        @endif
                    </span>
                </div>
            </div>
            @endif

            <!-- Endpoint Configuration Section -->
            <div class="mb-6 border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Endpoint Configuration</h3>
                    <button type="button" 
                            onclick="resetEndpoints()" 
                            class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-undo mr-2"></i> Reset to Defaults
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">Configure individual API endpoints. Use placeholders like {id}, {contact}, {templateID} where needed.</p>

                <!-- Messaging Endpoints -->
                <div class="mb-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-comment-dots mr-2 text-green-600"></i> Messaging
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="send_message_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Send Message</label>
                            <input type="text" id="send_message_endpoint" name="send_message_endpoint" 
                                   value="{{ $settings->send_message_endpoint ?? '/api/wpbox/sendmessage' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label for="send_template_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Send Template</label>
                            <input type="text" id="send_template_endpoint" name="send_template_endpoint" 
                                   value="{{ $settings->send_template_endpoint ?? '/api/wpbox/sendtemplatmessage' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <!-- Conversations Endpoints -->
                <div class="mb-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-comments mr-2 text-blue-600"></i> Conversations
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="get_conversations_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Get Conversations</label>
                            <input type="text" id="get_conversations_endpoint" name="get_conversations_endpoint" 
                                   value="{{ $settings->get_conversations_endpoint ?? '/api/wpbox/getConversations' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label for="get_messages_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Get Messages</label>
                            <input type="text" id="get_messages_endpoint" name="get_messages_endpoint" 
                                   value="{{ $settings->get_messages_endpoint ?? '/api/wpbox/getMessages/{contact}' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <!-- Templates Endpoints -->
                <div class="mb-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-file-alt mr-2 text-purple-600"></i> Templates
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="get_templates_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Get Templates</label>
                            <input type="text" id="get_templates_endpoint" name="get_templates_endpoint" 
                                   value="{{ $settings->get_templates_endpoint ?? '/api/wpbox/getTemplates' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label for="get_template_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Get Template</label>
                            <input type="text" id="get_template_endpoint" name="get_template_endpoint" 
                                   value="{{ $settings->get_template_endpoint ?? '/api/wpbox/get-template/{templateID}' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label for="create_template_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Create Template</label>
                            <input type="text" id="create_template_endpoint" name="create_template_endpoint" 
                                   value="{{ $settings->create_template_endpoint ?? '/api/wpbox/createTemplate' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label for="delete_template_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Delete Template</label>
                            <input type="text" id="delete_template_endpoint" name="delete_template_endpoint" 
                                   value="{{ $settings->delete_template_endpoint ?? '/api/wpbox/deleteTemplate' }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <!-- Groups Endpoints -->
                <div class="mb-4">
                    <details class="cursor-pointer">
                        <summary class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-users mr-2 text-indigo-600"></i> Groups
                        </summary>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label for="get_groups_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Get Groups</label>
                                <input type="text" id="get_groups_endpoint" name="get_groups_endpoint" 
                                       value="{{ $settings->get_groups_endpoint ?? '/api/wpbox/getGroups' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="make_group_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Create Group</label>
                                <input type="text" id="make_group_endpoint" name="make_group_endpoint" 
                                       value="{{ $settings->make_group_endpoint ?? '/api/wpbox/makeGroups' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="update_group_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Update Group</label>
                                <input type="text" id="update_group_endpoint" name="update_group_endpoint" 
                                       value="{{ $settings->update_group_endpoint ?? '/api/wpbox/updateGroups/{id}' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="remove_group_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Remove Group</label>
                                <input type="text" id="remove_group_endpoint" name="remove_group_endpoint" 
                                       value="{{ $settings->remove_group_endpoint ?? '/api/wpbox/removeGroups/{id}' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Contacts Endpoints -->
                <div class="mb-4">
                    <details class="cursor-pointer">
                        <summary class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-address-book mr-2 text-yellow-600"></i> Contacts
                        </summary>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label for="import_contact_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Import Contact</label>
                                <input type="text" id="import_contact_endpoint" name="import_contact_endpoint" 
                                       value="{{ $settings->import_contact_endpoint ?? '/api/wpbox/importContact' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="update_contact_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Update Contact</label>
                                <input type="text" id="update_contact_endpoint" name="update_contact_endpoint" 
                                       value="{{ $settings->update_contact_endpoint ?? '/api/wpbox/updateContact/{id}' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="remove_contact_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Remove Contact</label>
                                <input type="text" id="remove_contact_endpoint" name="remove_contact_endpoint" 
                                       value="{{ $settings->remove_contact_endpoint ?? '/api/wpbox/removeContact/{id}' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="add_contacts_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Add Contacts (Bulk)</label>
                                <input type="text" id="add_contacts_endpoint" name="add_contacts_endpoint" 
                                       value="{{ $settings->add_contacts_endpoint ?? '/api/wpbox/addContacts' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </details>
                </div>

                <!-- Media & Campaigns Endpoints -->
                <div class="mb-4">
                    <details class="cursor-pointer">
                        <summary class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-images mr-2 text-pink-600"></i> Media & Campaigns
                        </summary>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label for="get_media_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Get Media</label>
                                <input type="text" id="get_media_endpoint" name="get_media_endpoint" 
                                       value="{{ $settings->get_media_endpoint ?? '/api/wpbox/getMedia' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="get_campaigns_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Get Campaigns</label>
                                <input type="text" id="get_campaigns_endpoint" name="get_campaigns_endpoint" 
                                       value="{{ $settings->get_campaigns_endpoint ?? '/api/wpbox/getCampaigns' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label for="send_campaign_endpoint" class="block text-sm font-medium text-gray-700 mb-1">Send Campaign</label>
                                <input type="text" id="send_campaign_endpoint" name="send_campaign_endpoint" 
                                       value="{{ $settings->send_campaign_endpoint ?? '/api/wpbox/sendwpcampaigns' }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            <!-- Sync Templates Section (Prominent) -->
            <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                            <i class="fas fa-sync-alt mr-2 text-indigo-600"></i>Sync Templates
                        </h3>
                        <p class="text-sm text-gray-600">Sync all WhatsApp templates from API in one click. Templates will be available to all users.</p>
                    </div>
                    <button type="button" 
                            onclick="syncTemplates()" 
                            id="syncTemplatesBtn"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors duration-200 font-medium shadow-lg">
                        <i class="fas fa-sync-alt mr-2" id="syncIcon"></i>
                        <span id="syncText">Sync Templates Now</span>
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-4 flex-wrap">
                <button type="submit" 
                        class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-colors duration-200 font-medium">
                    <i class="fas fa-save mr-2"></i> Save Settings
                </button>
                <button type="button" 
                        onclick="verifyConnection()" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                    <i class="fas fa-check-circle mr-2"></i> Verify Connection
                </button>
                <button type="button" 
                        onclick="testMessage()" 
                        class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 font-medium">
                    <i class="fas fa-paper-plane mr-2"></i> Test Message
                </button>
                <a href="{{ route('integrations.whatsapp.quick-test') }}" 
                   class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 font-medium">
                    <i class="fas fa-bolt mr-2"></i> Quick Test
                </a>
                <a href="{{ route('integrations.whatsapp.debug') }}" 
                   class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors duration-200 font-medium">
                    <i class="fas fa-bug mr-2"></i> Debug & Test
                </a>
            </div>
        </form>
    </div>

    <!-- API Information Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
            API Information
        </h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current API Endpoint</label>
                <div class="flex items-center space-x-2">
                    <input type="text" 
                           value="{{ $settings->api_endpoint }}" 
                           readonly
                           class="flex-1 px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                    <button onclick="copyToClipboard('{{ $settings->api_endpoint }}')" 
                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">API Token</label>
                <div class="flex items-center space-x-2">
                    <input type="password" 
                           id="display_token"
                           value="{{ $settings->api_token }}" 
                           readonly
                           class="flex-1 px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-700">
                    <button onclick="copyToClipboard('{{ $settings->api_token }}')" 
                            class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Message Modal -->
    <div id="test-message-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Test WhatsApp Message</h3>
                <button onclick="closeTestModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="test-message-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="text" 
                           id="test_phone" 
                           name="phone" 
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="+919876543210 or 9876543210">
                    <p class="text-xs text-gray-500 mt-1">Include country code (e.g., +91 for India)</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea id="test_message" 
                              name="message" 
                              required
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Enter test message"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeTestModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i> Send Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Save Settings
document.getElementById('whatsapp-config-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('is_active', document.getElementById('is_active').checked ? '1' : '0');
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    
    fetch('{{ route("integrations.whatsapp.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Settings saved successfully!', 'success');
        } else {
            showMessage(data.message || 'Error saving settings', 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Verify Connection
function verifyConnection() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Verifying...';
    
    fetch('{{ route("integrations.whatsapp.verify") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Connection verified successfully!', 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showMessage('Verification failed: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Test Message
function testMessage() {
    document.getElementById('test-message-modal').classList.remove('hidden');
    document.getElementById('test_phone').focus();
}

function closeTestModal() {
    document.getElementById('test-message-modal').classList.add('hidden');
    document.getElementById('test-message-form').reset();
}

document.getElementById('test-message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        phone: document.getElementById('test_phone').value,
        message: document.getElementById('test_message').value
    };
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
    
    fetch('{{ route("integrations.whatsapp.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Test message sent successfully!', 'success');
            closeTestModal();
        } else {
            showMessage('Failed to send: ' + (data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showMessage('Error: ' + error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

function toggleTokenVisibility() {
    const input = document.getElementById('api_token');
    const displayInput = document.getElementById('display_token');
    const icon = document.getElementById('token-eye-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        if (displayInput) displayInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        if (displayInput) displayInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showMessage('Copied to clipboard!', 'success');
    }, function(err) {
        showMessage('Failed to copy', 'error');
    });
}

function showMessage(message, type) {
    const container = document.getElementById('message-container');
    const alert = document.getElementById('message-alert');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    container.style.display = 'block';
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}

// Sync Templates function
function syncTemplates() {
    const syncBtn = document.getElementById('syncTemplatesBtn');
    const syncIcon = document.getElementById('syncIcon');
    const syncText = document.getElementById('syncText');
    
    // Disable button and show loading
    syncBtn.disabled = true;
    syncIcon.classList.add('fa-spin');
    syncText.textContent = 'Syncing...';
    
    fetch('{{ route("chat.templates.sync") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = `✅ Successfully synced ${data.synced_count} template(s) from API.\nTotal templates available: ${data.total_templates}`;
            if (data.warnings && data.warnings.length > 0) {
                message += `\n\n⚠️ Warnings: ${data.warnings.length} template(s) had issues.`;
            }
            showMessage(message, 'success');
        } else {
            showMessage('❌ Sync failed: ' + (data.message || data.error || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error syncing templates:', error);
        showMessage('Error syncing templates. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable button
        syncBtn.disabled = false;
        syncIcon.classList.remove('fa-spin');
        syncText.textContent = 'Sync Templates';
    });
}

// Reset endpoints to defaults
function resetEndpoints() {
    if (confirm('Are you sure you want to reset all endpoints to default values?')) {
        document.getElementById('base_url').value = 'https://api.engage-api.com';
        document.getElementById('send_message_endpoint').value = '/api/wpbox/sendmessage';
        document.getElementById('send_template_endpoint').value = '/api/wpbox/sendtemplatmessage';
        document.getElementById('get_conversations_endpoint').value = '/api/wpbox/getConversations';
        document.getElementById('get_messages_endpoint').value = '/api/wpbox/getMessages/{contact}';
        document.getElementById('get_templates_endpoint').value = '/api/wpbox/getTemplates';
        document.getElementById('get_template_endpoint').value = '/api/wpbox/get-template/{templateID}';
        document.getElementById('create_template_endpoint').value = '/api/wpbox/createTemplate';
        document.getElementById('delete_template_endpoint').value = '/api/wpbox/deleteTemplate';
        document.getElementById('get_groups_endpoint').value = '/api/wpbox/getGroups';
        document.getElementById('make_group_endpoint').value = '/api/wpbox/makeGroups';
        document.getElementById('update_group_endpoint').value = '/api/wpbox/updateGroups/{id}';
        document.getElementById('remove_group_endpoint').value = '/api/wpbox/removeGroups/{id}';
        document.getElementById('import_contact_endpoint').value = '/api/wpbox/importContact';
        document.getElementById('update_contact_endpoint').value = '/api/wpbox/updateContact/{id}';
        document.getElementById('remove_contact_endpoint').value = '/api/wpbox/removeContact/{id}';
        document.getElementById('add_contacts_endpoint').value = '/api/wpbox/addContacts';
        document.getElementById('get_media_endpoint').value = '/api/wpbox/getMedia';
        document.getElementById('get_campaigns_endpoint').value = '/api/wpbox/getCampaigns';
        document.getElementById('send_campaign_endpoint').value = '/api/wpbox/sendwpcampaigns';
        showMessage('Endpoints reset to defaults', 'success');
    }
}

// Close modal on outside click
document.getElementById('test-message-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTestModal();
    }
});
</script>
@endsection
