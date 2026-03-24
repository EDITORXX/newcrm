@extends('sales-manager.layout')

@section('title', 'Create Site Visit - Senior Manager')
@section('page-title', 'Create Site Visit')

@push('styles')
<style>
    .schedule-shell {
        max-width: 980px;
        margin: 0 auto;
    }
    .form-container {
        background: #fff;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid rgba(23, 97, 168, 0.10);
        box-shadow: 0 24px 50px rgba(23, 97, 168, 0.08);
        max-width: 980px;
        margin: 0 auto;
        padding: 0;
    }
    .schedule-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 26px 28px;
        background: linear-gradient(135deg, #e7f0fa 0%, #f0f6fe 100%);
        border-bottom: 1px solid #d8e6f6;
    }
    .schedule-head-main {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .schedule-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: rgba(23, 97, 168, 0.12);
        color: #1761A8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .schedule-head h2 {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 700;
        color: #0e4a82;
        letter-spacing: -0.03em;
    }
    .schedule-head p {
        margin: 4px 0 0;
        color: #5f6f84;
        font-size: 0.95rem;
    }
    .schedule-badge {
        border-radius: 999px;
        background: #1761A8;
        color: #fff;
        padding: 7px 16px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .schedule-body {
        padding: 28px;
    }
    #siteVisitForm {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 20px;
    }
    .form-group {
        margin-bottom: 0;
    }
    .form-group.form-wide {
        grid-column: 1 / -1;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 0.95rem;
    }
    .form-group label .required {
        color: #ef4444;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #d5dfeb;
        border-radius: 14px;
        font-size: 15px;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: #fff;
        color: #1f2937;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #1761A8;
        box-shadow: 0 0 0 4px rgba(23, 97, 168, 0.08);
    }
    .form-group input[readonly] {
        background: #f8fafc;
        color: #4b5563;
    }
    .form-meta-note {
        grid-column: 1 / -1;
        margin-top: -4px;
        color: #6b7280;
        font-size: 0.82rem;
    }
    .photo-preview {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .photo-preview-item {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e0e0e0;
    }
    .photo-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .photo-preview-item .remove-photo {
        position: absolute;
        top: 4px;
        right: 4px;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    .form-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-top: 10px;
        padding-top: 22px;
        border-top: 1px solid #edf1ee;
    }
    .form-actions-copy {
        color: #6b7280;
        font-size: 0.9rem;
    }
    .form-actions-buttons {
        display: flex;
        gap: 12px;
    }
    .btn {
        padding: 13px 24px;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 700;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, #1761A8 0%, #124a82 100%);
        color: white;
        box-shadow: 0 14px 28px rgba(23, 97, 168, 0.18);
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #124a82 0%, #0e3b67 100%);
        transform: translateY(-1px);
        box-shadow: 0 18px 32px rgba(23, 97, 168, 0.24);
    }
    .btn-secondary {
        background: #fff;
        color: #4b5563;
        border: 1px solid #d1d5db;
    }
    .btn-secondary:hover {
        background: #f9fafb;
    }
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    @media (max-width: 768px) {
        .schedule-head,
        .schedule-body {
            padding: 20px;
        }
        .schedule-head {
            align-items: flex-start;
            flex-direction: column;
        }
        #siteVisitForm {
            grid-template-columns: 1fr;
        }
        .form-group.form-wide,
        .form-meta-note,
        .form-actions {
            grid-column: auto;
        }
        .form-actions,
        .form-actions-buttons {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush

@section('content')
<div class="schedule-shell">
<div class="form-container">
    <div class="schedule-head">
        <div class="schedule-head-main">
            <div class="schedule-icon">
                <i class="fas fa-location-dot"></i>
            </div>
            <div>
                <h2>Schedule visit</h2>
                <p>Cleaner site-visit planning form with stronger hierarchy and easier readability.</p>
            </div>
        </div>
        <span class="schedule-badge">Visit</span>
    </div>
    <div class="schedule-body">
    <div id="alertContainer"></div>
    
    @if(isset($dynamicForm) && $dynamicForm)
        <!-- Dynamic Form -->
        <x-dynamic-form :form="$dynamicForm" />
        <script>
            // Override form submission for dynamic form to use existing endpoint
            document.addEventListener('DOMContentLoaded', function() {
                const dynamicForm = document.querySelector('.dynamic-form');
                if (dynamicForm) {
                    dynamicForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        const token = getToken();
                        const API_BASE_URL = '{{ config("app.url") }}/api';
                        
                        if (!token) {
                            window.location.href = '{{ route("login") }}';
                            return;
                        }
                        
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        
                        fetch(`${API_BASE_URL}/site-visits`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                showAlert('Site visit scheduled successfully!', 'success');
                                setTimeout(() => {
                                    window.location.href = '{{ route("sales-manager.prospects") }}';
                                }, 1500);
                            } else {
                                showAlert(result.message || 'Failed to schedule site visit', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showAlert('Network error. Please try again.', 'error');
                        });
                    });
                }
            });
        </script>
    @else
    <!-- Original Form -->
    <form id="siteVisitForm" enctype="multipart/form-data">
        @csrf
        
        <!-- Customer Information -->
        <div class="form-group">
            <label for="customer_name">Customer Name <span class="required">*</span></label>
            <input type="text" id="customer_name" name="customer_name" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone <span class="required">*</span></label>
            <input type="tel" id="phone" name="phone" maxlength="16" required>
        </div>

        <div class="form-group">
            <label for="employee">Employee</label>
            <input type="text" id="employee" name="employee" readonly value="{{ auth()->user()->name }}">
        </div>

        <div class="form-group">
            <label for="occupation">Occupation</label>
            <input type="text" id="occupation" name="occupation" placeholder="e.g. IT / Business">
        </div>

        <div class="form-group">
            <label for="date_of_visit">Date of Visit <span class="required">*</span></label>
            <input type="date" id="date_of_visit" name="date_of_visit" required>
        </div>

        <div class="form-group">
            <label for="project">Project</label>
            <input type="text" id="project" name="project" placeholder="Project name">
        </div>

        <div class="form-group">
            <label for="budget_range">Budget Range <span class="required">*</span></label>
            <select id="budget_range" name="budget_range" required>
                <option value="">Select Budget Range</option>
                <option value="Under 50 Lac">Under 50 Lac</option>
                <option value="50 Lac – 1 Cr">50 Lac – 1 Cr</option>
                <option value="1 Cr – 2 Cr">1 Cr – 2 Cr</option>
                <option value="2 Cr – 3 Cr">2 Cr – 3 Cr</option>
                <option value="Above 3 Cr">Above 3 Cr</option>
            </select>
        </div>

        <div class="form-group">
            <label for="team_leader">Select TL <span class="required">*</span></label>
            <select id="team_leader" name="team_leader" required>
                <option value="">Select Team Leader</option>
                <option value="Admin">Admin</option>
                <option value="Alpish">Alpish</option>
                <option value="Akash">Akash</option>
                <option value="Omkar">Omkar</option>
                <option value="Shushank">Shushank</option>
            </select>
        </div>

        <div class="form-group">
            <label for="property_type">Property Type <span class="required">*</span></label>
            <select id="property_type" name="property_type" required>
                <option value="">Select Property Type</option>
                <option value="Plot/Villa">Plot/Villa</option>
                <option value="Flat">Flat</option>
                <option value="Commercial">Commercial</option>
                <option value="Just Exploring">Just Exploring</option>
            </select>
        </div>

        <div class="form-group">
            <label for="payment_mode">Payment Mode <span class="required">*</span></label>
            <select id="payment_mode" name="payment_mode" required>
                <option value="">Select Payment Mode</option>
                <option value="Self Fund">Self Fund</option>
                <option value="Loan">Loan</option>
            </select>
        </div>

        <div class="form-group">
            <label for="tentative_period">Tentative Finalisation Period <span class="required">*</span></label>
            <select id="tentative_period" name="tentative_period" required>
                <option value="">Select Period</option>
                <option value="Within 1 Month">Within 1 Month</option>
                <option value="Within 3 Months">Within 3 Months</option>
                <option value="Within 6 Months">Within 6 Months</option>
                <option value="More than 6 Months">More than 6 Months</option>
            </select>
        </div>

        <div class="form-group">
            <label for="lead_type">Lead Type <span class="required">*</span></label>
            <select id="lead_type" name="lead_type" required>
                <option value="">Select Lead Type</option>
                <option value="New Visit">New Visit</option>
                <option value="Revisited">Revisited</option>
                <option value="Meeting">Meeting</option>
                <option value="Prospect">Prospect</option>
            </select>
        </div>

        <div class="form-group">
            <label for="property_name">Property Name</label>
            <input type="text" id="property_name" name="property_name" placeholder="Property name">
        </div>

        <div class="form-group form-wide">
            <label for="property_address">Property Address</label>
            <textarea id="property_address" name="property_address" rows="3" placeholder="Property address..."></textarea>
        </div>

        <div class="form-group">
            <label for="scheduled_at">Scheduled Date & Time <span class="required">*</span></label>
            <input type="datetime-local" id="scheduled_at" name="scheduled_at" required>
        </div>

        <div class="form-group form-wide">
            <label for="visit_notes">Visit Notes</label>
            <textarea id="visit_notes" name="visit_notes" rows="4" placeholder="Additional notes..."></textarea>
        </div>

        <div class="form-group form-wide">
            <label for="photos">Photos (Multiple, max 5MB each)</label>
            <input type="file" id="photos" name="photos[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
            <div class="form-meta-note">You can select multiple images (JPEG, PNG, WEBP), maximum 5MB each.</div>
            <div id="photoPreview" class="photo-preview"></div>
        </div>

        <!-- Hidden fields for lead/prospect -->
        <input type="hidden" id="lead_id" name="lead_id" value="{{ request('lead_id') }}">
        <input type="hidden" id="prospect_id" name="prospect_id" value="{{ request('prospect_id') }}">

        <div class="form-actions">
            <div class="form-actions-copy">Site visit entry will be added to the CRM pipeline and linked lead activity.</div>
            <div class="form-actions-buttons">
                <a href="{{ route('sales-manager.prospects') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-location-dot"></i>Schedule Site Visit
                </button>
            </div>
        </div>
    </form>
    @endif
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    
    function getToken() {
        return localStorage.getItem('sales_manager_token') || '{{ session("api_token") }}';
    }

    // Photo preview
    document.getElementById('photos')?.addEventListener('change', function(e) {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).forEach((file, index) => {
            if (file.size > 5 * 1024 * 1024) {
                showAlert('File ' + file.name + ' exceeds 5MB limit', 'error');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'photo-preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-photo" onclick="removePhoto(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    function removePhoto(index) {
        const input = document.getElementById('photos');
        const dt = new DataTransfer();
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }

    // Form submission
    document.getElementById('siteVisitForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const token = getToken();
        
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        try {
            const response = await fetch(`${API_BASE_URL}/site-visits`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: formData,
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showAlert('Site visit scheduled successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("sales-manager.prospects") }}';
                }, 1500);
            } else {
                showAlert(result.message || 'Failed to schedule site visit', 'error');
                if (result.errors) {
                    console.error('Validation errors:', result.errors);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Network error. Please try again.', 'error');
        }
    });

    function showAlert(message, type) {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }

    // Set minimum date to today
    const dateOfVisitInput = document.getElementById('date_of_visit');
    const scheduledAtInput = document.getElementById('scheduled_at');
    if (dateOfVisitInput) dateOfVisitInput.min = new Date().toISOString().split('T')[0];
    if (scheduledAtInput) scheduledAtInput.min = new Date().toISOString().slice(0, 16);

    // Pre-fill form from meeting if meeting_id is in URL
    async function preFillFromMeeting() {
        const urlParams = new URLSearchParams(window.location.search);
        const meetingId = urlParams.get('meeting_id');
        const siteVisitId = urlParams.get('site_visit_id');
        
        if (siteVisitId) {
            // If site visit already created, load it
            try {
                const response = await fetch(`${API_BASE_URL}/site-visits/${siteVisitId}`, {
                    headers: {
                        'Authorization': `Bearer ${getToken()}`,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data) {
                    fillFormFromData(data);
                }
            } catch (error) {
                console.error('Error loading site visit:', error);
            }
        } else if (meetingId) {
            // Load meeting data and pre-fill
            try {
                const response = await fetch(`${API_BASE_URL}/meetings/${meetingId}`, {
                    headers: {
                        'Authorization': `Bearer ${getToken()}`,
                        'Accept': 'application/json',
                    },
                });
                const result = await response.json();
                if (result && result.data) {
                    fillFormFromMeetingData(result.data);
                }
            } catch (error) {
                console.error('Error loading meeting:', error);
            }
        }
    }

    function fillFormFromMeetingData(meeting) {
        if (meeting.customer_name) document.getElementById('customer_name').value = meeting.customer_name;
        if (meeting.phone) document.getElementById('phone').value = meeting.phone;
        if (meeting.employee) document.getElementById('employee').value = meeting.employee;
        if (meeting.occupation) document.getElementById('occupation').value = meeting.occupation;
        if (meeting.date_of_visit) {
            const date = new Date(meeting.date_of_visit);
            document.getElementById('date_of_visit').value = date.toISOString().split('T')[0];
        }
        if (meeting.project) document.getElementById('project').value = meeting.project;
        if (meeting.budget_range) document.getElementById('budget_range').value = meeting.budget_range;
        if (meeting.team_leader) document.getElementById('team_leader').value = meeting.team_leader;
        if (meeting.property_type) document.getElementById('property_type').value = meeting.property_type;
        if (meeting.payment_mode) document.getElementById('payment_mode').value = meeting.payment_mode;
        if (meeting.tentative_period) document.getElementById('tentative_period').value = meeting.tentative_period;
        if (meeting.lead_type) document.getElementById('lead_type').value = meeting.lead_type;
        if (meeting.property_name) document.getElementById('property_name').value = meeting.property_name;
        if (meeting.lead_id) document.getElementById('lead_id').value = meeting.lead_id;
        if (meeting.prospect_id) document.getElementById('prospect_id').value = meeting.prospect_id;
        
        // Set scheduled_at to next day from meeting
        if (meeting.scheduled_at) {
            const nextDay = new Date(meeting.scheduled_at);
            nextDay.setDate(nextDay.getDate() + 1);
            document.getElementById('scheduled_at').value = nextDay.toISOString().slice(0, 16);
        }
    }

    function fillFormFromData(data) {
        // Similar to fillFormFromMeetingData but for site visit data
        if (data.customer_name) document.getElementById('customer_name').value = data.customer_name;
        if (data.phone) document.getElementById('phone').value = data.phone;
        if (data.employee) document.getElementById('employee').value = data.employee;
        if (data.occupation) document.getElementById('occupation').value = data.occupation;
        if (data.date_of_visit) {
            const date = new Date(data.date_of_visit);
            document.getElementById('date_of_visit').value = date.toISOString().split('T')[0];
        }
        if (data.project) document.getElementById('project').value = data.project;
        if (data.budget_range) document.getElementById('budget_range').value = data.budget_range;
        if (data.team_leader) document.getElementById('team_leader').value = data.team_leader;
        if (data.property_type) document.getElementById('property_type').value = data.property_type;
        if (data.payment_mode) document.getElementById('payment_mode').value = data.payment_mode;
        if (data.tentative_period) document.getElementById('tentative_period').value = data.tentative_period;
        if (data.lead_type) document.getElementById('lead_type').value = data.lead_type;
        if (data.property_name) document.getElementById('property_name').value = data.property_name;
        if (data.property_address) document.getElementById('property_address').value = data.property_address;
        if (data.lead_id) document.getElementById('lead_id').value = data.lead_id;
        if (data.scheduled_at) {
            const date = new Date(data.scheduled_at);
            document.getElementById('scheduled_at').value = date.toISOString().slice(0, 16);
        }
    }

    // Initialize on page load
    (function() {
        preFillFromMeeting();
    })();
</script>
@endpush
