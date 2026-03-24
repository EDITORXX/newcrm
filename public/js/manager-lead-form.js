// Shared Manager Lead Form Functions — auto-generated


    function getAuthHeaders() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const apiToken = (typeof API_TOKEN !== 'undefined' && API_TOKEN)
            ? API_TOKEN : (localStorage.getItem('sales_manager_token') || '');
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (apiToken) headers['Authorization'] = 'Bearer ' + apiToken;
        return headers;
    }

    function renderManagerLeadForm(data) {
        const container = document.getElementById('managerLeadFormContainer');
        
        // Update modal title based on whether it's a prospect or direct lead
        const modalTitle = document.querySelector('#managerLeadRequirementFormModal .modal-header h3');
        if (modalTitle) {
            const hasProspect = data.has_prospect === true;
            modalTitle.textContent = hasProspect ? 'Prospect Verification' : 'Lead Detail Form';
        }
        
        const formValues = data.form_values || {};
        
        // Get existing values for pre-population
        const existingCategory = formValues.category || '';
        const existingPreferredLocation = formValues.preferred_location || '';
        const existingType = formValues.type || '';
        const existingPurpose = formValues.purpose || '';
        const existingPossession = formValues.possession || '';
        const existingBudget = formValues.budget || '';
        
        let formHTML = `
            <form id="managerLeadRequirementForm" novalidate onsubmit="submitManagerLeadRequirementForm(event); return false;">
                <input type="hidden" name="task_id" value="${currentTaskId}">
                
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Basic Information</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Name <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="manager_form_name"
                                   value="${data.lead_name || ''}"
                                   required
                                   placeholder="Enter lead name"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>
                        
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Mobile Number <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="tel" 
                                   name="phone" 
                                   id="manager_form_phone"
                                   value="${data.lead_phone || ''}"
                                   required
                                   placeholder="Enter phone number"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Lead Requirements</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Category Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Category <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="category" 
                                    id="manager_form_category" 
                                    required
                                    onchange="handleManagerCategoryChange(this.value)"
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Category --</option>
                                <option value="Residential" ${existingCategory === 'Residential' ? 'selected' : ''}>Residential</option>
                                <option value="Commercial" ${existingCategory === 'Commercial' ? 'selected' : ''}>Commercial</option>
                                <option value="Both" ${existingCategory === 'Both' ? 'selected' : ''}>Both</option>
                                <option value="N.A" ${existingCategory === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Preferred Location Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Preferred Location <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="preferred_location" 
                                    id="manager_form_preferred_location" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Preferred Location --</option>
                                <option value="Inside City" ${existingPreferredLocation === 'Inside City' ? 'selected' : ''}>Inside City</option>
                                <option value="Sitapur Road" ${existingPreferredLocation === 'Sitapur Road' ? 'selected' : ''}>Sitapur Road</option>
                                <option value="Hardoi Road" ${existingPreferredLocation === 'Hardoi Road' ? 'selected' : ''}>Hardoi Road</option>
                                <option value="Faizabad Road" ${existingPreferredLocation === 'Faizabad Road' ? 'selected' : ''}>Faizabad Road</option>
                                <option value="Sultanpur Road" ${existingPreferredLocation === 'Sultanpur Road' ? 'selected' : ''}>Sultanpur Road</option>
                                <option value="Shaheed Path" ${existingPreferredLocation === 'Shaheed Path' ? 'selected' : ''}>Shaheed Path</option>
                                <option value="Raebareily Road" ${existingPreferredLocation === 'Raebareily Road' ? 'selected' : ''}>Raebareily Road</option>
                                <option value="Kanpur Road" ${existingPreferredLocation === 'Kanpur Road' ? 'selected' : ''}>Kanpur Road</option>
                                <option value="Outer Ring Road" ${existingPreferredLocation === 'Outer Ring Road' ? 'selected' : ''}>Outer Ring Road</option>
                                <option value="Bijnor Road" ${existingPreferredLocation === 'Bijnor Road' ? 'selected' : ''}>Bijnor Road</option>
                                <option value="Deva Road" ${existingPreferredLocation === 'Deva Road' ? 'selected' : ''}>Deva Road</option>
                                <option value="Sushant Golf City" ${existingPreferredLocation === 'Sushant Golf City' ? 'selected' : ''}>Sushant Golf City</option>
                                <option value="Vrindavan Yojana" ${existingPreferredLocation === 'Vrindavan Yojana' ? 'selected' : ''}>Vrindavan Yojana</option>
                                <option value="N.A" ${existingPreferredLocation === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Type Field (dependent on Category) -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Type <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="type" 
                                    id="manager_form_type" 
                                    required
                                    ${!existingCategory ? 'disabled' : ''}
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; ${!existingCategory ? 'background-color: #f5f5f5;' : ''}">
                                <option value="">-- Select Type (select category first) --</option>
                            </select>
                        </div>
                        
                        <!-- Purpose Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Purpose <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="purpose" 
                                    id="manager_form_purpose" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Purpose --</option>
                                <option value="End Use" ${existingPurpose === 'End Use' ? 'selected' : ''}>End Use</option>
                                <option value="Short Term Investment" ${existingPurpose === 'Short Term Investment' ? 'selected' : ''}>Short Term Investment</option>
                                <option value="Long Term Investment" ${existingPurpose === 'Long Term Investment' ? 'selected' : ''}>Long Term Investment</option>
                                <option value="Rental Income" ${existingPurpose === 'Rental Income' ? 'selected' : ''}>Rental Income</option>
                                <option value="Investment + End Use" ${existingPurpose === 'Investment + End Use' ? 'selected' : ''}>Investment + End Use</option>
                                <option value="N.A" ${existingPurpose === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Possession Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Possession <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="possession" 
                                    id="manager_form_possession" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Possession --</option>
                                <option value="Under Construction" ${existingPossession === 'Under Construction' ? 'selected' : ''}>Under Construction</option>
                                <option value="Ready To Move" ${existingPossession === 'Ready To Move' ? 'selected' : ''}>Ready To Move</option>
                                <option value="Pre Launch" ${existingPossession === 'Pre Launch' ? 'selected' : ''}>Pre Launch</option>
                                <option value="Both" ${existingPossession === 'Both' ? 'selected' : ''}>Both</option>
                                <option value="N.A" ${existingPossession === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                        
                        <!-- Budget Field -->
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                Budget <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="budget" 
                                    id="manager_form_budget" 
                                    required
                                    style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Budget --</option>
                                <option value="Below 50 Lacs" ${existingBudget === 'Below 50 Lacs' ? 'selected' : ''}>Below 50 Lacs</option>
                                <option value="50-75 Lacs" ${existingBudget === '50-75 Lacs' ? 'selected' : ''}>50-75 Lacs</option>
                                <option value="75 Lacs-1 Cr" ${existingBudget === '75 Lacs-1 Cr' ? 'selected' : ''}>75 Lacs-1 Cr</option>
                                <option value="Above 1 Cr" ${existingBudget === 'Above 1 Cr' ? 'selected' : ''}>Above 1 Cr</option>
                                <option value="Above 2 Cr" ${existingBudget === 'Above 2 Cr' ? 'selected' : ''}>Above 2 Cr</option>
                                <option value="N.A" ${existingBudget === 'N.A' ? 'selected' : ''}>N.A</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Follow Up Required Section -->
                <div style="margin-bottom: 24px;">
                    <div style="padding: 16px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                        <div style="display: flex; align-items: center; margin-bottom: 12px;">
                            <input type="checkbox" 
                                   name="follow_up_required" 
                                   id="manager_form_follow_up_required"
                                   style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                            <label for="manager_form_follow_up_required" style="font-size: 14px; font-weight: 500; color: #333; cursor: pointer; margin: 0;">
                                <strong>Follow Up Required</strong>
                            </label>
                        </div>
                        <small style="display: block; color: #666; font-size: 12px; margin-left: 28px;">Check this if you need to schedule a follow-up call for this lead</small>
                        
                        <!-- Follow Up Date & Time Picker (shown conditionally when Follow Up Required is checked) -->
                        <div id="followUpDateContainer" style="display: none; margin-top: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Follow Up Date & Time</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="datetime-local" 
                                   name="follow_up_date" 
                                   id="manager_form_follow_up_date"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            <small style="display: block; margin-top: 4px; color: #666; font-size: 12px;">Select date and time for the follow-up call. A calling task will be created automatically.</small>
                            
                            <!-- Create Sales Executive Task Option (shown when Follow Up Required is checked) -->
                            <div id="createTelecallerTaskContainer" style="display: none; margin-top: 12px;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" 
                                           name="create_telecaller_task" 
                                           id="create_telecaller_task_checkbox"
                                           style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                                    <label for="create_telecaller_task_checkbox" style="font-size: 14px; font-weight: 500; color: #333; cursor: pointer; margin: 0;">
                                        Create calling task for Sales Executive also
                                    </label>
                                </div>
                                <small style="display: block; color: #666; font-size: 12px; margin-left: 28px; margin-top: 4px;">
                                    This will create a calling task for the original Sales Executive who provided this lead
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Verification Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Lead Status</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="lead_status" id="manager_form_lead_status" required style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Lead Status --</option>
                                <option value="hot">Hot</option>
                                <option value="warm">Warm</option>
                                <option value="cold">Cold</option>
                                <option value="junk">Junk</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Lead Quality</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <select name="lead_quality" id="manager_form_lead_quality" required style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">-- Select Lead Quality --</option>
                                <option value="1">1 - Bad</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5 - Best Lead</option>
                            </select>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                <strong>Interested Projects</strong> <span style="color: #d32f2f;">*</span>
                            </label>
                            <input type="text"
                                   id="manager_project_input"
                                   placeholder="Type project name and press Enter"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 12px;">
                            <div id="project-tags-container" class="project-tags-wrapper">
                                <div class="project-tags-grid" id="project-tags-grid">
                                    <!-- Project tags will be loaded dynamically -->
                                </div>
                            </div>
                            <input type="hidden" name="interested_projects" id="manager_form_interested_projects_hidden">
                        </div>
                    </div>
                    
                    <!-- Customer Profiling Section -->
                    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
                        <h3 style="font-size: 16px; font-weight: 600; color: #333; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e0e0e0;">Customer Profiling <span style="color: #666; font-weight: 400; font-size: 14px;">(Optional)</span></h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Customer Job
                                </label>
                                <input type="text" 
                                       name="customer_job" 
                                       id="manager_form_customer_job"
                                       value="${formValues.customer_job || ''}"
                                       placeholder="Enter customer job / occupation"
                                       style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Industry / Sector
                                </label>
                                <select name="industry_sector" 
                                        id="manager_form_industry_sector"
                                        style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                    <option value="">-- Select Industry / Sector --</option>
                                    <option value="IT" ${formValues.industry_sector === 'IT' ? 'selected' : ''}>IT</option>
                                    <option value="Education" ${formValues.industry_sector === 'Education' ? 'selected' : ''}>Education</option>
                                    <option value="Healthcare" ${formValues.industry_sector === 'Healthcare' ? 'selected' : ''}>Healthcare</option>
                                    <option value="Business" ${formValues.industry_sector === 'Business' ? 'selected' : ''}>Business</option>
                                    <option value="FMCG" ${formValues.industry_sector === 'FMCG' ? 'selected' : ''}>FMCG</option>
                                    <option value="Government" ${formValues.industry_sector === 'Government' ? 'selected' : ''}>Government</option>
                                    <option value="Other" ${formValues.industry_sector === 'Other' ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Buying Frequency
                                </label>
                                <select name="buying_frequency" 
                                        id="manager_form_buying_frequency"
                                        style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                    <option value="">-- Select Buying Frequency --</option>
                                    <option value="Regular" ${formValues.buying_frequency === 'Regular' ? 'selected' : ''}>Regular</option>
                                    <option value="Occasional" ${formValues.buying_frequency === 'Occasional' ? 'selected' : ''}>Occasional</option>
                                    <option value="First-time" ${formValues.buying_frequency === 'First-time' ? 'selected' : ''}>First-time</option>
                                </select>
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    Living City
                                </label>
                                <input type="text" 
                                       name="living_city" 
                                       id="manager_form_living_city"
                                       value="${formValues.living_city || ''}"
                                       placeholder="Enter living city"
                                       style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            </div>
                            
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                                    City Type
                                </label>
                                <select name="city_type" 
                                        id="manager_form_city_type"
                                        style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                    <option value="">-- Select City Type --</option>
                                    <option value="Metro" ${formValues.city_type === 'Metro' ? 'selected' : ''}>Metro</option>
                                    <option value="Tier 1" ${formValues.city_type === 'Tier 1' ? 'selected' : ''}>Tier 1</option>
                                    <option value="Tier 2" ${formValues.city_type === 'Tier 2' ? 'selected' : ''}>Tier 2</option>
                                    <option value="Tier 3" ${formValues.city_type === 'Tier 3' ? 'selected' : ''}>Tier 3</option>
                                    <option value="Local Resident" ${formValues.city_type === 'Local Resident' ? 'selected' : ''}>Local Resident</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 16px;">
                        <label style="display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 6px;">
                            <strong>Manager Remark</strong>
                        </label>
                        <textarea name="manager_remark" id="manager_form_manager_remark" rows="3" placeholder="Enter remarks or notes..." style="width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"></textarea>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 20px; border-top: 1px solid #e0e0e0; margin-top: 24px;">
                    <button type="button" 
                            onclick="cancelManagerLeadRequirementForm()" 
                            style="padding: 10px 20px; border: 1px solid #ddd; border-radius: 6px; background: white; color: #333; cursor: pointer; font-size: 14px; font-weight: 500;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="padding: 10px 20px; background: #205A44; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>Verify Prospect
                    </button>
                </div>
            </form>
        `;
        
        try {
            container.innerHTML = formHTML;
            
            // Load interested projects
            loadInterestedProjectsForManager();
            
            // Add Enter key handler for custom project input
            const projectInput = document.getElementById('manager_project_input');
            if (projectInput) {
                projectInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const value = this.value.trim();
                        if (value) {
                            addManagerProjectTag(value);
                            this.value = ''; // Clear input after adding
                        }
                    }
                });
            }
            
            // Initialize dependent fields (category -> type)
            const categorySelect = document.getElementById('manager_form_category');
            const typeSelect = document.getElementById('manager_form_type');
            
            if (categorySelect && typeSelect) {
                // Initialize Type field based on existing category
                if (categorySelect.value) {
                    updateManagerTypeOptions(categorySelect.value, typeSelect, existingType);
                }
            }
        } catch (error) {
            console.error('Error inserting form HTML:', error);
            container.innerHTML = `
                <div style="padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 8px; color: #c33;">
                    <h4 style="margin: 0 0 10px 0;">Error Loading Form</h4>
                    <p style="margin: 0;">${error.message}</p>
                </div>
            `;
        }
        
        // Initialize Follow Up Required checkbox handler
        const followUpRequiredCheckbox = document.getElementById('manager_form_follow_up_required');
        if (followUpRequiredCheckbox) {
            followUpRequiredCheckbox.addEventListener('change', function() {
                handleFollowUpRequiredChange(this.checked);
            });
            // Initialize on page load if checkbox is already checked
            if (followUpRequiredCheckbox.checked) {
                handleFollowUpRequiredChange(true);
            }
        }
    }

    function updateManagerTypeOptions(category, typeSelect, existingValue = null) {
        const typeOptions = {
            'Residential': ['Plots & Villas', 'Apartments', 'Studio', 'Farmhouse', 'N.A'],
            'Commercial': ['Retail Shops', 'Office Space', 'Studio', 'N.A'],
            'Both': ['Plots & Villas', 'Apartments', 'Retail Shops', 'Office Space', 'Studio', 'Farmhouse', 'Agricultural', 'Others', 'N.A'],
            'N.A': ['N.A']
        };
        
        const currentValue = existingValue || typeSelect.value;
        const options = typeOptions[category] || typeOptions['Both'];
        
        // Enable/disable Type field based on category selection
        if (category && category !== '') {
            typeSelect.disabled = false;
            typeSelect.style.backgroundColor = '';
        } else {
            typeSelect.disabled = true;
            typeSelect.style.backgroundColor = '#f5f5f5';
        }
        
        typeSelect.innerHTML = '<option value="">-- Select Type (select category first) --</option>';
        options.forEach(option => {
            const selected = option === currentValue ? 'selected' : '';
            typeSelect.innerHTML += `<option value="${option}" ${selected}>${option}</option>`;
        });
        
        // If current value is not in the new options, clear it
        if (currentValue && !options.includes(currentValue)) {
            typeSelect.value = '';
        }
    }

    async function loadInterestedProjectsForManager() {
        try {
            // Use full path for interested projects endpoint
            const response = await fetch('/api/interested-project-names', {
                headers: getAuthHeaders(),
            });
            const projectsResponse = await response.json();
            const projectTagsGrid = document.getElementById('project-tags-grid');
            
            if (projectsResponse && projectsResponse.success && projectsResponse.data && projectTagsGrid) {
                projectTagsGrid.innerHTML = '';
                projectsResponse.data.forEach(project => {
                    const tag = document.createElement('div');
                    tag.className = 'project-tag';
                    tag.dataset.projectId = project.id;
                    tag.innerHTML = `
                        <span class="project-tag-text">${escapeHtml(project.name)}</span>
                        <i class="fas fa-check project-tag-check"></i>
                    `;
                    tag.addEventListener('click', function() {
                        toggleProjectTag(this);
                    });
                    projectTagsGrid.appendChild(tag);
                });
            }
        } catch (error) {
            console.error('Error loading interested projects:', error);
        }
    }

    function handleFollowUpRequiredChange(isRequired) {
        const followUpContainer = document.getElementById('followUpDateContainer');
        const followUpDateInput = document.getElementById('manager_form_follow_up_date');
        const telecallerTaskContainer = document.getElementById('createTelecallerTaskContainer');
        
        if (isRequired) {
            // Show follow-up date picker
            if (followUpContainer) {
                followUpContainer.style.display = 'block';
                followUpContainer.style.visibility = 'visible';
            }
            if (followUpDateInput) {
                followUpDateInput.style.display = 'block';
                followUpDateInput.style.visibility = 'visible';
                followUpDateInput.removeAttribute('disabled');
                followUpDateInput.removeAttribute('readonly');
                // Don't set required attribute - we'll validate in JavaScript
                followUpDateInput.removeAttribute('required');
                followUpDateInput.required = false;
            }
            // Show telecaller task checkbox
            if (telecallerTaskContainer) {
                telecallerTaskContainer.style.display = 'block';
            }
        } else {
            // Hide follow-up date picker
            if (followUpContainer) {
                followUpContainer.style.display = 'none';
            }
            if (followUpDateInput) {
                // Always remove required attribute when hiding to prevent validation error
                followUpDateInput.removeAttribute('required');
                followUpDateInput.required = false;
                followUpDateInput.value = '';
            }
            // Hide telecaller task checkbox
            if (telecallerTaskContainer) {
                telecallerTaskContainer.style.display = 'none';
            }
            // Uncheck telecaller task checkbox when hiding
            const telecallerTaskCheckbox = document.getElementById('create_telecaller_task_checkbox');
            if (telecallerTaskCheckbox) {
                telecallerTaskCheckbox.checked = false;
            }
        }
    }

    function handleManagerFormFieldChange(fieldKey, value, dependentField = null) {
        if (fieldKey === 'category' && dependentField === 'type') {
            const typeSelect = document.getElementById('manager_form_type');
            if (typeSelect) {
                updateManagerTypeOptions(value, typeSelect);
            }
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function handleManagerCategoryChange(value) {
        const typeSelect = document.getElementById('manager_form_type');
        if (typeSelect) updateManagerTypeOptions(value, typeSelect);
    }

function addManagerProjectTag(projectName) {
        const projectTagsGrid = document.getElementById('project-tags-grid');
        if (!projectTagsGrid || !projectName || !projectName.trim()) {
            return;
        }
        
        const trimmedName = projectName.trim();
        
        // Check if tag already exists (case-insensitive)
        const existingTags = projectTagsGrid.querySelectorAll('.project-tag');
        for (let tag of existingTags) {
            const tagText = tag.querySelector('.project-tag-text')?.textContent?.trim();
            if (tagText && tagText.toLowerCase() === trimmedName.toLowerCase()) {
                // Tag already exists, just select it
                tag.classList.add('selected');
                updateSelectedProjects();
                return;
            }
        }
        
        // Create new tag element
        const tag = document.createElement('div');
        tag.className = 'project-tag selected'; // Auto-select custom projects
        tag.dataset.projectName = trimmedName; // Use projectName instead of projectId for custom projects
        tag.dataset.isCustom = 'true'; // Flag to identify custom projects
        tag.innerHTML = `
            <span class="project-tag-text">${escapeHtml(trimmedName)}</span>
            <i class="fas fa-check project-tag-check"></i>
        `;
        tag.addEventListener('click', function() {
            toggleProjectTag(this);
        });
        
        projectTagsGrid.appendChild(tag);
        updateSelectedProjects();
    }
function updateSelectedProjects() {
    const selectedTags = document.querySelectorAll('#project-tags-grid .project-tag.selected');
    const selectedProjects = Array.from(selectedTags).map(tag => {
        if (tag.dataset.isCustom === 'true' && tag.dataset.projectName) {
            return { name: tag.dataset.projectName, is_custom: true };
        } else if (tag.dataset.projectId) {
            return parseInt(tag.dataset.projectId);
        }
        return null;
    }).filter(p => p !== null);
    const hiddenInput = document.getElementById('manager_form_interested_projects_hidden');
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(selectedProjects);
    }
}

function removeManagerProjectTag(element) {
    const tag = element.closest('.project-tag');
    if (tag) {
        tag.remove();
        updateSelectedProjects();
    }
}

function updateManagerProjectsHidden() {
    updateSelectedProjects();
}
