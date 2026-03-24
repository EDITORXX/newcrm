@extends('sales-manager.layout')

@section('title', 'Create Meeting - Senior Manager')
@section('page-title', 'Create Meeting')

@push('styles')
<style>
    .form-container {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 800px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
        font-size: 14px;
    }
    .form-group label .required {
        color: #ef4444;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
        background: white;
        color: #333;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #205A44;
        background: white;
    }
    .form-group input[readonly] {
        background: white;
        cursor: not-allowed;
        color: #333;
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
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-primary {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background: #205A44;
        color: white;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        background: #063A1C;
    }
    .btn-secondary {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background: #6b7280;
        color: white;
    }
    .btn-secondary:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        background: #4b5563;
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
</style>
@endpush

@section('content')
<div class="form-container">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Schedule New Meeting</h2>
    
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
                        const token = '{{ $api_token ?? "" }}';
                        const API_BASE_URL = '{{ config("app.url") }}/api';
                        
                        if (!token) {
                            window.location.href = '{{ route("login") }}';
                            return;
                        }
                        
                        fetch(`${API_BASE_URL}/sales-manager/meetings`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Accept': 'application/json',
                            },
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success !== false) {
                                showAlert('Meeting scheduled successfully!', 'success');
                                setTimeout(() => {
                                    window.location.href = '{{ route("sales-manager.meetings") }}';
                                }, 1500);
                            } else {
                                showAlert(data.message || 'Failed to schedule meeting', 'error');
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
    <form id="meetingForm" enctype="multipart/form-data">
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
            <label for="scheduled_at">Scheduled Date & Time <span class="required">*</span></label>
            <input type="datetime-local" id="scheduled_at" name="scheduled_at" required>
        </div>

        <div class="form-group">
            <label for="meeting_notes">Meeting Notes</label>
            <textarea id="meeting_notes" name="meeting_notes" rows="4" placeholder="Additional notes..."></textarea>
        </div>

        <div class="form-group">
            <label for="photos">Photos (Multiple, max 5MB each)</label>
            <input type="file" id="photos" name="photos[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
            <small style="color: #6b7280; font-size: 12px;">You can select multiple images (JPEG, PNG, WEBP)</small>
            <div id="photoPreview" class="photo-preview"></div>
        </div>

        <!-- Hidden fields for lead/prospect -->
        <input type="hidden" id="lead_id" name="lead_id" value="{{ request('lead_id') }}">
        <input type="hidden" id="prospect_id" name="prospect_id" value="{{ request('prospect_id') }}">

        <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-calendar-check mr-2"></i>Schedule Meeting
            </button>
            <a href="{{ route('sales-manager.meetings') }}" class="btn btn-secondary">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    
    function getToken() {
        return localStorage.getItem('sales_manager_token') || '{{ session("api_token") }}';
    }

    // Photo preview
    document.getElementById('photos').addEventListener('change', function(e) {
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
    document.getElementById('meetingForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const token = getToken();
        
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        try {
            const response = await fetch(`${API_BASE_URL}/meetings`, {
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
                showAlert('Meeting scheduled successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("sales-manager.meetings") }}';
                }, 1500);
            } else {
                showAlert(result.message || 'Failed to schedule meeting', 'error');
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
    document.getElementById('date_of_visit').min = new Date().toISOString().split('T')[0];
    document.getElementById('scheduled_at').min = new Date().toISOString().slice(0, 16);
</script>
@endpush

