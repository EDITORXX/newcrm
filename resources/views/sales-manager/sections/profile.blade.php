@extends('sales-manager.layout')

@section('title', 'Profile - Senior Manager')
@section('page-title', 'Profile')

@push('styles')
<style>
    .profile-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 24px;
        border-bottom: 2px solid #f0f0f0;
    }
    .avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 32px;
        font-weight: 700;
        margin-right: 20px;
        position: relative;
        overflow: hidden;
    }
    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .avatar-upload {
        position: relative;
        display: inline-block;
    }
    .avatar-upload-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #205A44;
        color: white;
        border: 2px solid white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
    }
    .avatar-upload-btn:hover {
        background: #5568d3;
    }
    .avatar-upload input[type="file"] {
        display: none;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        margin-top: 10px;
        display: none;
    }
    .image-preview.show {
        display: block;
    }
    .profile-info h2 {
        font-size: 24px;
        font-weight: 700;
        color: #063A1C;
        margin-bottom: 4px;
    }
    .profile-info p {
        color: #B3B5B4;
        font-size: 14px;
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
    .form-group input, .form-group select, .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s;
        background: #ffffff;
        color: #063A1C;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none;
        border-color: #205A44;
        background: #ffffff;
    }
    .form-group input[readonly] {
        background: #f5f5f5;
        cursor: not-allowed;
    }
    .password-input-wrapper {
        position: relative;
    }
    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #B3B5B4;
        cursor: pointer;
        padding: 4px 8px;
        font-size: 16px;
    }
    .password-toggle:hover {
        color: #205A44;
    }
    .form-group input[type="password"],
    .form-group input[type="text"][data-password-field] {
        padding-right: 45px;
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
    @media (max-width: 768px) {
        .profile-card {
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .profile-header {
            flex-direction: column;
            align-items: flex-start;
            text-align: center;
        }
        
        .avatar {
            width: 64px;
            height: 64px;
            font-size: 24px;
            margin-right: 0;
            margin-bottom: 12px;
        }
        
        .profile-info {
            width: 100%;
            text-align: center;
        }
        
        .profile-info h2 {
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px;
            font-size: 14px;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            font-size: 14px;
        }
        
        .form-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .form-actions button {
            width: 100%;
        }
        
        /* Table responsive */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table-responsive table {
            min-width: 600px;
        }
        
        .table-responsive th,
        .table-responsive td {
            padding: 8px 12px;
            font-size: 12px;
        }
        
        /* Grid responsive */
        .grid {
            grid-template-columns: 1fr !important;
            gap: 16px;
        }
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
        background: #5568d3;
    }
    .btn-success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background: #10b981;
        color: white;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        background: #059669;
    }
    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    .card-title i {
        margin-right: 10px;
        color: #205A44;
    }
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        width: 150px;
        font-weight: 500;
        color: #B3B5B4;
    }
    .info-value {
        flex: 1;
        color: #063A1C;
    }
    .activity-table {
        width: 100%;
        border-collapse: collapse;
    }
    .activity-table th,
    .activity-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }
    .activity-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #B3B5B4;
        font-size: 12px;
        text-transform: uppercase;
    }
    .activity-table td {
        color: #063A1C;
        font-size: 14px;
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
    .hidden {
        display: none;
    }
    .edit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid #f0f0f0;
    }
    .edit-actions {
        display: flex;
        gap: 10px;
    }
    .team-member-card {
        display: flex;
        align-items: center;
        padding: 16px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.3s;
    }
    .team-member-card:hover {
        border-color: #205A44;
        box-shadow: 0 2px 8px rgba(32, 90, 68, 0.1);
    }
    .team-member-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #205A44 0%, #063A1C 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
        font-weight: 600;
        margin-right: 16px;
        flex-shrink: 0;
    }
    .team-member-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .team-member-info {
        flex: 1;
    }
    .team-member-name {
        font-weight: 600;
        color: #063A1C;
        margin-bottom: 4px;
    }
    .team-member-role {
        font-size: 13px;
        color: #B3B5B4;
    }
    .team-member-status {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-available {
        background: #d1fae5;
        color: #065f46;
    }
    .status-absent {
        background: #fee2e2;
        color: #991b1b;
    }
    .stat-badge {
        display: inline-block;
        padding: 4px 8px;
        background: #f0f0f0;
        border-radius: 6px;
        font-size: 12px;
        color: #666;
        margin-left: 8px;
    }
</style>
@endpush

@section('content')
    <!-- Profile Header -->
    <div class="profile-card">
        <div class="edit-header">
            <h2 style="font-size: 24px; font-weight: 700; color: #063A1C; margin: 0;">Profile</h2>
            <div class="edit-actions">
                <button type="button" id="saveChangesBtn" class="btn btn-success" onclick="saveAllChanges()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
        <div class="profile-header">
            <div class="avatar-upload">
                <div class="avatar" id="avatar">
                    <span id="avatarInitial">S</span>
                    <img id="avatarImage" src="" alt="Profile Picture" style="display: none;">
                </div>
                <label for="profilePictureInput" class="avatar-upload-btn" id="profilePictureLabel" title="Upload Profile Picture">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="profilePictureInput" accept="image/jpeg,image/jpg,image/png">
            </div>
            <div class="profile-info">
                <h2 id="profileName">Senior Manager</h2>
                <p id="profileEmail">Loading...</p>
            </div>
        </div>
        <div id="profilePictureAlert"></div>
        <div style="margin-top: 16px;">
            <img id="imagePreview" class="image-preview" alt="Preview">
            <div id="uploadActions" style="display: none; margin-top: 10px;">
                <button type="button" class="btn btn-primary" onclick="uploadProfilePicture()">Upload Picture</button>
                <button type="button" class="btn" style="background: #6b7280; color: white; margin-left: 10px;" onclick="cancelPictureUpload()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Personal Information Card -->
    <div class="profile-card" id="personalInfoCard">
        <div class="card-title">
            <i class="fas fa-user"></i>
            Personal Information
        </div>
        <div id="personalInfoAlert"></div>
        <form id="personalInfoForm">
            <div class="form-group">
                <label for="profileNameInput">Name *</label>
                <input type="text" id="profileNameInput" name="name" required>
            </div>
            <div class="form-group">
                <label for="profilePhoneInput">Phone</label>
                <input type="text" id="profilePhoneInput" name="phone">
            </div>
            <div class="form-group">
                <label for="profileEmailInput">Email *</label>
                <input type="email" id="profileEmailInput" name="email" required>
            </div>
            <div class="info-row">
                <div class="info-label">Role</div>
                <div class="info-value" id="profileRole">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">Manager</div>
                <div class="info-value" id="profileManager">-</div>
            </div>
            <div class="info-row">
                <div class="info-label">Joining Date</div>
                <div class="info-value" id="profileJoinDate">-</div>
            </div>
        </form>
    </div>

    <!-- Team Members Card -->
    <div class="profile-card">
        <div class="card-title">
            <i class="fas fa-users"></i>
            My Team
            <span class="stat-badge" id="teamStatsTotal">0 members</span>
        </div>
        <div id="teamMembersContainer">
            <p style="text-align: center; color: #B3B5B4; padding: 20px;">Loading team members...</p>
        </div>
    </div>

    <!-- Password Change Card -->
    <div class="profile-card" id="passwordCard">
        <div class="card-title">
            <i class="fas fa-lock"></i>
            Change Password
        </div>
        <div id="passwordAlert"></div>
        <form id="passwordForm">
            <div class="form-group">
                <label for="currentPassword">Current Password *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="currentPassword" name="current_password" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('currentPassword')" title="Show/Hide Password">
                        <i class="fas fa-eye" id="currentPasswordToggleIcon"></i>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="newPassword" name="new_password" required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('newPassword')" title="Show/Hide Password">
                        <i class="fas fa-eye" id="newPasswordToggleIcon"></i>
                    </button>
                </div>
                <small style="color: #B3B5B4; font-size: 12px; margin-top: 4px; display: block;">Minimum 8 characters</small>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirmPassword" name="new_password_confirmation" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirmPassword')" title="Show/Hide Password">
                        <i class="fas fa-eye" id="confirmPasswordToggleIcon"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Activity History Card -->
    <div class="profile-card">
        <div class="card-title">
            <i class="fas fa-history"></i>
            Recent Activity
        </div>
        <div id="activityHistory">
            <p style="text-align: center; color: #B3B5B4; padding: 20px;">Loading...</p>
        </div>
    </div>

    <!-- Logout Card -->
    <div class="profile-card">
        <div class="card-title">
            <i class="fas fa-sign-out-alt"></i>
            Account Actions
        </div>
        <div style="padding: 20px 0;">
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger" style="width: 100%; max-width: 300px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const API_BASE_URL = '{{ url("/api/sales-manager") }}';
    
    // Get token from localStorage or session
    function getToken() {
        return localStorage.getItem('sales_manager_token') || '{{ session("api_token") }}';
    }

    // API call helper
    async function apiCall(endpoint, options = {}) {
        const token = getToken();
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return null;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers },
                credentials: 'same-origin',
            });

            if (response.status === 401) {
                localStorage.removeItem('sales_manager_token');
                window.location.href = '{{ route("login") }}';
                return null;
            }

            if (!response.ok) {
                const errorText = await response.text();
                try {
                    return JSON.parse(errorText);
                } catch (e) {
                    return { success: false, message: errorText };
                }
            }

            return await response.json();
        } catch (error) {
            console.error('API Call Error:', error);
            return { success: false, message: error.message };
        }
    }

    // Show alert message
    function showAlert(containerId, message, type = 'success') {
        const container = document.getElementById(containerId);
        container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }

    // Load profile data
    async function loadProfile() {
        try {
            const data = await apiCall('/profile');
            
            if (!data || !data.user) {
                console.error('Failed to load profile');
                return;
            }

            const user = data.user;

            // Update header
            document.getElementById('profileName').textContent = user.name || 'Senior Manager';
            document.getElementById('profileEmail').textContent = user.email || 'No email';
            document.getElementById('avatarInitial').textContent = (user.name || 'S').charAt(0).toUpperCase();
            
            // Update profile picture
            updateAvatarDisplay(user.profile_picture, user.name);

            // Update personal info form
            document.getElementById('profileNameInput').value = user.name || '';
            document.getElementById('profileNameInput').defaultValue = user.name || '';
            document.getElementById('profilePhoneInput').value = user.phone || '';
            document.getElementById('profilePhoneInput').defaultValue = user.phone || '';
            document.getElementById('profileEmailInput').value = user.email || '';
            document.getElementById('profileEmailInput').defaultValue = user.email || '';
            
            // Update role, manager, and joining date
            document.getElementById('profileRole').textContent = user.role || '-';
            document.getElementById('profileManager').textContent = user.manager || 'Not Assigned';
            document.getElementById('profileJoinDate').textContent = user.created_at || '-';

            // Load team members
            if (data.team_members) {
                loadTeamMembers(data.team_members, data.team_stats);
            }

            // Load activity history
            if (data.activity_history) {
                loadActivityHistory(data.activity_history);
            }
        } catch (error) {
            console.error('Error loading profile:', error);
        }
    }

    // Load team members
    function loadTeamMembers(teamMembers, teamStats) {
        const container = document.getElementById('teamMembersContainer');
        const statsTotal = document.getElementById('teamStatsTotal');
        
        if (teamStats) {
            statsTotal.textContent = `${teamStats.total_members} members (${teamStats.available_members} available)`;
        }
        
        if (!teamMembers || teamMembers.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #B3B5B4; padding: 20px;">No team members found</p>';
            return;
        }

        const html = teamMembers.map(member => `
            <div class="team-member-card">
                <div class="team-member-avatar">
                    ${member.profile_picture ? 
                        `<img src="${member.profile_picture}" alt="${member.name}">` : 
                        member.name.charAt(0).toUpperCase()
                    }
                </div>
                <div class="team-member-info">
                    <div class="team-member-name">${member.name}</div>
                    <div class="team-member-role">${member.role} • ${member.email}</div>
                </div>
                <div style="text-align: right;">
                    <span class="team-member-status ${member.is_absent ? 'status-absent' : 'status-available'}">
                        ${member.is_absent ? 'Absent' : 'Available'}
                    </span>
                    ${member.today_prospects ? `<div class="stat-badge" style="margin-top: 4px;">Today: ${member.today_prospects} prospects</div>` : ''}
                </div>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }

    // Load activity history
    function loadActivityHistory(activities) {
        const container = document.getElementById('activityHistory');
        
        if (!activities || activities.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #B3B5B4; padding: 20px;">No activity history found</p>';
            return;
        }

        const table = `
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    ${activities.map(activity => `
                        <tr>
                            <td>${activity.action}</td>
                            <td>${activity.ip || 'N/A'}</td>
                            <td>${new Date(activity.created_at).toLocaleString()}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        container.innerHTML = table;
    }

    // Update avatar display
    function updateAvatarDisplay(profilePictureUrl, userName) {
        const avatarImage = document.getElementById('avatarImage');
        const avatarInitial = document.getElementById('avatarInitial');
        
        if (profilePictureUrl) {
            avatarImage.src = profilePictureUrl;
            avatarImage.style.display = 'block';
            avatarInitial.style.display = 'none';
        } else {
            avatarImage.style.display = 'none';
            avatarInitial.style.display = 'block';
            avatarInitial.textContent = userName.charAt(0).toUpperCase();
        }
    }

    // Handle profile picture file selection
    let selectedFile = null;
    document.getElementById('profilePictureInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.match('image.*')) {
            showAlert('profilePictureAlert', 'Please select a valid image file', 'error');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            showAlert('profilePictureAlert', 'Image size must be less than 2MB', 'error');
            return;
        }

        selectedFile = file;

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.classList.add('show');
            document.getElementById('uploadActions').style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    // Upload profile picture
    async function uploadProfilePicture() {
        if (!selectedFile) {
            showAlert('profilePictureAlert', 'Please select an image first', 'error');
            return;
        }

        const token = getToken();
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return;
        }

        const uploadBtn = document.querySelector('#uploadActions button.btn-primary');
        const originalText = uploadBtn.textContent;
        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading...';

        const formData = new FormData();
        formData.append('profile_picture', selectedFile);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        try {
            const response = await fetch(`${API_BASE_URL}/profile/picture`, {
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

            if (response.status === 401) {
                localStorage.removeItem('sales_manager_token');
                window.location.href = '{{ route("login") }}';
                return;
            }

            const result = await response.json();

            if (result.success) {
                showAlert('profilePictureAlert', 'Profile picture uploaded successfully!', 'success');
                updateAvatarDisplay(result.profile_picture, document.getElementById('profileName').textContent);
                cancelPictureUpload();
            } else {
                showAlert('profilePictureAlert', result.message || 'Failed to upload profile picture', 'error');
            }

            uploadBtn.disabled = false;
            uploadBtn.textContent = originalText;
        } catch (error) {
            console.error('Upload Error:', error);
            showAlert('profilePictureAlert', 'Network error: Unable to upload picture', 'error');
            uploadBtn.disabled = false;
            uploadBtn.textContent = originalText;
        }
    }

    // Cancel picture upload
    function cancelPictureUpload() {
        selectedFile = null;
        document.getElementById('profilePictureInput').value = '';
        document.getElementById('imagePreview').classList.remove('show');
        document.getElementById('uploadActions').style.display = 'none';
    }

    // Toggle password visibility
    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + 'ToggleIcon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Save all changes
    async function saveAllChanges() {
        try {
            const nameInput = document.getElementById('profileNameInput');
            const emailInput = document.getElementById('profileEmailInput');
            const phoneInput = document.getElementById('profilePhoneInput');
            
            const nameChanged = nameInput.value !== (nameInput.defaultValue || '');
            const emailChanged = emailInput.value !== (emailInput.defaultValue || '');
            const phoneChanged = phoneInput.value !== (phoneInput.defaultValue || '');
            
            const currentPassword = document.getElementById('currentPassword')?.value || '';
            const newPassword = document.getElementById('newPassword')?.value || '';
            const confirmPassword = document.getElementById('confirmPassword')?.value || '';
            
            const profilePictureSelected = selectedFile !== null;
            
            let hasChanges = nameChanged || emailChanged || phoneChanged || (newPassword && currentPassword) || profilePictureSelected;
            
            if (!hasChanges) {
                showAlert('personalInfoAlert', 'No changes to save', 'error');
                return;
            }
            
            let allSuccess = true;
            
            // Save profile info if changed
            if (nameChanged || emailChanged || phoneChanged) {
                const formData = {
                    name: nameInput.value,
                    email: emailInput.value,
                    phone: phoneInput.value,
                };

                const result = await apiCall('/profile', {
                    method: 'PUT',
                    body: JSON.stringify(formData),
                });

                if (result && result.success) {
                    showAlert('personalInfoAlert', 'Profile updated successfully', 'success');
                    
                    if (result.user) {
                        document.getElementById('profileName').textContent = result.user.name;
                        document.getElementById('profileEmail').textContent = result.user.email;
                        document.getElementById('avatarInitial').textContent = result.user.name.charAt(0).toUpperCase();
                    }
                    
                    nameInput.defaultValue = nameInput.value;
                    emailInput.defaultValue = emailInput.value;
                    phoneInput.defaultValue = phoneInput.value;
                } else {
                    showAlert('personalInfoAlert', result?.message || 'Failed to update profile', 'error');
                    allSuccess = false;
                }
            }
            
            // Save password if changed
            if (newPassword && currentPassword) {
                if (newPassword !== confirmPassword) {
                    showAlert('passwordAlert', 'New passwords do not match', 'error');
                    allSuccess = false;
                } else if (newPassword.length < 8) {
                    showAlert('passwordAlert', 'Password must be at least 8 characters', 'error');
                    allSuccess = false;
                } else {
                    const result = await apiCall('/profile/password', {
                        method: 'POST',
                        body: JSON.stringify({
                            current_password: currentPassword,
                            new_password: newPassword,
                            new_password_confirmation: confirmPassword,
                        }),
                    });

                    if (result && result.success) {
                        showAlert('passwordAlert', 'Password changed successfully', 'success');
                        document.getElementById('currentPassword').value = '';
                        document.getElementById('newPassword').value = '';
                        document.getElementById('confirmPassword').value = '';
                    } else {
                        showAlert('passwordAlert', result?.message || 'Failed to change password', 'error');
                        allSuccess = false;
                    }
                }
            }
            
            // Save profile picture if selected
            if (profilePictureSelected && allSuccess) {
                await uploadProfilePicture();
            }
        } catch (error) {
            console.error('Error in saveAllChanges:', error);
            showAlert('personalInfoAlert', 'An error occurred while saving', 'error');
        }
    }

    // Make functions globally accessible
    window.saveAllChanges = saveAllChanges;
    window.togglePasswordVisibility = togglePasswordVisibility;
    window.uploadProfilePicture = uploadProfilePicture;
    window.cancelPictureUpload = cancelPictureUpload;
    
    // Initialize on page load
    (function() {
        loadProfile();
    })();
</script>
@endpush

