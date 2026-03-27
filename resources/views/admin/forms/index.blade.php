@extends('layouts.app')

@section('title', 'Forms Management - Admin')
@section('page-title', 'Forms Management')
@section('page-subtitle', 'Manage all forms in the system')

@section('header-actions')
<div style="display: flex; gap: 10px; align-items: center;">
    <!-- Filter Options -->
    <div style="display: flex; gap: 8px; background: white; padding: 4px; border-radius: 8px; border: 1px solid #E5DED4;">
        <a href="{{ route('admin.forms.index', ['filter' => 'all']) }}" 
           style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; {{ (!isset($filter) || $filter === 'all') ? 'background: #205A44; color: white;' : 'color: #063A1C;' }}">
            All
        </a>
        <a href="{{ route('admin.forms.index', ['filter' => 'drafts']) }}" 
           style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; {{ $filter === 'drafts' ? 'background: #205A44; color: white;' : 'color: #063A1C;' }}">
            Drafts
        </a>
        <a href="{{ route('admin.forms.index', ['filter' => 'published']) }}" 
           style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500; {{ $filter === 'published' ? 'background: #205A44; color: white;' : 'color: #063A1C;' }}">
            Published
        </a>
    </div>
    <a href="{{ route('admin.forms.test-field-type') }}" class="btn" style="background: #ef4444; color: white; text-decoration: none;">
        <i class="fas fa-bug" style="margin-right: 5px;"></i> Test Field Type
    </a>
</div>
@endsection

