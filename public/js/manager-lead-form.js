(function () {
    const TYPE_OPTIONS = {
        Residential: ['Plots & Villas', 'Apartments', 'Studio', 'Farmhouse', 'N.A'],
        Commercial: ['Retail Shops', 'Office Space', 'Studio', 'N.A'],
        Both: ['Plots & Villas', 'Apartments', 'Retail Shops', 'Office Space', 'Studio', 'Farmhouse', 'Agricultural', 'Others', 'N.A'],
        'N.A': ['N.A']
    };

    const PROPERTY_TYPE_MAP = {
        'Plots & Villas': 'Plot/Villa',
        Apartments: 'Flat',
        'Retail Shops': 'Commercial',
        'Office Space': 'Commercial',
        Studio: 'Flat',
        Farmhouse: 'Plot/Villa',
        Agricultural: 'Plot/Villa',
        Others: 'Just Exploring',
        'N.A': 'Just Exploring'
    };

    const BUDGET_RANGE_MAP = {
        'Below 50 Lacs': 'Under 50 Lac',
        '50-75 Lacs': '50 Lac – 1 Cr',
        '75 Lacs-1 Cr': '50 Lac – 1 Cr',
        'Above 1 Cr': '1 Cr – 2 Cr',
        'Above 2 Cr': '2 Cr – 3 Cr',
        'N.A': 'Under 50 Lac'
    };

    function getAuthHeaders() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const apiToken = (typeof API_TOKEN !== 'undefined' && API_TOKEN)
            ? API_TOKEN
            : (localStorage.getItem('sales_manager_token') || '');
        const headers = {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (apiToken) headers.Authorization = 'Bearer ' + apiToken;
        return headers;
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function getModalContext() {
        if (document.getElementById('managerLeadRequirementFormModal')) {
            return 'task';
        }
        return 'lead';
    }

    function getModalTitleElement() {
        return document.getElementById('leadReqModalTitle')
            || document.querySelector('#managerLeadRequirementFormModal .modal-header h3');
    }

    function renderProgressStep(index, label, state) {
        const classNames = ['manager-lead-progress-step'];
        if (state.done) classNames.push('is-done');
        if (state.active) classNames.push('is-active');

        const inner = state.done
            ? '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3.5 8.5l3 3 6-7"/></svg>'
            : String(index);

        return `
            <div class="${classNames.join(' ')}">
                <div class="manager-lead-progress-dot">${inner}</div>
                <span class="manager-lead-progress-label">${label}</span>
            </div>
        `;
    }

    function getInfoChips(data) {
        const chips = [];
        chips.push({
            tone: 'teal',
            label: 'Lead detail form'
        });
        if (data.lead_id) chips.push({ tone: 'blue', label: `Lead #${data.lead_id}` });
        if (data.prospect_id) chips.push({ tone: 'purple', label: `Prospect #${data.prospect_id}` });
        if (data.prospect_status) chips.push({ tone: 'amber', label: String(data.prospect_status).replace(/_/g, ' ') });
        return chips;
    }

    function renderInfoChips(data) {
        return getInfoChips(data).map(function (chip) {
            return `<span class="manager-lead-chip manager-lead-chip-${chip.tone}">${escapeHtml(chip.label)}</span>`;
        }).join('');
    }

    function getSelectedAction(formValues, context) {
        const explicit = formValues.next_action || formValues.output_action || formValues.next_step_action;
        if (explicit) return explicit;
        if (context === 'task' && formValues.follow_up_required === '1') return 'follow_up';
        return '';
    }

    function parseInterestedProjects(formValues) {
        const raw = formValues.interested_projects;
        if (!raw) return [];
        if (Array.isArray(raw)) return raw;
        if (typeof raw === 'string') {
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        }
        return [];
    }

    function renderActionCard(action, icon, label, description, active, disabled) {
        const classes = ['manager-lead-output-btn', 'manager-lead-output-' + action];
        if (active) classes.push('is-active');
        if (disabled) classes.push('is-disabled');

        return `
            <button
                type="button"
                class="${classes.join(' ')}"
                data-output-action="${action}"
                ${disabled ? 'disabled' : ''}
            >
                <span class="manager-lead-output-icon">${icon}</span>
                <span class="manager-lead-output-copy">
                    <span class="manager-lead-output-label">${label}</span>
                    <span class="manager-lead-output-sub">${description}</span>
                </span>
            </button>
        `;
    }

    function renderManagerLeadForm(data) {
        const container = document.getElementById('managerLeadFormContainer');
        if (!container) return;

        const context = getModalContext();
        const formValues = data.form_values || {};
        const existingCategory = formValues.category || '';
        const existingPreferredLocation = formValues.preferred_location || '';
        const existingType = formValues.type || '';
        const existingPurpose = formValues.purpose || '';
        const existingPossession = formValues.possession || '';
        const existingBudget = formValues.budget || '';
        const selectedAction = getSelectedAction(formValues, context);
        const modalTitle = getModalTitleElement();

        if (modalTitle) {
            modalTitle.textContent = 'Lead Detail Form';
        }

        container.dataset.leadId = data.lead_id || '';
        container.dataset.prospectId = data.prospect_id || '';
        container.dataset.formContext = context;

        const deadDisabled = context !== 'task';
        const footerLabel = context === 'task' ? 'Save & continue' : 'Save requirements';

        container.innerHTML = `
            <div class="manager-lead-shell">
                <div class="manager-lead-header">
                    <div class="manager-lead-header-copy">
                        <div class="manager-lead-breadcrumb">CRM › Leads › <span>Lead detail form</span></div>
                        <h2>Lead detail form</h2>
                        <p>Fill in customer requirements, profiling, and choose the next CRM action.</p>
                    </div>
                    <div class="manager-lead-chip-row">${renderInfoChips(data)}</div>
                </div>

                <div class="manager-lead-progress">
                    ${renderProgressStep(1, 'Requirement', { done: true, active: false })}
                    ${renderProgressStep(2, 'Profiling', { done: false, active: true })}
                    ${renderProgressStep(3, 'Output', { done: false, active: !!selectedAction })}
                </div>

                <form id="managerLeadRequirementForm" class="manager-lead-form" novalidate onsubmit="submitManagerLeadRequirementForm(event); return false;">
                    <input type="hidden" name="task_id" value="${typeof window.currentTaskId !== 'undefined' ? (window.currentTaskId || '') : ''}">
                    <input type="hidden" name="lead_id" value="${data.lead_id || ''}">
                    <input type="hidden" name="prospect_id" value="${data.prospect_id || ''}">
                    <input type="hidden" name="output_action" id="manager_form_output_action" value="${selectedAction}">

                    <section class="manager-lead-section">
                        <div class="manager-lead-section-head">
                            <div class="manager-lead-section-icon manager-lead-section-icon-blue"><i class="fas fa-list-ul"></i></div>
                            <div class="manager-lead-section-copy">
                                <h3>Customer requirement</h3>
                                <span>Section 1</span>
                            </div>
                        </div>
                        <div class="manager-lead-section-body">
                            <div class="manager-lead-grid">
                                <div class="manager-lead-field">
                                    <label for="manager_form_name">Customer name <span class="req">*</span></label>
                                    <input class="manager-lead-input" type="text" name="name" id="manager_form_name" value="${escapeHtml(data.lead_name || '')}" required placeholder="Enter lead name">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_phone">Phone <span class="req">*</span></label>
                                    <input class="manager-lead-input" type="tel" name="phone" id="manager_form_phone" value="${escapeHtml(data.lead_phone || '')}" required placeholder="Enter phone number">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_category">Category <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="category" id="manager_form_category" required onchange="handleManagerCategoryChange(this.value)">
                                        <option value="">Select category</option>
                                        <option value="Residential" ${existingCategory === 'Residential' ? 'selected' : ''}>Residential</option>
                                        <option value="Commercial" ${existingCategory === 'Commercial' ? 'selected' : ''}>Commercial</option>
                                        <option value="Both" ${existingCategory === 'Both' ? 'selected' : ''}>Both</option>
                                        <option value="N.A" ${existingCategory === 'N.A' ? 'selected' : ''}>N.A</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_preferred_location">Location <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="preferred_location" id="manager_form_preferred_location" required>
                                        <option value="">Select location</option>
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
                                <div class="manager-lead-field">
                                    <label for="manager_form_budget">Budget <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="budget" id="manager_form_budget" required>
                                        <option value="">Select budget</option>
                                        <option value="Below 50 Lacs" ${existingBudget === 'Below 50 Lacs' ? 'selected' : ''}>Below 50 Lacs</option>
                                        <option value="50-75 Lacs" ${existingBudget === '50-75 Lacs' ? 'selected' : ''}>50-75 Lacs</option>
                                        <option value="75 Lacs-1 Cr" ${existingBudget === '75 Lacs-1 Cr' ? 'selected' : ''}>75 Lacs-1 Cr</option>
                                        <option value="Above 1 Cr" ${existingBudget === 'Above 1 Cr' ? 'selected' : ''}>Above 1 Cr</option>
                                        <option value="Above 2 Cr" ${existingBudget === 'Above 2 Cr' ? 'selected' : ''}>Above 2 Cr</option>
                                        <option value="N.A" ${existingBudget === 'N.A' ? 'selected' : ''}>N.A</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_type">Type <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="type" id="manager_form_type" required ${!existingCategory ? 'disabled' : ''}></select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_purpose">Purpose <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="purpose" id="manager_form_purpose" required>
                                        <option value="">Select purpose</option>
                                        <option value="End Use" ${existingPurpose === 'End Use' ? 'selected' : ''}>End Use</option>
                                        <option value="Short Term Investment" ${existingPurpose === 'Short Term Investment' ? 'selected' : ''}>Short Term Investment</option>
                                        <option value="Long Term Investment" ${existingPurpose === 'Long Term Investment' ? 'selected' : ''}>Long Term Investment</option>
                                        <option value="Rental Income" ${existingPurpose === 'Rental Income' ? 'selected' : ''}>Rental Income</option>
                                        <option value="Investment + End Use" ${existingPurpose === 'Investment + End Use' ? 'selected' : ''}>Investment + End Use</option>
                                        <option value="N.A" ${existingPurpose === 'N.A' ? 'selected' : ''}>N.A</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_possession">Possession <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="possession" id="manager_form_possession" required>
                                        <option value="">Select possession</option>
                                        <option value="Under Construction" ${existingPossession === 'Under Construction' ? 'selected' : ''}>Under Construction</option>
                                        <option value="Ready To Move" ${existingPossession === 'Ready To Move' ? 'selected' : ''}>Ready To Move</option>
                                        <option value="Pre Launch" ${existingPossession === 'Pre Launch' ? 'selected' : ''}>Pre Launch</option>
                                        <option value="Both" ${existingPossession === 'Both' ? 'selected' : ''}>Both</option>
                                        <option value="N.A" ${existingPossession === 'N.A' ? 'selected' : ''}>N.A</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_lead_status">Status <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="lead_status" id="manager_form_lead_status" required>
                                        <option value="">Select status</option>
                                        <option value="hot" ${formValues.lead_status === 'hot' ? 'selected' : ''}>Hot</option>
                                        <option value="warm" ${formValues.lead_status === 'warm' ? 'selected' : ''}>Warm</option>
                                        <option value="cold" ${formValues.lead_status === 'cold' ? 'selected' : ''}>Cold</option>
                                        <option value="junk" ${formValues.lead_status === 'junk' ? 'selected' : ''}>Junk</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_lead_quality">Lead quality <span class="req">*</span></label>
                                    <select class="manager-lead-select" name="lead_quality" id="manager_form_lead_quality" required>
                                        <option value="">Select lead quality</option>
                                        <option value="1" ${String(formValues.lead_quality || '') === '1' ? 'selected' : ''}>1 - Bad</option>
                                        <option value="2" ${String(formValues.lead_quality || '') === '2' ? 'selected' : ''}>2</option>
                                        <option value="3" ${String(formValues.lead_quality || '') === '3' ? 'selected' : ''}>3</option>
                                        <option value="4" ${String(formValues.lead_quality || '') === '4' ? 'selected' : ''}>4</option>
                                        <option value="5" ${String(formValues.lead_quality || '') === '5' ? 'selected' : ''}>5 - Best Lead</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field manager-lead-field-full">
                                    <label for="manager_project_input">Interested projects <span class="req">*</span></label>
                                    <input class="manager-lead-input" type="text" id="manager_project_input" placeholder="Type a project and press Enter">
                                    <div id="project-tags-container" class="manager-lead-project-wrap">
                                        <div class="project-tags-grid" id="project-tags-grid"></div>
                                    </div>
                                    <input type="hidden" name="interested_projects" id="manager_form_interested_projects_hidden">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="manager-lead-section">
                        <div class="manager-lead-section-head">
                            <div class="manager-lead-section-icon manager-lead-section-icon-purple"><i class="fas fa-user"></i></div>
                            <div class="manager-lead-section-copy">
                                <h3>Customer profiling</h3>
                                <span>Section 2</span>
                            </div>
                            <div class="manager-lead-section-pill">Optional</div>
                        </div>
                        <div class="manager-lead-section-body">
                            <div class="manager-lead-grid">
                                <div class="manager-lead-field">
                                    <label for="manager_form_customer_job">Customer job</label>
                                    <input class="manager-lead-input" type="text" name="customer_job" id="manager_form_customer_job" value="${escapeHtml(formValues.customer_job || '')}" placeholder="Enter job / occupation">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_industry_sector">Industry / sector</label>
                                    <select class="manager-lead-select" name="industry_sector" id="manager_form_industry_sector">
                                        <option value="">Select industry / sector</option>
                                        <option value="IT" ${formValues.industry_sector === 'IT' ? 'selected' : ''}>IT</option>
                                        <option value="Education" ${formValues.industry_sector === 'Education' ? 'selected' : ''}>Education</option>
                                        <option value="Healthcare" ${formValues.industry_sector === 'Healthcare' ? 'selected' : ''}>Healthcare</option>
                                        <option value="Business" ${formValues.industry_sector === 'Business' ? 'selected' : ''}>Business</option>
                                        <option value="FMCG" ${formValues.industry_sector === 'FMCG' ? 'selected' : ''}>FMCG</option>
                                        <option value="Government" ${formValues.industry_sector === 'Government' ? 'selected' : ''}>Government</option>
                                        <option value="Other" ${formValues.industry_sector === 'Other' ? 'selected' : ''}>Other</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_buying_frequency">Buying frequency</label>
                                    <select class="manager-lead-select" name="buying_frequency" id="manager_form_buying_frequency">
                                        <option value="">Select buying frequency</option>
                                        <option value="Regular" ${formValues.buying_frequency === 'Regular' ? 'selected' : ''}>Regular</option>
                                        <option value="Occasional" ${formValues.buying_frequency === 'Occasional' ? 'selected' : ''}>Occasional</option>
                                        <option value="First-time" ${formValues.buying_frequency === 'First-time' ? 'selected' : ''}>First-time</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_living_city">Living city</label>
                                    <input class="manager-lead-input" type="text" name="living_city" id="manager_form_living_city" value="${escapeHtml(formValues.living_city || '')}" placeholder="Enter living city">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_city_type">City type</label>
                                    <select class="manager-lead-select" name="city_type" id="manager_form_city_type">
                                        <option value="">Select city type</option>
                                        <option value="Metro" ${formValues.city_type === 'Metro' ? 'selected' : ''}>Metro</option>
                                        <option value="Tier 1" ${formValues.city_type === 'Tier 1' ? 'selected' : ''}>Tier 1</option>
                                        <option value="Tier 2" ${formValues.city_type === 'Tier 2' ? 'selected' : ''}>Tier 2</option>
                                        <option value="Tier 3" ${formValues.city_type === 'Tier 3' ? 'selected' : ''}>Tier 3</option>
                                        <option value="Local Resident" ${formValues.city_type === 'Local Resident' ? 'selected' : ''}>Local Resident</option>
                                    </select>
                                </div>
                                <div class="manager-lead-field manager-lead-field-full">
                                    <label for="manager_form_manager_remark">Manager remark</label>
                                    <textarea class="manager-lead-textarea" name="manager_remark" id="manager_form_manager_remark" rows="3" placeholder="Enter remarks or notes from manager...">${escapeHtml(formValues.manager_remark || '')}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="manager-lead-section">
                        <div class="manager-lead-section-head">
                            <div class="manager-lead-section-icon manager-lead-section-icon-green"><i class="fas fa-check"></i></div>
                            <div class="manager-lead-section-copy">
                                <h3>Output</h3>
                                <span>Section 3</span>
                            </div>
                        </div>
                        <div class="manager-lead-section-body">
                            <p class="manager-lead-output-desc">Choose what happens next with this lead. This decides the immediate CRM workflow.</p>
                            <div class="manager-lead-output-grid">
                                ${renderActionCard('meeting', '<i class="fas fa-calendar-alt"></i>', 'Meeting', 'Continue to schedule meeting', selectedAction === 'meeting', false)}
                                ${renderActionCard('visit', '<i class="fas fa-location-dot"></i>', 'Visit', 'Continue to schedule site visit', selectedAction === 'visit', false)}
                                ${renderActionCard('follow_up', '<i class="fas fa-clock"></i>', 'Follow up', 'Set reminder to call again', selectedAction === 'follow_up', false)}
                                ${renderActionCard('dead', '<i class="fas fa-ban"></i>', 'Dead', context === 'task' ? 'Move to reject reason flow' : 'Unavailable on lead detail page', selectedAction === 'dead', deadDisabled)}
                            </div>

                            <div id="manager_follow_up_panel" class="manager-lead-followup-panel" style="display:none;">
                                <div class="manager-lead-followup-row">
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_follow_up_date">Follow up date & time <span class="req">*</span></label>
                                        <input class="manager-lead-input" type="datetime-local" name="follow_up_date" id="manager_form_follow_up_date">
                                        <small>Select a future date and time for the next follow-up.</small>
                                    </div>
                                </div>
                                <label class="manager-lead-check">
                                    <input type="checkbox" name="follow_up_required" id="manager_form_follow_up_required">
                                    <span>Follow up required</span>
                                </label>
                                <label class="manager-lead-check" id="createTelecallerTaskContainer" style="display:none;">
                                    <input type="checkbox" name="create_telecaller_task" id="create_telecaller_task_checkbox">
                                    <span>Create calling task for Sales Executive also</span>
                                </label>
                            </div>
                        </div>
                    </section>
                </form>
            </div>

            <div class="manager-lead-footer">
                <button type="button" class="manager-lead-btn manager-lead-btn-secondary" onclick="${context === 'task' ? 'cancelManagerLeadRequirementForm()' : 'closeLeadRequirementsModal()'}">
                    Cancel
                </button>
                <button ${context === 'task' ? 'type="submit" form="managerLeadRequirementForm"' : 'type="button" onclick="submitLeadRequirementsFromShow()"'} class="manager-lead-btn manager-lead-btn-primary" id="${context === 'task' ? 'managerLeadPrimarySubmitBtn' : 'leadReqSubmitBtn'}">
                    ${footerLabel}
                </button>
            </div>
        `;

        const typeSelect = document.getElementById('manager_form_type');
        updateManagerTypeOptions(existingCategory, typeSelect, existingType);
        loadInterestedProjectsForManager(parseInterestedProjects(formValues));
        initializeManagerFormInteractions(context, selectedAction);
    }

    function initializeManagerFormInteractions(context, selectedAction) {
        const projectInput = document.getElementById('manager_project_input');
        if (projectInput) {
            projectInput.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                const value = this.value.trim();
                if (!value) return;
                addManagerProjectTag(value);
                this.value = '';
            });
        }

        document.querySelectorAll('[data-output-action]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (button.disabled) return;
                setManagerOutputAction(button.dataset.outputAction, context);
            });
        });

        setManagerOutputAction(selectedAction || '', context, true);
    }

    function setManagerOutputAction(action, context, silent) {
        const hidden = document.getElementById('manager_form_output_action');
        if (hidden) hidden.value = action;

        document.querySelectorAll('[data-output-action]').forEach(function (button) {
            button.classList.toggle('is-active', button.dataset.outputAction === action);
        });

        const progressStep = document.querySelectorAll('.manager-lead-progress-step')[2];
        if (progressStep) progressStep.classList.toggle('is-active', !!action);

        const followPanel = document.getElementById('manager_follow_up_panel');
        const followCheckbox = document.getElementById('manager_form_follow_up_required');
        const followDate = document.getElementById('manager_form_follow_up_date');
        const telecallerContainer = document.getElementById('createTelecallerTaskContainer');
        const isFollowUp = action === 'follow_up';

        if (followPanel) followPanel.style.display = isFollowUp ? 'block' : 'none';
        if (followCheckbox) followCheckbox.checked = isFollowUp;
        if (followDate && !isFollowUp) followDate.value = '';
        if (telecallerContainer) telecallerContainer.style.display = isFollowUp && context === 'task' ? 'flex' : 'none';

        const submitButton = document.getElementById(context === 'task' ? 'managerLeadPrimarySubmitBtn' : 'leadReqSubmitBtn');
        if (submitButton) {
            if (action === 'meeting') submitButton.textContent = 'Save & schedule meeting';
            else if (action === 'visit') submitButton.textContent = 'Save & schedule visit';
            else if (action === 'dead') submitButton.textContent = 'Continue to reject';
            else submitButton.textContent = context === 'task' ? 'Save & continue' : 'Save requirements';
        }

        if (!silent && isFollowUp && followDate) {
            setTimeout(function () {
                followDate.focus();
            }, 50);
        }
    }

    function updateManagerTypeOptions(category, typeSelect, existingValue) {
        if (!typeSelect) return;
        const currentValue = existingValue || typeSelect.value || '';
        const options = TYPE_OPTIONS[category] || TYPE_OPTIONS.Both;

        typeSelect.innerHTML = '<option value="">Select type</option>';
        options.forEach(function (option) {
            const optionEl = document.createElement('option');
            optionEl.value = option;
            optionEl.textContent = option;
            if (option === currentValue) optionEl.selected = true;
            typeSelect.appendChild(optionEl);
        });

        if (category) {
            typeSelect.disabled = false;
            typeSelect.classList.remove('is-disabled');
        } else {
            typeSelect.disabled = true;
            typeSelect.classList.add('is-disabled');
        }
    }

    async function loadInterestedProjectsForManager(selectedProjects) {
        try {
            const response = await fetch('/api/interested-project-names', {
                headers: getAuthHeaders()
            });
            const result = await response.json();
            const grid = document.getElementById('project-tags-grid');
            if (!grid) return;

            grid.innerHTML = '';
            const normalized = Array.isArray(selectedProjects) ? selectedProjects : [];

            if (result && result.success && Array.isArray(result.data)) {
                result.data.forEach(function (project) {
                    const tag = document.createElement('button');
                    tag.type = 'button';
                    tag.className = 'project-tag';
                    tag.dataset.projectId = String(project.id);
                    tag.innerHTML = `<span class="project-tag-text">${escapeHtml(project.name)}</span><i class="fas fa-check project-tag-check"></i>`;
                    if (normalized.some(function (item) { return String(item) === String(project.id); })) {
                        tag.classList.add('selected');
                    }
                    tag.addEventListener('click', function () {
                        toggleProjectTag(this);
                    });
                    grid.appendChild(tag);
                });
            }

            normalized.forEach(function (item) {
                if (typeof item === 'object' && item && item.is_custom && item.name) {
                    addManagerProjectTag(item.name);
                }
            });

            updateSelectedProjects();
        } catch (error) {
            console.error('Error loading interested projects:', error);
        }
    }

    function toggleProjectTag(tagElement) {
        tagElement.classList.toggle('selected');
        updateSelectedProjects();
    }

    function addManagerProjectTag(projectName) {
        const grid = document.getElementById('project-tags-grid');
        if (!grid || !projectName || !projectName.trim()) return;

        const trimmed = projectName.trim();
        const tags = Array.from(grid.querySelectorAll('.project-tag'));
        const existing = tags.find(function (tag) {
            return (tag.querySelector('.project-tag-text')?.textContent || '').trim().toLowerCase() === trimmed.toLowerCase();
        });

        if (existing) {
            existing.classList.add('selected');
            updateSelectedProjects();
            return;
        }

        const tag = document.createElement('button');
        tag.type = 'button';
        tag.className = 'project-tag selected';
        tag.dataset.projectName = trimmed;
        tag.dataset.isCustom = 'true';
        tag.innerHTML = `<span class="project-tag-text">${escapeHtml(trimmed)}</span><i class="fas fa-check project-tag-check"></i>`;
        tag.addEventListener('click', function () {
            toggleProjectTag(this);
        });
        grid.appendChild(tag);
        updateSelectedProjects();
    }

    function updateSelectedProjects() {
        const selectedTags = document.querySelectorAll('#project-tags-grid .project-tag.selected');
        const selectedProjects = Array.from(selectedTags).map(function (tag) {
            if (tag.dataset.isCustom === 'true' && tag.dataset.projectName) {
                return { name: tag.dataset.projectName, is_custom: true };
            }
            if (tag.dataset.projectId) {
                return parseInt(tag.dataset.projectId, 10);
            }
            return null;
        }).filter(Boolean);

        const hidden = document.getElementById('manager_form_interested_projects_hidden');
        if (hidden) hidden.value = JSON.stringify(selectedProjects);
        return selectedProjects;
    }

    function handleManagerCategoryChange(value) {
        updateManagerTypeOptions(value, document.getElementById('manager_form_type'));
    }

    function collectManagerLeadPayload() {
        const getValue = function (id) {
            const field = document.getElementById(id);
            return field ? field.value.trim() : '';
        };

        const outputAction = getValue('manager_form_output_action');
        const interestedProjects = updateSelectedProjects();
        const isFollowUp = outputAction === 'follow_up';

        return {
            name: getValue('manager_form_name'),
            phone: getValue('manager_form_phone'),
            category: getValue('manager_form_category'),
            preferred_location: getValue('manager_form_preferred_location'),
            type: getValue('manager_form_type'),
            purpose: getValue('manager_form_purpose'),
            possession: getValue('manager_form_possession'),
            budget: getValue('manager_form_budget'),
            lead_status: getValue('manager_form_lead_status'),
            lead_quality: getValue('manager_form_lead_quality'),
            interested_projects: interestedProjects,
            customer_job: getValue('manager_form_customer_job'),
            industry_sector: getValue('manager_form_industry_sector'),
            buying_frequency: getValue('manager_form_buying_frequency'),
            living_city: getValue('manager_form_living_city'),
            city_type: getValue('manager_form_city_type'),
            manager_remark: getValue('manager_form_manager_remark'),
            follow_up_required: isFollowUp ? '1' : '0',
            follow_up_date: isFollowUp ? getValue('manager_form_follow_up_date') : '',
            create_telecaller_task: document.getElementById('create_telecaller_task_checkbox')?.checked ? '1' : '0',
            output_action: outputAction,
            lead_id: document.querySelector('#managerLeadRequirementForm input[name="lead_id"]')?.value || '',
            prospect_id: document.querySelector('#managerLeadRequirementForm input[name="prospect_id"]')?.value || ''
        };
    }

    function validateManagerLeadPayload(payload, context) {
        const requiredFields = [
            ['name', 'Please enter customer name'],
            ['phone', 'Please enter phone number'],
            ['category', 'Please select category'],
            ['preferred_location', 'Please select location'],
            ['type', 'Please select type'],
            ['purpose', 'Please select purpose'],
            ['possession', 'Please select possession'],
            ['budget', 'Please select budget'],
            ['lead_status', 'Please select lead status'],
            ['lead_quality', 'Please select lead quality']
        ];

        for (let index = 0; index < requiredFields.length; index += 1) {
            const pair = requiredFields[index];
            if (!payload[pair[0]]) return pair[1];
        }

        if (!payload.interested_projects.length) return 'Please select at least one Interested Project';
        if (context === 'task' && !payload.output_action) return 'Please select the next action';

        if (payload.output_action === 'follow_up') {
            if (!payload.follow_up_date) return 'Please select a Follow Up Date & Time';
            if (new Date(payload.follow_up_date) <= new Date()) return 'Follow Up Date & Time cannot be in the past';
        }

        return '';
    }

    function buildScheduleRedirectUrl(action, payload) {
        const baseUrl = action === 'meeting' ? window.managerLeadMeetingCreateUrl : window.managerLeadSiteVisitCreateUrl;
        if (!baseUrl) return '';

        const firstProject = payload.interested_projects.find(function (item) {
            return typeof item === 'object' ? item.name : true;
        });
        const firstProjectLabel = typeof firstProject === 'object'
            ? firstProject.name
            : document.querySelector('#project-tags-grid .project-tag.selected .project-tag-text')?.textContent || '';

        const params = new URLSearchParams();
        if (payload.lead_id) params.set('lead_id', payload.lead_id);
        if (payload.prospect_id) params.set('prospect_id', payload.prospect_id);
        params.set('prefill_name', payload.name);
        params.set('prefill_phone', payload.phone);
        if (firstProjectLabel) params.set('prefill_project', firstProjectLabel);
        if (payload.budget) params.set('prefill_budget', BUDGET_RANGE_MAP[payload.budget] || payload.budget);
        if (payload.type) params.set('prefill_property_type', PROPERTY_TYPE_MAP[payload.type] || 'Just Exploring');
        params.set('prefill_lead_type', action === 'meeting' ? 'Prospect' : 'Meeting');
        if (payload.manager_remark) params.set('prefill_notes', payload.manager_remark);
        params.set('prefill_date', new Date().toISOString().split('T')[0]);
        return `${baseUrl}?${params.toString()}`;
    }

    async function submitManagerLeadRequirementForm(event) {
        event.preventDefault();

        const context = getModalContext();
        const payload = collectManagerLeadPayload();
        const validationError = validateManagerLeadPayload(payload, context);
        if (validationError) {
            window.showAlert ? window.showAlert(validationError, 'warning') : alert(validationError);
            return;
        }

        if (payload.output_action === 'dead') {
            closeManagerLeadRequirementFormModal();
            if (typeof window.proceedToReject === 'function') {
                window.proceedToReject();
            } else {
                const rejectModal = document.getElementById('rejectReasonModal');
                if (rejectModal) rejectModal.classList.add('active');
            }
            return;
        }

        if (typeof window.currentTaskId === 'undefined' || !window.currentTaskId) {
            window.showAlert ? window.showAlert('Task ID not found', 'error') : alert('Task ID not found');
            return;
        }

        try {
            const response = await apiCall(`/tasks/${window.currentTaskId}/verify`, {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (!response || !response.success) {
                window.showAlert ? window.showAlert(response?.message || response?.error || 'Failed to process request', 'error') : alert('Failed to process request');
                return;
            }

            const taskCard = document.getElementById(`task-card-${window.currentTaskId}`);
            if (taskCard) {
                taskCard.style.transition = 'opacity 0.3s, transform 0.3s';
                taskCard.style.opacity = '0';
                taskCard.style.transform = 'scale(0.96)';
                setTimeout(function () {
                    taskCard.remove();
                }, 250);
            }

            closeManagerLeadRequirementFormModal();
            const message = payload.output_action === 'follow_up'
                ? 'Follow-up task created successfully'
                : 'Lead requirements saved successfully';
            window.showAlert ? window.showAlert(message, 'success', 3000) : alert(message);

            const redirectUrl = ['meeting', 'visit'].includes(payload.output_action)
                ? buildScheduleRedirectUrl(payload.output_action, payload)
                : '';

            if (redirectUrl) {
                setTimeout(function () {
                    window.location.href = redirectUrl;
                }, 400);
                return;
            }

            if (typeof window.loadTasks === 'function') {
                setTimeout(function () {
                    const status = window.currentStatus || 'all';
                    const dateFilter = document.getElementById('dateFilterDropdown')?.value
                        || document.getElementById('dateFilterDropdownDesktop')?.value
                        || 'all';
                    const customDate = document.getElementById('customDatePicker')?.value || null;
                    window.loadTasks(status, dateFilter, customDate);
                }, 250);
            }

            window.currentTaskId = null;
        } catch (error) {
            console.error('Error submitting manager lead requirement form:', error);
            window.showAlert ? window.showAlert(error.message || 'Failed to process request', 'error') : alert('Failed to process request');
        }
    }

    function buildLeadRequirementsPayload() {
        const payload = collectManagerLeadPayload();
        return {
            customer_name: payload.name,
            phone: payload.phone,
            preferred_location: payload.preferred_location,
            budget: payload.budget,
            purpose: payload.purpose,
            possession: payload.possession,
            lead_status: payload.lead_status,
            form_fields: {
                category: payload.category,
                preferred_location: payload.preferred_location,
                type: payload.type,
                purpose: payload.purpose,
                possession: payload.possession,
                budget: payload.budget,
                lead_status: payload.lead_status,
                lead_quality: payload.lead_quality,
                interested_projects: payload.interested_projects,
                customer_job: payload.customer_job,
                industry_sector: payload.industry_sector,
                buying_frequency: payload.buying_frequency,
                living_city: payload.living_city,
                city_type: payload.city_type,
                manager_remark: payload.manager_remark,
                follow_up_required: payload.follow_up_required,
                follow_up_date: payload.follow_up_date,
                create_telecaller_task: payload.create_telecaller_task,
                output_action: payload.output_action
            },
            output_action: payload.output_action
        };
    }

    window.getAuthHeaders = getAuthHeaders;
    window.renderManagerLeadForm = renderManagerLeadForm;
    window.updateManagerTypeOptions = updateManagerTypeOptions;
    window.handleManagerCategoryChange = handleManagerCategoryChange;
    window.loadInterestedProjectsForManager = loadInterestedProjectsForManager;
    window.toggleProjectTag = toggleProjectTag;
    window.addManagerProjectTag = addManagerProjectTag;
    window.updateSelectedProjects = updateSelectedProjects;
    window.collectManagerLeadPayload = collectManagerLeadPayload;
    window.buildLeadRequirementsPayload = buildLeadRequirementsPayload;
    window.submitManagerLeadRequirementForm = submitManagerLeadRequirementForm;
})();
