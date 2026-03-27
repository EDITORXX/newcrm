@extends('layouts.app')

@section('title', 'Create Lead - Base CRM')
@section('page-title', 'Create Lead')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('leads.store') }}">
                @csrf

                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Basic Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="Enter lead name"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone"
                                   value="{{ old('phone') }}"
                                   required
                                   placeholder="Enter phone number"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               value="{{ old('email') }}"
                               placeholder="Enter email address"
                               class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                    </div>
                </div>

                @if($showLocationDetails ?? true)
                <!-- Location Details (hidden when CRM creates lead) -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Location Details</h3>
                    
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" id="address" rows="2" placeholder="Enter full address" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" id="city" value="{{ old('city') }}" placeholder="Enter city" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 mb-2">State</label>
                            <input type="text" name="state" id="state" value="{{ old('state') }}" placeholder="Enter state" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                        </div>

                        <div>
                            <label for="pincode" class="block text-sm font-medium text-gray-700 mb-2">Pincode</label>
                            <input type="text" name="pincode" id="pincode" value="{{ old('pincode') }}" placeholder="Enter pincode" class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                        </div>
                    </div>
                </div>
                @endif

                <!-- Property Requirements -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Property Requirements</h3>
                    
                    <div>
                        <label for="preferred_location" class="block text-sm font-medium text-gray-700 mb-2">Preferred Location</label>
                        <select name="preferred_location" 
                                id="preferred_location"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                            <option value="">-- Select Location --</option>
                            <option value="Shaheed Path" {{ old('preferred_location') == 'Shaheed Path' ? 'selected' : '' }}>Shaheed Path</option>
                            <option value="Sultanpur Road" {{ old('preferred_location') == 'Sultanpur Road' ? 'selected' : '' }}>Sultanpur Road</option>
                            <option value="Kanpur Road" {{ old('preferred_location') == 'Kanpur Road' ? 'selected' : '' }}>Kanpur Road</option>
                            <option value="Bijnore Road" {{ old('preferred_location') == 'Bijnore Road' ? 'selected' : '' }}>Bijnore Road</option>
                            <option value="IIM Road" {{ old('preferred_location') == 'IIM Road' ? 'selected' : '' }}>IIM Road</option>
                            <option value="Faizabad Road" {{ old('preferred_location') == 'Faizabad Road' ? 'selected' : '' }}>Faizabad Road</option>
                            <option value="Outer Ring Road" {{ old('preferred_location') == 'Outer Ring Road' ? 'selected' : '' }}>Outer Ring Road</option>
                            <option value="Sushant Golf City" {{ old('preferred_location') == 'Sushant Golf City' ? 'selected' : '' }}>Sushant Golf City</option>
                            <option value="Other" {{ old('preferred_location') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>

                <!-- Property Requirements -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Property Requirements</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="preferred_size" class="block text-sm font-medium text-gray-700 mb-2">Preferred Size</label>
                            <input type="text" 
                                   name="preferred_size" 
                                   id="preferred_size"
                                   value="{{ old('preferred_size') }}"
                                   placeholder="e.g., 2 BHK, 1200 sqft"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                        </div>

                        <div>
                            <label for="property_type" class="block text-sm font-medium text-gray-700 mb-2">Property Type</label>
                            <select name="property_type" 
                                    id="property_type"
                                    class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                                <option value="">-- Select Type --</option>
                                <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Apartment</option>
                                <option value="villa" {{ old('property_type') == 'villa' ? 'selected' : '' }}>Villa</option>
                                <option value="plot" {{ old('property_type') == 'plot' ? 'selected' : '' }}>Plot</option>
                                <option value="commercial" {{ old('property_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                <option value="other" {{ old('property_type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <label for="preferred_projects" class="block text-sm font-medium text-gray-700">Preferred Project</label>
                            @if(auth()->user()->canManageUsers())
                                <button type="button" 
                                        onclick="openCreateProjectModal()"
                                        class="text-sm px-3 py-1 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200">
                                    + Create Project
                                </button>
                            @endif
                        </div>
                        <select name="preferred_projects[]" 
                                id="preferred_projects"
                                multiple
                                size="5"
                                onchange="handleProjectSelection(event)"
                                class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                            @php
                                $selectedProjects = old('preferred_projects', []);
                            @endphp
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" data-name="{{ $project->name }}" {{ in_array($project->id, $selectedProjects) ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-2">Click to select/deselect projects</p>
                        
                        <!-- Selected Projects Display Box -->
                        <div id="selected_projects_box" class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-lg min-h-[60px]">
                            <div class="text-sm text-gray-500" id="no_projects_text">No projects selected</div>
                            <div id="selected_projects_list" class="flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">Budget</label>
                        <select name="budget" 
                                id="budget"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                            <option value="">-- Select Budget --</option>
                            <option value="Under ₹1 Cr" {{ old('budget') == 'Under ₹1 Cr' ? 'selected' : '' }}>Under ₹1 Cr</option>
                            <option value="₹1.1 Cr – ₹2 Cr" {{ old('budget') == '₹1.1 Cr – ₹2 Cr' ? 'selected' : '' }}>₹1.1 Cr – ₹2 Cr</option>
                            <option value="Above ₹2 Cr" {{ old('budget') == 'Above ₹2 Cr' ? 'selected' : '' }}>Above ₹2 Cr</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <label for="source" class="block text-sm font-medium text-gray-700 mb-2">
                            Source @if(auth()->user()->isCrm())<span class="text-red-500">*</span>@endif
                        </label>
                        <select name="source" 
                                id="source"
                                {{ auth()->user()->isCrm() ? 'required' : '' }}
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                            <option value="">-- Select Source --</option>
                            @foreach(\App\Models\Lead::sourceOptions() as $value => $label)
                                <option value="{{ $value }}" {{ old('source') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-6">
                        <label for="use_end_use" class="block text-sm font-medium text-gray-700 mb-2">Use/End Use</label>
                        <select name="use_end_use" 
                                id="use_end_use"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                            <option value="">-- Select Use/End Use --</option>
                            <option value="End User" {{ old('use_end_use') == 'End User' ? 'selected' : '' }}>End User</option>
                            <option value="2nd Investments" {{ old('use_end_use') == '2nd Investments' ? 'selected' : '' }}>2nd Investments</option>
                        </select>
                    </div>
                </div>

                <!-- Possession Status -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Possession Status</h3>
                    
                    <div>
                        <label for="possession_status" class="block text-sm font-medium text-gray-700 mb-2">Possession Status</label>
                        <select name="possession_status" 
                                id="possession_status"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                            <option value="">-- Select Possession Status --</option>
                            <option value="Ready to Move" {{ old('possession_status') == 'Ready to Move' ? 'selected' : '' }}>Ready to Move</option>
                            <option value="Under Construction" {{ old('possession_status') == 'Under Construction' ? 'selected' : '' }}>Under Construction</option>
                        </select>
                    </div>
                </div>

                <!-- Assignment -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Assignment</h3>
                    
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assign To User (Optional)</label>
                        <select name="assigned_to" 
                                id="assigned_to"
                                   class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                            <option value="">-- Don't Assign Now --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role->name }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-2">You can assign this lead to a user now or assign later</p>
                    </div>
                </div>

                <!-- Requirements and Notes -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Additional Information</h3>
                    
                    <div class="mt-6">
                        <label for="requirements" class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                        <textarea name="requirements" 
                                  id="requirements"
                                  rows="3"
                                  placeholder="Additional requirements or preferences"
                                  class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">{{ old('requirements') }}</textarea>
                    </div>

                    <div class="mt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="3"
                                  placeholder="Any additional notes"
                                  class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('leads.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 font-medium">
                        Create Lead
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Project Modal -->
    @if(auth()->user()->canManageUsers())
    <div id="create_project_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Create New Project</h3>
                <button type="button" onclick="closeCreateProjectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="create_project_form" onsubmit="createProject(event)">
                @csrf
                <input type="hidden" name="is_active" value="1">
                <div class="mb-4">
                    <label for="project_name" class="block text-sm font-medium text-gray-700 mb-2">Project Name <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="project_name" 
                           name="name"
                           required
                           class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]">
                </div>
                <div class="mb-4">
                    <label for="project_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="project_description" 
                              name="description"
                              rows="2"
                              class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#205A44] focus:border-[#205A44]"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeCreateProjectModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script>
        // Show/hide custom source input (only when element exists – non-CRM)
        // Handle project selection (single click without Ctrl)
        function handleProjectSelection(event) {
            updateSelectedProjectsBox();
        }

        // Remove project from selection
        function removeProject(projectId) {
            const select = document.getElementById('preferred_projects');
            const option = select.querySelector(`option[value="${projectId}"]`);
            if (option) {
                option.selected = false;
                updateSelectedProjectsBox();
            }
        }

        // Update selected projects display box
        function updateSelectedProjectsBox() {
            const select = document.getElementById('preferred_projects');
            if (!select) return;
            const selectedOptions = Array.from(select.selectedOptions);
            const box = document.getElementById('selected_projects_list');
            const noProjectsText = document.getElementById('no_projects_text');
            
            box.innerHTML = '';
            
            if (selectedOptions.length === 0) {
                noProjectsText.style.display = 'block';
            } else {
                noProjectsText.style.display = 'none';
                selectedOptions.forEach(option => {
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-brand-secondary gap-2';
                    
                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = option.getAttribute('data-name');
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.onclick = () => removeProject(option.value);
                    removeBtn.className = 'ml-1 text-brand-secondary hover:text-brand-primary font-bold cursor-pointer';
                    removeBtn.innerHTML = '×';
                    removeBtn.style.fontSize = '20px';
                    removeBtn.style.lineHeight = '1';
                    removeBtn.style.padding = '0 2px';
                    
                    badge.appendChild(nameSpan);
                    badge.appendChild(removeBtn);
                    box.appendChild(badge);
                });
            }
        }

        // Initialize on page load (only when preferred_projects exists – non-CRM)
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('preferred_projects');
            if (!select) return;
            updateSelectedProjectsBox();
            
            // Make dropdown work with single click (without Ctrl)
            select.addEventListener('mousedown', function(e) {
                // Only handle if Ctrl/Cmd is not pressed
                if (!e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    const option = e.target;
                    if (option.tagName === 'OPTION') {
                        // Toggle selection
                        option.selected = !option.selected;
                        updateSelectedProjectsBox();
                    }
                }
            });
        });

        // Create Project Modal Functions
        function openCreateProjectModal() {
            document.getElementById('create_project_modal').classList.remove('hidden');
        }

        function closeCreateProjectModal() {
            document.getElementById('create_project_modal').classList.add('hidden');
            document.getElementById('create_project_form').reset();
        }

        // Create Project via AJAX
        function createProject(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            
            fetch('{{ route("projects.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                return response.json().then(data => {
                    if (response.ok) {
                        return data;
                    }
                    throw data;
                });
            })
            .then(data => {
                if (data.success) {
                    // Reload projects list from server
                    reloadProjectsList(data.project.id);
                    
                    // Close modal and reset form
                    closeCreateProjectModal();
                } else {
                    alert('Error creating project: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                let errorMessage = 'Error creating project. Please try again.';
                if (error.errors) {
                    const errors = Object.values(error.errors).flat();
                    errorMessage = errors.join('\n');
                } else if (error.message) {
                    errorMessage = error.message;
                }
                alert(errorMessage);
            });
        }

        // Reload projects list from server
        function reloadProjectsList(selectedProjectId = null) {
            const select = document.getElementById('preferred_projects');
            if (!select) return;
            fetch('{{ route("projects.list") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(projects => {
                const selectEl = document.getElementById('preferred_projects');
                if (!selectEl) return;
                const currentSelected = Array.from(selectEl.selectedOptions).map(opt => opt.value);
                
                // Clear existing options
                selectEl.innerHTML = '';
                
                // Add all projects
                projects.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.id;
                    option.setAttribute('data-name', project.name);
                    option.textContent = project.name;
                    
                    // Keep previously selected projects selected, or select the newly created one
                    if (selectedProjectId && project.id == selectedProjectId) {
                        option.selected = true;
                    } else if (currentSelected.includes(String(project.id))) {
                        option.selected = true;
                    }
                    
                    selectEl.appendChild(option);
                });
                
                // Update the display box
                updateSelectedProjectsBox();
            })
            .catch(error => {
                console.error('Error loading projects:', error);
            });
        }
    </script>
@endsection