@push('styles')
<style>
    .forms-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }
    .form-card {
        background: white;
        border: 1px solid #e7e0d7;
        border-radius: 22px;
        padding: 28px;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        min-height: 100%;
    }
    .form-card:hover {
        box-shadow: 0 14px 32px rgba(17, 24, 39, 0.08);
        transform: translateY(-2px);
    }
    .form-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 12px;
    }
    .form-card-title {
        font-size: 19px;
        line-height: 1.35;
        font-weight: 700;
        color: var(--text-color);
        margin: 0;
    }
    .form-card-badge {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .badge-custom {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-existing {
        background: #fef3c7;
        color: #92400e;
    }
    .form-card-meta {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 10px;
        flex: 1;
    }
    .form-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        color: #5b6473;
        line-height: 1.5;
    }
    .form-meta-item i {
        width: 16px;
        color: #205A44;
        margin-top: 3px;
    }
    .form-card-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid #E5DED4;
    }
    .form-action-btn {
        flex: 1;
        padding: 12px 16px;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-edit {
        background: #205A44;
        color: white;
    }
    .btn-edit:hover {
        background: #063A1C;
    }
    .btn-view {
        background: #e0e0e0;
        color: #063A1C;
    }
    .btn-view:hover {
        background: #d0d0d0;
    }
    .btn-delete {
        background: #ef4444;
        color: white;
    }
    .btn-delete:hover {
        background: #dc2626;
    }
    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-color);
        margin: 30px 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #E5DED4;
    }
    .section-title:first-child {
        margin-top: 0;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #B3B5B4;
    }
    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
    }
    .form-preview-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }
    .form-preview-modal.active {
        display: flex;
    }
    .modal-content-preview {
        background: white;
        border-radius: 12px;
        padding: 30px;
        max-width: 90%;
        max-height: 90vh;
        width: 800px;
        overflow-y: auto;
        position: relative;
    }
    .modal-header-preview {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #E5DED4;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .modal-close:hover {
        background: #f0f0f0;
    }
    .modal-iframe {
        width: 100%;
        height: 600px;
        border: 1px solid #E5DED4;
        border-radius: 8px;
    }
    .form-card-summary {
        color: #5b6473;
        margin: 0 0 14px 0;
        font-size: 15px;
        line-height: 1.6;
        min-height: 72px;
    }
    @media (max-width: 768px) {
        .forms-container {
            grid-template-columns: 1fr;
            gap: 18px;
        }
        .form-card {
            padding: 22px;
            border-radius: 18px;
        }
        .form-card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .form-card-summary {
            min-height: auto;
        }
        .form-card-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    @if(session('success'))
        <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Existing Forms Section -->
    @if(count($existingForms) > 0)
        <h2 class="section-title">Existing Forms in System</h2>
        <div class="forms-container">
            @foreach($existingForms as $form)
                <div class="form-card">
                    <div class="form-card-header">
                        <h3 class="form-card-title">{{ $form['name'] }}</h3>
                        <span class="form-card-badge badge-existing">Existing</span>
                    </div>
                    @if(!empty($form['description']))
                        <p class="form-card-summary">{{ $form['description'] }}</p>
                    @endif
                    <div class="form-card-meta">
                        <div class="form-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><strong>Location:</strong> {{ $form['location'] }}</span>
                        </div>
                        <div class="form-meta-item">
                            <i class="fas fa-tag"></i>
                            <span><strong>Type:</strong> {{ ucfirst($form['type']) }}</span>
                        </div>
                        <div class="form-meta-item">
                            <i class="fas fa-code"></i>
                            <span><strong>Path:</strong> {{ $form['path'] }}</span>
                        </div>
                    </div>
                    <div class="form-card-actions">
                        <button type="button" onclick="viewForm('{{ $form['route'] }}', '{{ $form['name'] }}', '{{ $form['path'] }}')" class="form-action-btn btn-view" style="border: none;">
                            <i class="fas fa-eye"></i> View Form
                        </button>
                        <a href="{{ $form['edit_url'] }}" class="form-action-btn btn-edit" style="border: none; text-decoration: none; text-align: center;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Custom Forms Section -->
    <h2 class="section-title">Custom Forms</h2>
    @if(count($customForms) > 0)
        <div class="forms-container">
            @foreach($customForms as $form)
                <div class="form-card">
                    <div class="form-card-header">
                        <h3 class="form-card-title">{{ $form->name }}</h3>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span class="form-card-badge badge-custom">Custom</span>
                            <span class="form-card-badge" style="background: {{ $form->status === 'published' ? '#d1fae5' : '#fef3c7' }}; color: {{ $form->status === 'published' ? '#065f46' : '#92400e' }};">
                                {{ ucfirst($form->status) }}
                            </span>
                        </div>
                    </div>
                    @if($form->description)
                        <p class="form-card-summary">{{ $form->description }}</p>
                    @endif
                    <div class="form-card-meta">
                        <div class="form-meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><strong>Location:</strong> {{ $form->location_path }}</span>
                        </div>
                        <div class="form-meta-item">
                            <i class="fas fa-tag"></i>
                            <span><strong>Type:</strong> {{ ucfirst($form->form_type) }}</span>
                        </div>
                        <div class="form-meta-item">
                            <i class="fas fa-list"></i>
                            <span><strong>Fields:</strong> {{ $form->fields->count() }} fields</span>
                        </div>
                        <div class="form-meta-item">
                            <i class="fas fa-user"></i>
                            <span><strong>Created by:</strong> {{ $form->creator->name ?? 'System' }}</span>
                        </div>
                        <div class="form-meta-item">
                            <i class="fas fa-clock"></i>
                            <span><strong>Created:</strong> {{ $form->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="form-meta-item">
                            <i class="fas fa-{{ $form->is_active ? 'check-circle text-green-600' : 'times-circle text-red-600' }}"></i>
                            <span><strong>Status:</strong> {{ $form->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        @if($form->replacedForm)
                            <div class="form-meta-item" style="background: #fff3cd; padding: 8px; border-radius: 4px; border-left: 3px solid #ffc107;">
                                <i class="fas fa-exchange-alt" style="color: #856404;"></i>
                                <span style="color: #856404;"><strong>Replaces:</strong> {{ $form->replacedForm->name }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="form-card-actions">
                        <a href="{{ route('admin.forms.edit', $form->id) }}" class="form-action-btn btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.forms.destroy', $form->id) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Are you sure you want to delete this form?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="form-action-btn btn-delete" style="width: 100%;">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Linked Forms Yet</h3>
            <p>The approved forms will appear here after their first edit and publish.</p>
        </div>
    @endif
</div>

<!-- Form Preview Modal -->
<div id="formPreviewModal" class="form-preview-modal">
    <div class="modal-content-preview">
        <div class="modal-header-preview">
            <h3 id="previewModalTitle" style="margin: 0; font-size: 20px; font-weight: 600;">Form Preview</h3>
            <button type="button" class="modal-close" onclick="closeFormPreview()">&times;</button>
        </div>
        <iframe id="formPreviewIframe" class="modal-iframe" src="about:blank"></iframe>
    </div>
</div>


@push('scripts')
<script>
    const existingPreviewBase = '{{ url("admin/forms/existing-preview") }}';

    function viewForm(url, formName, formPath) {
        document.getElementById('previewModalTitle').textContent = formName + ' - Preview';

        // Existing system forms: use dedicated field-preview endpoint
        if (formPath) {
            document.getElementById('formPreviewIframe').src = existingPreviewBase + '/' + encodeURIComponent(formPath);
        } else {
            // Custom forms: load actual form URL
            if (!url || url === '#') {
                alert('Form URL is not available');
                return;
            }
            document.getElementById('formPreviewIframe').src = url;
        }

        document.getElementById('formPreviewModal').classList.add('active');
    }
    
    function closeFormPreview() {
        document.getElementById('formPreviewModal').classList.remove('active');
        document.getElementById('formPreviewIframe').src = 'about:blank';
    }
    
    // Close modal on outside click
    window.onclick = function(event) {
        const previewModal = document.getElementById('formPreviewModal');
        if (event.target == previewModal) {
            closeFormPreview();
        }
    }
    
    // Add test page link as floating button
    const testLink = document.createElement('a');
    testLink.href = '{{ route("admin.forms.test-field-type") }}';
    testLink.innerHTML = '<i class="fas fa-bug"></i> Test Field Type';
    testLink.style.cssText = 'position: fixed; bottom: 20px; right: 20px; padding: 12px 20px; background: #ef4444; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000;';
    testLink.onmouseover = function() { this.style.background = '#dc2626'; };
    testLink.onmouseout = function() { this.style.background = '#ef4444'; };
    document.body.appendChild(testLink);
</script>
@endpush
@endsection
