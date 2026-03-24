<!-- Chatbot Assistant Widget -->
<div id="chatbotWidget" class="chatbot-widget">
    <!-- Floating Button -->
    <button id="chatbotToggle" class="chatbot-toggle" onclick="toggleChatbot()">
        <i class="fas fa-comments"></i>
        <span id="chatbotBadge" class="chatbot-badge" style="display: none;">0</span>
    </button>

    <!-- Chat Window -->
    <div id="chatbotWindow" class="chatbot-window" style="display: none;">
        <div class="chatbot-header">
            <div class="chatbot-header-content">
                <i class="fas fa-robot"></i>
                <span>CRM Assistant</span>
            </div>
            <button class="chatbot-close" onclick="toggleChatbot()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="chatbotContent" class="chatbot-content">
            <div class="chatbot-welcome">
                <p>👋 Hello! I'm your CRM assistant. I'll notify you about:</p>
                @php
                    $chatbotUser = auth()->user();
                @endphp
                <ul>
                    <li>📋 New leads assigned to you</li>
                    @if(!$chatbotUser || !method_exists($chatbotUser, 'isTelecaller') || !$chatbotUser->isTelecaller())
                        <li>✅ Pending verifications</li>
                    @endif
                    <li>📅 Upcoming follow-ups</li>
                    <li>📢 Important announcements</li>
                </ul>
            </div>
            <div id="chatbotNotifications" class="chatbot-notifications">
                <!-- Notifications will be loaded here -->
            </div>
        </div>
        
        <div class="chatbot-footer">
            <a href="{{ url('/dashboard') }}" class="chatbot-view-all">
                View Dashboard
            </a>
            <button type="button" class="chatbot-clear-all" onclick="window.chatbotAssistant?.cleanAllNotifications?.()">
                Clean All Notifications
            </button>
        </div>
    </div>
</div>

<style>
.chatbot-widget {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 99999 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    position: relative !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    z-index: 99999 !important;
}

.chatbot-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.chatbot-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.chatbot-window {
    position: absolute !important;
    bottom: 80px !important;
    right: 0 !important;
    width: 380px;
    max-width: calc(100vw - 40px);
    height: 500px;
    max-height: calc(100vh - 100px);
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s ease;
    z-index: 99999 !important;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chatbot-header {
    background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chatbot-header-content {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    font-size: 16px;
}

.chatbot-close {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.chatbot-close:hover {
    opacity: 1;
}

.chatbot-content {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    color: #1f2937;
}

.chatbot-welcome {
    padding: 16px;
    background: #f3f4f6;
    border-radius: 12px;
    margin-bottom: 16px;
    font-size: 14px;
    line-height: 1.6;
    color: #1f2937;
}

.chatbot-welcome p {
    color: #111827;
    font-weight: 500;
    margin-bottom: 8px;
}

.chatbot-welcome li {
    color: #374151;
}

.chatbot-welcome ul {
    margin: 12px 0 0 0;
    padding-left: 20px;
}

.chatbot-welcome li {
    margin: 6px 0;
}

.chatbot-notifications {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chatbot-notification {
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
    border-left: 4px solid #063A1C;
    cursor: pointer;
    transition: all 0.2s;
}

.chatbot-notification:hover {
    background: #f3f4f6;
    transform: translateX(-2px);
}

.chatbot-notification.unread {
    background: #eff6ff;
    border-left-color: #2563eb;
}

.chatbot-notification-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.chatbot-notification-title {
    font-weight: 600;
    font-size: 14px;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chatbot-notification-time {
    font-size: 11px;
    color: #6b7280;
}

.chatbot-notification-message {
    font-size: 13px;
    color: #4b5563;
    line-height: 1.4;
}

.chatbot-notification-action {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
}

.chatbot-view-lead-btn {
    width: 100%;
    padding: 8px 12px;
    background: linear-gradient(to right, #063A1C, #205A44);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.chatbot-view-lead-btn:hover {
    background: linear-gradient(to right, #205A44, #15803d);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chatbot-notification-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-footer {
    padding: 12px 16px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}

.chatbot-view-all {
    display: block;
    text-align: center;
    color: #063A1C;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    padding: 8px;
    border-radius: 6px;
    transition: background 0.2s;
}

.chatbot-view-all:hover {
    background: #e5e7eb;
}

.chatbot-clear-all {
    width: 100%;
    margin-top: 8px;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    background: #ffffff;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.chatbot-clear-all:hover {
    background: #f3f4f6;
    color: #111827;
    border-color: #d1d5db;
}

@media (max-width: 767px) {
    .chatbot-widget {
        bottom: 70px !important; /* Above mobile footer nav (60px height + 10px gap) */
        right: 12px !important;
    }
    
    .chatbot-toggle {
        width: 56px;
        height: 56px;
        font-size: 20px;
    }
    
    .chatbot-window {
        width: calc(100vw - 20px);
        right: 10px;
        bottom: 80px;
        height: calc(100vh - 100px);
    }
}
</style>

<script>
function toggleChatbot() {
    const window = document.getElementById('chatbotWindow');
    if (window.style.display === 'none') {
        window.style.display = 'flex';
        if (typeof loadChatbotNotifications === 'function') {
            loadChatbotNotifications();
        }
    } else {
        window.style.display = 'none';
    }
}
</script>
