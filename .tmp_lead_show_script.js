
    // Use window only to avoid duplicate declaration when layout (e.g. telecaller) already defines API_BASE_URL
    if (typeof window.API_BASE_URL === 'undefined') {
        window.API_BASE_URL = '{{ url("/api") }}';
    }
    if (typeof window.API_TOKEN === 'undefined') {
        window.API_TOKEN = document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
    }
    if (typeof window.USER_ROLE === 'undefined') {
        window.USER_ROLE = '{{ $user->role->slug ?? "" }}';
    }
    const LEAD_ID = {{ $lead->id }};
    const LEAD_INTERESTED_PROJECTS = @json($uniqueProjectNames ?? []);
    const CAN_TRANSFER_OWNER = @json($user && ($user->isAdmin() || $user->isCrm()));

    // Modal open/close functions
    function openCallModal() {
        document.getElementById('callModal').classList.remove('hidden');
        // Set default start time to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.querySelector('#callForm input[name="start_time"]').value = now.toISOString().slice(0, 16);
    }

    function closeCallModal() {
        document.getElementById('callModal').classList.add('hidden');
        document.getElementById('callForm').reset();
    }

    function openFollowupModal() {
        document.getElementById('followupModal').classList.remove('hidden');
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
        document.querySelector('#followupForm input[name="scheduled_at"]').value = tomorrow.toISOString().slice(0, 16);
        const checkbox = document.getElementById('followup_required');
        if (checkbox) checkbox.checked = true;
    }

    function closeFollowupModal() {
        document.getElementById('followupModal').classList.add('hidden');
        document.getElementById('followupForm').reset();
    }

    function openSiteVisitModal() {
        document.getElementById('siteVisitModal').classList.remove('hidden');
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
        const dateInput = document.querySelector('#siteVisitForm input[name="visit_date"]');
        const timeInput = document.querySelector('#siteVisitForm input[name="visit_time"]');
        const projectInput = document.querySelector('#siteVisitForm input[name="project_name"]');
        if (dateInput) dateInput.value = tomorrow.toISOString().slice(0, 10);
        if (timeInput) timeInput.value = tomorrow.toISOString().slice(11, 16);
        if (projectInput) {
            projectInput.value = (LEAD_INTERESTED_PROJECTS && LEAD_INTERESTED_PROJECTS[0]) ? LEAD_INTERESTED_PROJECTS[0] : '';
        }
        updateSiteVisitProjectHiddenInput();
    }

    function closeSiteVisitModal() {
        document.getElementById('siteVisitModal').classList.add('hidden');
        document.getElementById('siteVisitForm').reset();
        const hiddenInput = document.getElementById('siteVisitProjectHidden');
        if (hiddenInput) {
            hiddenInput.value = '';
        }
    }

    function updateSiteVisitProjectHiddenInput() {
        const projectInput = document.getElementById('siteVisitProjectInput');
        const hiddenInput = document.getElementById('siteVisitProjectHidden');

        if (!hiddenInput) return;

        hiddenInput.value = projectInput ? projectInput.value.trim() : '';
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    async function openMeetingModal() {
        document.getElementById('meetingModal').classList.remove('hidden');

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
        const dateInput = document.getElementById('meeting_date');
        const timeInput = document.getElementById('meeting_time');
        if (dateInput) dateInput.value = tomorrow.toISOString().slice(0, 10);
        if (timeInput) timeInput.value = tomorrow.toISOString().slice(11, 16);
        const typeInput = document.getElementById('meeting_type');
        if (typeInput && !typeInput.value) typeInput.value = 'Initial Meeting';
        const modeInput = document.getElementById('meeting_mode');
        if (modeInput && !modeInput.value) modeInput.value = 'online';
        toggleMeetingModeFields();

        try {
            const response = await fetch(`${window.API_BASE_URL}/sales-manager/leads/${LEAD_ID}/meeting-history`, {
                headers: {
                    'Authorization': `Bearer ${window.API_TOKEN}`,
                    'Accept': 'application/json',
                }
            });

            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    document.getElementById('meeting_sequence').value = result.next_sequence || 1;
                } else {
                    console.warn('Non-JSON response for meeting history');
                }
            }
        } catch (error) {
            console.error('Failed to load meeting history:', error);
            document.getElementById('meeting_sequence').value = 1;
        }
    }

    function closeMeetingModal() {
        document.getElementById('meetingModal').classList.add('hidden');
        document.getElementById('meetingForm').reset();
        const modeInput = document.getElementById('meeting_mode');
        if (modeInput) modeInput.value = 'online';
        toggleMeetingModeFields();
    }

    function toggleMeetingModeFields() {
        const mode = document.getElementById('meeting_mode')?.value || 'online';
        const onlineFields = document.getElementById('meetingLinkField');
        const offlineFields = document.getElementById('meetingLocationField');
        const locationInput = document.getElementById('location_input');
        const meetingLinkInput = document.querySelector('#meetingForm input[name="meeting_link"]');

        if (mode === 'online') {
            if (onlineFields) onlineFields.classList.remove('hidden');
            if (offlineFields) offlineFields.classList.add('hidden');
            if (locationInput) locationInput.removeAttribute('required');
            if (meetingLinkInput) {
                meetingLinkInput.setAttribute('required', 'required');
            }
        } else {
            if (onlineFields) onlineFields.classList.add('hidden');
            if (offlineFields) offlineFields.classList.remove('hidden');
            if (locationInput) locationInput.setAttribute('required', 'required');
            if (meetingLinkInput) {
                meetingLinkInput.removeAttribute('required');
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleMeetingModeFields();
    });

    function openScheduleCallTaskModal() {
        document.getElementById('scheduleCallTaskModal').classList.remove('hidden');
        // Set default scheduled time to tomorrow same time
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
        document.querySelector('#scheduleCallTaskForm input[name="scheduled_at"]').value = tomorrow.toISOString().slice(0, 16);
    }

    function closeScheduleCallTaskModal() {
        document.getElementById('scheduleCallTaskModal').classList.add('hidden');
        document.getElementById('scheduleCallTaskForm').reset();
    }

    function openOwnerTransferModal() {
        if (!CAN_TRANSFER_OWNER) return;
        const modal = document.getElementById('ownerTransferModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeOwnerTransferModal() {
        const modal = document.getElementById('ownerTransferModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        const form = document.getElementById('ownerTransferForm');
        if (form) {
            form.reset();
            const createCheckbox = document.getElementById('ownerTransferCreateCallingTask');
            if (createCheckbox) {
                createCheckbox.checked = true;
            }
        }
    }

    // Form submission functions
    async function submitCall(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            lead_id: LEAD_ID,
            phone_number: formData.get('phone_number'),
            call_type: formData.get('call_type'),
            start_time: new Date(formData.get('start_time')).toISOString(),
            duration: parseInt(formData.get('duration')),
            call_outcome: formData.get('call_outcome') || null,
            notes: formData.get('notes') || null,
        };

        try {
            const response = await fetch(`${window.API_BASE_URL}/call-logs`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${window.API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            // Check if response is JSON before parsing
            const contentType = response.headers.get('content-type');
            let result;
            
            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                alert('Server error: Invalid response format. Please try again.');
                return;
            }

            if (response.ok && result.success) {
                alert('Call logged successfully!');
                closeCallModal();
                location.reload(); // Reload to show in timeline
            } else {
                alert(result.message || 'Failed to log call');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while logging the call');
        }
    }

    async function submitFollowup(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            lead_id: LEAD_ID,
            type: formData.get('type'),
            notes: formData.get('notes'),
            scheduled_at: new Date(formData.get('scheduled_at')).toISOString(),
        };

        try {
            const response = await fetch(`${window.API_BASE_URL}/follow-ups`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${window.API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            // Check if response is JSON before parsing
            const contentType = response.headers.get('content-type');
            let result;
            
            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                alert('Server error: Invalid response format. Please try again.');
                return;
            }

            if (response.ok) {
                alert('Follow-up scheduled successfully!');
                closeFollowupModal();
                location.reload(); // Reload to show in timeline
            } else {
                alert(result.message || 'Failed to schedule follow-up');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while scheduling the follow-up');
        }
    }

    async function submitSiteVisit(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        updateSiteVisitProjectHiddenInput();
        const scheduledAt = new Date(`${formData.get('visit_date')}T${formData.get('visit_time')}`);
        const projectName = document.getElementById('siteVisitProjectHidden').value || null;
        const data = {
            lead_id: LEAD_ID,
            scheduled_at: scheduledAt.toISOString(),
            project: projectName,
            property_name: projectName,
            property_address: formData.get('visit_location') || null,
            visit_notes: formData.get('visit_notes') || null,
            reminder_enabled: formData.get('visit_reminder') === 'on',
        };

        try {
            const response = await fetch(`${window.API_BASE_URL}/site-visits`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${window.API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const contentType = response.headers.get('content-type');
            let result;

            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                if (typeof showNotification === 'function') {
                    showNotification('Server error: Invalid response format. Please try again.', 'error', 3000);
                } else {
                    alert('Server error: Invalid response format. Please try again.');
                }
                return;
            }

            if (response.ok && result.success) {
                if (typeof showNotification === 'function') {
                    showNotification('Site visit scheduled successfully!', 'success', 3000);
                } else {
                    alert('Site visit scheduled successfully!');
                }
                closeSiteVisitModal();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                if (typeof showNotification === 'function') {
                    showNotification(result.message || 'Failed to schedule site visit', 'error', 3000);
                } else {
                    alert(result.message || 'Failed to schedule site visit');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while scheduling the site visit');
        }
    }

    async function submitMeeting(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const scheduledAt = new Date(`${formData.get('meeting_date')}T${formData.get('meeting_time')}`);

        const data = {
            lead_id: LEAD_ID,
            meeting_sequence: parseInt(formData.get('meeting_sequence')),
            scheduled_at: scheduledAt.toISOString(),
            meeting_mode: formData.get('meeting_mode'),
            meeting_link: formData.get('meeting_link') || null,
            location: formData.get('location') || null,
            reminder_enabled: formData.get('reminder_enabled') === 'on',
            reminder_minutes: 5,
            meeting_notes: formData.get('meeting_notes') || null,
        };

        try {
            const response = await fetch(`${window.API_BASE_URL}/sales-manager/meetings/quick-schedule-with-reminder`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${window.API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const contentType = response.headers.get('content-type');
            let result;

            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                alert('Server error: Invalid response format. Please try again.');
                return;
            }

            if (response.ok && (result.success !== false)) {
                const message = 'Meeting scheduled successfully!' + (data.reminder_enabled ? ' You will get a reminder 5 minutes before.' : '');
                showSuccessPopup(message);
                closeMeetingModal();
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                let errorMsg = result.message || 'Failed to schedule meeting';
                if (result.errors) {
                    const errorList = Object.values(result.errors).flat().join('\n');
                    errorMsg += '\n\n' + errorList;
                } else if (result.error) {
                    errorMsg += '\n\n' + result.error;
                }
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while scheduling the meeting. Please check console for details.');
        }
    }

    async function submitScheduleCallTask(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = {
            lead_id: LEAD_ID,
            scheduled_at: new Date(formData.get('scheduled_at')).toISOString(),
            notes: formData.get('notes') || null,
        };

        try {
            // Determine the correct API endpoint based on user role
            let endpoint;
            if (window.USER_ROLE === 'telecaller') {
                endpoint = `${window.API_BASE_URL}/telecaller/tasks/schedule-call`;
            } else {
                // For sales managers, sales executives, and others, use sales-manager endpoint
                endpoint = `${window.API_BASE_URL}/sales-manager/tasks/schedule-call`;
            }
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${window.API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });

            // Check if response is JSON before parsing
            const contentType = response.headers.get('content-type');
            let result;
            
            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                alert('Server error: Invalid response format. Please try again.');
                return;
            }

            if (response.ok && result.success) {
                // Show success message with button to go to task section
                closeScheduleCallTaskModal();
                
                // Determine task route based on user role
                let taskRoute = '#';
                if (window.USER_ROLE === 'telecaller') {
                    taskRoute = '{{ route("telecaller.tasks") }}';
                } else if (window.USER_ROLE === 'sales_manager' || window.USER_ROLE === 'sales_executive') {
                    @php
                        try {
                            $tasksRoute = route('sales-manager.tasks');
                        } catch (\Exception $e) {
                            $tasksRoute = '/sales-manager/tasks';
                        }
                    @endphp
                    taskRoute = '{{ $tasksRoute }}';
                } else {
                    // For other roles, try to construct the URL
                    taskRoute = '/sales-manager/tasks';
                }
                
                // Create success message with button
                const successMessage = `
                    <div id="taskSuccessMessage" style="position: fixed; top: 20px; right: 20px; z-index: 10000; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px 24px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); min-width: 320px; max-width: 400px; animation: slideInRight 0.3s ease-out;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                                ✓
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; font-size: 16px; margin-bottom: 4px;">Task Created Successfully!</div>
                                <div style="font-size: 14px; opacity: 0.9;">Call task has been scheduled.</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button onclick="document.getElementById('taskSuccessMessage').remove(); window.location.href='${taskRoute}';" style="flex: 1; background: white; color: #059669; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                Go to Task Section
                            </button>
                            <button onclick="document.getElementById('taskSuccessMessage').remove(); location.reload();" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)';" onmouseout="this.style.background='rgba(255,255,255,0.2)';">
                                Close
                            </button>
                        </div>
                    </div>
                    <style>
                        @keyframes slideInRight {
                            from {
                                transform: translateX(100%);
                                opacity: 0;
                            }
                            to {
                                transform: translateX(0);
                                opacity: 1;
                            }
                        }
                    </style>
                `;
                
                // Insert success message
                document.body.insertAdjacentHTML('beforeend', successMessage);
                
                // Auto-remove after 10 seconds
                setTimeout(() => {
                    const msg = document.getElementById('taskSuccessMessage');
                    if (msg) msg.remove();
                }, 10000);
            } else {
                // Show detailed error message
                let errorMsg = result.message || 'Failed to schedule call task';
                if (result.errors) {
                    const errorDetails = Object.values(result.errors).flat().join(', ');
                    errorMsg += ': ' + errorDetails;
                }
                
                console.error('Task creation error:', result);
                console.error('Response status:', response.status);
                console.error('Response body:', result);
                
                if (typeof showNotification === 'function') {
                    showNotification(errorMsg, 'error', 5000);
                } else {
                    alert(errorMsg);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            if (typeof showNotification === 'function') {
                showNotification('An error occurred while scheduling the call task', 'error', 3000);
            } else {
                alert('An error occurred while scheduling the call task');
            }
        }
    }

    async function submitOwnerTransfer(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const assignedToRaw = formData.get('assigned_to');
        if (!assignedToRaw) {
            alert('Please select new owner');
            return;
        }

        const payload = {
            assigned_to: parseInt(assignedToRaw, 10),
            create_calling_task: formData.get('create_calling_task') === 'on',
            transfer_existing_tasks: true,
            notes: (formData.get('notes') || '').trim() || null,
        };

        try {
            const response = await fetch(`${window.API_BASE_URL}/leads/${LEAD_ID}/assign`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${window.API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const contentType = response.headers.get('content-type') || '';
            const result = contentType.includes('application/json') ? await response.json() : {};
            if (!response.ok) {
                throw new Error(result.message || 'Failed to transfer lead owner');
            }

            alert('Lead owner changed successfully.');
            closeOwnerTransferModal();
            location.reload();
        } catch (error) {
            console.error('Owner transfer error:', error);
            alert(error.message || 'Unable to transfer lead owner');
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const callModal = document.getElementById('callModal');
        const followupModal = document.getElementById('followupModal');
        const siteVisitModal = document.getElementById('siteVisitModal');
        const meetingModal = document.getElementById('meetingModal');
        const scheduleCallTaskModal = document.getElementById('scheduleCallTaskModal');

        if (event.target === callModal) {
            closeCallModal();
        }
        if (event.target === followupModal) {
            closeFollowupModal();
        }
        if (event.target === siteVisitModal) {
            closeSiteVisitModal();
        }
        if (event.target === meetingModal) {
            closeMeetingModal();
        }
        if (event.target === scheduleCallTaskModal) {
            closeScheduleCallTaskModal();
        }
        const ownerTransferModal = document.getElementById('ownerTransferModal');
        if (event.target === ownerTransferModal) {
            closeOwnerTransferModal();
        }
    }

    // Animated Success Popup
    function showSuccessPopup(message) {
        // Create popup if it doesn't exist
        let popup = document.getElementById('successPopup');
        if (!popup) {
            popup = document.createElement('div');
            popup.id = 'successPopup';
            popup.className = 'fixed inset-0 z-[9999] flex items-center justify-center pointer-events-none';
            popup.innerHTML = `
                <div class="bg-black bg-opacity-50 fixed inset-0 pointer-events-auto" id="successPopupOverlay"></div>
                <div id="successPopupContent" class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 transform scale-0 pointer-events-auto relative z-10">
                    <div class="flex flex-col items-center">
                        <div class="success-tick-container w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center mb-4 shadow-lg">
                            <svg class="success-tick" width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="22" stroke="white" stroke-width="3" class="tick-circle"/>
                                <path d="M14 24 L20 30 L34 16" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="tick-path" fill="none"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Success!</h3>
                        <p class="text-gray-600 text-center">${message}</p>
                    </div>
                </div>
            `;
            document.body.appendChild(popup);
        }

        // Update message
        const messageEl = popup.querySelector('p');
        if (messageEl) {
            messageEl.textContent = message;
        }

        // Show popup with animation
        popup.style.display = 'flex';
        const content = document.getElementById('successPopupContent');
        content.style.transform = 'scale(0)';
        content.style.opacity = '0';

        // Trigger animation
        setTimeout(() => {
            content.style.transition = 'all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
            content.style.transform = 'scale(1)';
            content.style.opacity = '1';
        }, 10);

        // Auto-hide after 2 seconds
        setTimeout(() => {
            content.style.transition = 'all 0.3s ease-in';
            content.style.transform = 'scale(0.8)';
            content.style.opacity = '0';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 300);
        }, 2000);
    }

