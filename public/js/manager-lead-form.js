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

    function getDefaultOutputFormConfig() {
        return {
            follow_up: {
                required_label: 'Follow up required',
                date: {
                    label: 'Follow up date & time',
                    required: true,
                    help_text: 'Select a future date and time for the next follow-up.'
                },
                notes: {
                    label: 'Remark',
                    placeholder: 'Add follow-up note, context, or callback instruction...'
                }
            },
            meeting: {
                type: {
                    label: 'Meeting type',
                    required: true,
                    options: ['Initial Meeting', 'Follow-up Meeting', 'Negotiation Meeting', 'Closing Meeting']
                },
                date: { label: 'Scheduled date', required: true },
                time: { label: 'Scheduled time', required: true },
                mode: {
                    label: 'Meeting mode',
                    required: true,
                    options: ['Online', 'Offline']
                },
                link: {
                    label: 'Meeting link',
                    placeholder: 'https://meet.google.com/...'
                },
                location: {
                    label: 'Location',
                    placeholder: 'Office address, project site, etc.'
                },
                notes: {
                    label: 'Remark',
                    placeholder: 'Any notes about this meeting...'
                },
                reminder: { label: 'Remind me before meeting' }
            },
            visit: {
                date: { label: 'Visit date', required: true },
                time: { label: 'Visit time', required: true },
                type: {
                    label: 'Visit type',
                    options: ['Site visit', 'Office visit']
                },
                project: {
                    label: 'Project to visit',
                    placeholder: 'Enter project name'
                },
                location: {
                    label: 'Visit location',
                    placeholder: 'Project site address or landmark'
                },
                notes: {
                    label: 'Remark',
                    placeholder: 'Add visit note or instruction...'
                },
                reminder: { label: 'Remind me before visit' }
            }
        };
    }

    function getDefaultLeadRequirementsFormConfig() {
        return {
            name: { label: 'Customer name', placeholder: 'Enter lead name', required: true },
            phone: { label: 'Phone', placeholder: 'Enter phone number', required: true },
            category: { label: 'Category', required: true, options: ['Residential', 'Commercial', 'Both', 'N.A'] },
            preferred_location: {
                label: 'Location',
                required: true,
                options: ['Inside City', 'Sitapur Road', 'Hardoi Road', 'Faizabad Road', 'Sultanpur Road', 'Shaheed Path', 'Raebareily Road', 'Kanpur Road', 'Outer Ring Road', 'Bijnor Road', 'Deva Road', 'Sushant Golf City', 'Vrindavan Yojana', 'N.A']
            },
            budget: { label: 'Budget', required: true, options: ['Below 50 Lacs', '50-75 Lacs', '75 Lacs-1 Cr', 'Above 1 Cr', 'Above 2 Cr', 'N.A'] },
            type: { label: 'Type', placeholder: 'Select type', required: true },
            purpose: { label: 'Purpose', required: true, options: ['End Use', 'Short Term Investment', 'Long Term Investment', 'Rental Income', 'Investment + End Use', 'N.A'] },
            possession: { label: 'Possession', required: true, options: ['Under Construction', 'Ready To Move', 'Pre Launch', 'Both', 'N.A'] },
            lead_status: { label: 'Status', required: true, options: ['hot', 'warm', 'cold', 'junk'] },
            lead_quality: { label: 'Lead quality', required: true, options: ['1', '2', '3', '4', '5'] },
            interested_projects: { label: 'Interested projects', placeholder: 'Type a project and press Enter', required: true },
            customer_job: { label: 'Customer job', placeholder: 'Enter job / occupation' },
            industry_sector: { label: 'Industry / sector', options: ['IT', 'Education', 'Healthcare', 'Business', 'FMCG', 'Government', 'Other'] },
            buying_frequency: { label: 'Buying frequency', options: ['Regular', 'Occasional', 'First-time'] },
            living_city: { label: 'Living city', placeholder: 'Enter living city' },
            city_type: { label: 'City type', options: ['Metro', 'Tier 1', 'Tier 2', 'Tier 3', 'Local Resident'] },
            manager_remark: { label: 'Remark', placeholder: 'Enter remarks or notes...' },
            type_option_groups: TYPE_OPTIONS
        };
    }

    function getLeadRequirementsFormConfig() {
        const defaults = getDefaultLeadRequirementsFormConfig();
        const custom = window.leadDetailRequirementsFormConfig || window.managerLeadRequirementsFormConfig || {};

        return {
            name: Object.assign({}, defaults.name, custom.name || {}),
            phone: Object.assign({}, defaults.phone, custom.phone || {}),
            category: Object.assign({}, defaults.category, custom.category || {}),
            preferred_location: Object.assign({}, defaults.preferred_location, custom.preferred_location || {}),
            budget: Object.assign({}, defaults.budget, custom.budget || {}),
            type: Object.assign({}, defaults.type, custom.type || {}),
            purpose: Object.assign({}, defaults.purpose, custom.purpose || {}),
            possession: Object.assign({}, defaults.possession, custom.possession || {}),
            lead_status: Object.assign({}, defaults.lead_status, custom.lead_status || {}),
            lead_quality: Object.assign({}, defaults.lead_quality, custom.lead_quality || {}),
            interested_projects: Object.assign({}, defaults.interested_projects, custom.interested_projects || {}),
            customer_job: Object.assign({}, defaults.customer_job, custom.customer_job || {}),
            industry_sector: Object.assign({}, defaults.industry_sector, custom.industry_sector || {}),
            buying_frequency: Object.assign({}, defaults.buying_frequency, custom.buying_frequency || {}),
            living_city: Object.assign({}, defaults.living_city, custom.living_city || {}),
            city_type: Object.assign({}, defaults.city_type, custom.city_type || {}),
            manager_remark: Object.assign({}, defaults.manager_remark, custom.manager_remark || {}),
            type_option_groups: custom.type_option_groups || defaults.type_option_groups
        };
    }

    function getOutputFormConfig() {
        const defaults = getDefaultOutputFormConfig();
        const custom = window.leadDetailOutputFormConfig || window.managerLeadOutputFormConfig || {};

        return {
            follow_up: Object.assign({}, defaults.follow_up, custom.follow_up || {}, {
                date: Object.assign({}, defaults.follow_up.date, custom.follow_up?.date || {}),
                notes: Object.assign({}, defaults.follow_up.notes, custom.follow_up?.notes || {})
            }),
            meeting: Object.assign({}, defaults.meeting, custom.meeting || {}, {
                type: Object.assign({}, defaults.meeting.type, custom.meeting?.type || {}),
                date: Object.assign({}, defaults.meeting.date, custom.meeting?.date || {}),
                time: Object.assign({}, defaults.meeting.time, custom.meeting?.time || {}),
                mode: Object.assign({}, defaults.meeting.mode, custom.meeting?.mode || {}),
                link: Object.assign({}, defaults.meeting.link, custom.meeting?.link || {}),
                location: Object.assign({}, defaults.meeting.location, custom.meeting?.location || {}),
                notes: Object.assign({}, defaults.meeting.notes, custom.meeting?.notes || {}),
                reminder: Object.assign({}, defaults.meeting.reminder, custom.meeting?.reminder || {})
            }),
            visit: Object.assign({}, defaults.visit, custom.visit || {}, {
                date: Object.assign({}, defaults.visit.date, custom.visit?.date || {}),
                time: Object.assign({}, defaults.visit.time, custom.visit?.time || {}),
                type: Object.assign({}, defaults.visit.type, custom.visit?.type || {}),
                project: Object.assign({}, defaults.visit.project, custom.visit?.project || {}),
                location: Object.assign({}, defaults.visit.location, custom.visit?.location || {}),
                notes: Object.assign({}, defaults.visit.notes, custom.visit?.notes || {}),
                reminder: Object.assign({}, defaults.visit.reminder, custom.visit?.reminder || {})
            })
        };
    }

    function renderPlainOptions(options, placeholder, selectedValue) {
        const safeOptions = Array.isArray(options) ? options : [];
        const placeholderOption = placeholder
            ? `<option value="">${escapeHtml(placeholder)}</option>`
            : '';

        return placeholderOption + safeOptions.map(function (option) {
            const isSelected = String(selectedValue || '') === String(option);
            return `<option value="${escapeHtml(option)}" ${isSelected ? 'selected' : ''}>${escapeHtml(option)}</option>`;
        }).join('');
    }

    function renderMeetingModeOptions(options) {
        const safeOptions = Array.isArray(options) ? options : [];
        return safeOptions.map(function (option) {
            const normalized = String(option).toLowerCase() === 'offline' ? 'offline' : 'online';
            return `<option value="${normalized}">${escapeHtml(option)}</option>`;
        }).join('');
    }

    function renderVisitTypeOptions(options) {
        const safeOptions = Array.isArray(options) ? options : [];
        return safeOptions.map(function (option) {
            const normalized = String(option).toLowerCase().includes('office') ? 'office_visit' : 'site_visit';
            return `<option value="${normalized}">${escapeHtml(option)}</option>`;
        }).join('');
    }

    function renderLabelOptions(options, placeholder, formatter, selectedValue) {
        const safeOptions = Array.isArray(options) ? options : [];
        const placeholderOption = placeholder ? `<option value="">${escapeHtml(placeholder)}</option>` : '';

        return placeholderOption + safeOptions.map(function (option) {
            const value = typeof option === 'object' && option !== null ? option.value : option;
            const label = typeof formatter === 'function'
                ? formatter(value, option)
                : (typeof option === 'object' && option !== null ? (option.label || option.value) : option);
            const isSelected = String(selectedValue || '') === String(value);
            return `<option value="${escapeHtml(value)}" ${isSelected ? 'selected' : ''}>${escapeHtml(label)}</option>`;
        }).join('');
    }

    function updateManagerMeetingModeFields() {
        const meetingMode = document.getElementById('manager_form_meeting_mode')?.value || 'online';
        const linkWrap = document.getElementById('manager_form_meeting_link_wrap');
        const locationWrap = document.getElementById('manager_form_meeting_location_wrap');
        const meetingLink = document.getElementById('manager_form_meeting_link');
        const meetingLocation = document.getElementById('manager_form_meeting_location');

        if (meetingMode === 'online') {
            if (linkWrap) linkWrap.style.display = 'block';
            if (locationWrap) locationWrap.style.display = 'none';
            if (meetingLink) meetingLink.setAttribute('required', 'required');
            if (meetingLocation) meetingLocation.removeAttribute('required');
        } else {
            if (linkWrap) linkWrap.style.display = 'none';
            if (locationWrap) locationWrap.style.display = 'block';
            if (meetingLink) meetingLink.removeAttribute('required');
            if (meetingLocation) meetingLocation.setAttribute('required', 'required');
        }
    }

    function getModalContext() {
        if (document.getElementById('managerLeadRequirementFormModal')) {
            return 'task';
        }
        return 'lead';
    }

    function getTaskOutcomeContext() {
        return window.managerTaskOutcomeContext || null;
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
        const outcomeContext = getTaskOutcomeContext();
        const isInterestedOutcomeFlow = context === 'task' && outcomeContext && outcomeContext.outcome === 'interested';
        const selectedAction = getSelectedAction(formValues, context);
        const requirementsConfig = getLeadRequirementsFormConfig();
        const outputConfig = getOutputFormConfig();
        const modalTitle = getModalTitleElement();
        const showHeroSection = false;

        if (modalTitle) {
            modalTitle.textContent = 'Lead Form';
        }

        container.dataset.leadId = data.lead_id || '';
        container.dataset.prospectId = data.prospect_id || '';
        container.dataset.formContext = context;

        const deadDisabled = context !== 'task';
        const footerLabel = isInterestedOutcomeFlow
            ? 'Save & complete'
            : (context === 'task' ? 'Save & continue' : 'Save requirements');

        container.innerHTML = `
            <div class="manager-lead-shell">
                ${showHeroSection ? `
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
                ` : ''}

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
                                    <label for="manager_form_name">${escapeHtml(requirementsConfig.name.label)} ${requirementsConfig.name.required ? '<span class="req">*</span>' : ''}</label>
                                    <input class="manager-lead-input" type="text" name="name" id="manager_form_name" value="${escapeHtml(data.lead_name || '')}" ${requirementsConfig.name.required ? 'required' : ''} placeholder="${escapeHtml(requirementsConfig.name.placeholder || '')}">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_phone">${escapeHtml(requirementsConfig.phone.label)} ${requirementsConfig.phone.required ? '<span class="req">*</span>' : ''}</label>
                                    <input class="manager-lead-input" type="tel" name="phone" id="manager_form_phone" value="${escapeHtml(data.lead_phone || '')}" ${requirementsConfig.phone.required ? 'required' : ''} placeholder="${escapeHtml(requirementsConfig.phone.placeholder || '')}">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_category">${escapeHtml(requirementsConfig.category.label)} ${requirementsConfig.category.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="category" id="manager_form_category" ${requirementsConfig.category.required ? 'required' : ''} onchange="handleManagerCategoryChange(this.value)">
                                        ${renderPlainOptions(requirementsConfig.category.options, 'Select category', existingCategory)}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_preferred_location">${escapeHtml(requirementsConfig.preferred_location.label)} ${requirementsConfig.preferred_location.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="preferred_location" id="manager_form_preferred_location" ${requirementsConfig.preferred_location.required ? 'required' : ''}>
                                        ${renderPlainOptions(requirementsConfig.preferred_location.options, 'Select location', existingPreferredLocation)}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_budget">${escapeHtml(requirementsConfig.budget.label)} ${requirementsConfig.budget.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="budget" id="manager_form_budget" ${requirementsConfig.budget.required ? 'required' : ''}>
                                        ${renderPlainOptions(requirementsConfig.budget.options, 'Select budget', existingBudget)}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_type">${escapeHtml(requirementsConfig.type.label)} ${requirementsConfig.type.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="type" id="manager_form_type" ${requirementsConfig.type.required ? 'required' : ''} ${!existingCategory ? 'disabled' : ''}></select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_purpose">${escapeHtml(requirementsConfig.purpose.label)} ${requirementsConfig.purpose.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="purpose" id="manager_form_purpose" ${requirementsConfig.purpose.required ? 'required' : ''}>
                                        ${renderPlainOptions(requirementsConfig.purpose.options, 'Select purpose', existingPurpose)}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_possession">${escapeHtml(requirementsConfig.possession.label)} ${requirementsConfig.possession.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="possession" id="manager_form_possession" ${requirementsConfig.possession.required ? 'required' : ''}>
                                        ${renderPlainOptions(requirementsConfig.possession.options, 'Select possession', existingPossession)}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_lead_status">${escapeHtml(requirementsConfig.lead_status.label)} ${requirementsConfig.lead_status.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="lead_status" id="manager_form_lead_status" ${requirementsConfig.lead_status.required ? 'required' : ''}>
                                        ${renderLabelOptions(requirementsConfig.lead_status.options, 'Select status', function (value) {
                                            return String(value).charAt(0).toUpperCase() + String(value).slice(1);
                                        }, formValues.lead_status || '')}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_lead_quality">${escapeHtml(requirementsConfig.lead_quality.label)} ${requirementsConfig.lead_quality.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="lead_quality" id="manager_form_lead_quality" ${requirementsConfig.lead_quality.required ? 'required' : ''}>
                                        ${renderLabelOptions(requirementsConfig.lead_quality.options, 'Select lead quality', function (value) {
                                            if (String(value) === '1') return '1 - Bad';
                                            if (String(value) === '5') return '5 - Best Lead';
                                            return String(value);
                                        }, String(formValues.lead_quality || ''))}
                                    </select>
                                </div>
                                <div class="manager-lead-field manager-lead-field-full">
                                    <label for="manager_project_input">${escapeHtml(requirementsConfig.interested_projects.label)} ${requirementsConfig.interested_projects.required ? '<span class="req">*</span>' : ''}</label>
                                    <input class="manager-lead-input" type="text" id="manager_project_input" placeholder="${escapeHtml(requirementsConfig.interested_projects.placeholder || '')}">
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
                                    <label for="manager_form_customer_job">${escapeHtml(requirementsConfig.customer_job.label)} ${requirementsConfig.customer_job.required ? '<span class="req">*</span>' : ''}</label>
                                    <input class="manager-lead-input" type="text" name="customer_job" id="manager_form_customer_job" value="${escapeHtml(formValues.customer_job || '')}" ${requirementsConfig.customer_job.required ? 'required' : ''} placeholder="${escapeHtml(requirementsConfig.customer_job.placeholder || '')}">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_industry_sector">${escapeHtml(requirementsConfig.industry_sector.label)} ${requirementsConfig.industry_sector.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="industry_sector" id="manager_form_industry_sector" ${requirementsConfig.industry_sector.required ? 'required' : ''}>
                                        ${renderPlainOptions(requirementsConfig.industry_sector.options, 'Select industry / sector', formValues.industry_sector || '')}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_buying_frequency">${escapeHtml(requirementsConfig.buying_frequency.label)} ${requirementsConfig.buying_frequency.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="buying_frequency" id="manager_form_buying_frequency" ${requirementsConfig.buying_frequency.required ? 'required' : ''}>
                                        ${renderPlainOptions(requirementsConfig.buying_frequency.options, 'Select buying frequency', formValues.buying_frequency || '')}
                                    </select>
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_living_city">${escapeHtml(requirementsConfig.living_city.label)} ${requirementsConfig.living_city.required ? '<span class="req">*</span>' : ''}</label>
                                    <input class="manager-lead-input" type="text" name="living_city" id="manager_form_living_city" value="${escapeHtml(formValues.living_city || '')}" ${requirementsConfig.living_city.required ? 'required' : ''} placeholder="${escapeHtml(requirementsConfig.living_city.placeholder || '')}">
                                </div>
                                <div class="manager-lead-field">
                                    <label for="manager_form_city_type">${escapeHtml(requirementsConfig.city_type.label)} ${requirementsConfig.city_type.required ? '<span class="req">*</span>' : ''}</label>
                                    <select class="manager-lead-select" name="city_type" id="manager_form_city_type" ${requirementsConfig.city_type.required ? 'required' : ''}>
                                        ${renderPlainOptions(requirementsConfig.city_type.options, 'Select city type', formValues.city_type || '')}
                                    </select>
                                </div>
                                <div class="manager-lead-field manager-lead-field-full">
                                    <label for="manager_form_manager_remark">${escapeHtml(requirementsConfig.manager_remark.label)} ${requirementsConfig.manager_remark.required ? '<span class="req">*</span>' : ''}</label>
                                    <textarea class="manager-lead-textarea" name="manager_remark" id="manager_form_manager_remark" rows="3" ${requirementsConfig.manager_remark.required ? 'required' : ''} placeholder="${escapeHtml(requirementsConfig.manager_remark.placeholder || '')}">${escapeHtml(formValues.manager_remark || '')}</textarea>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="manager-lead-section">
                        <div class="manager-lead-section-head">
                            <div class="manager-lead-section-icon manager-lead-section-icon-green"><i class="fas fa-check"></i></div>
                            <div class="manager-lead-section-copy">
                                <h3>Output - Select next action</h3>
                                <span>Choose what happens next in the CRM journey</span>
                            </div>
                            <div class="manager-lead-section-pill">Section 3</div>
                        </div>
                        <div class="manager-lead-section-body">
                            <p class="manager-lead-output-desc">Choose what happens next with this lead. This action will determine the lead's journey in the CRM pipeline.</p>
                            <div class="manager-lead-output-grid">
                                ${renderActionCard('meeting', '<i class="fas fa-calendar-check"></i>', 'Meeting', 'Schedule a meeting', selectedAction === 'meeting', false)}
                                ${renderActionCard('visit', '<i class="fas fa-location-dot"></i>', 'Visit', 'Schedule site visit', selectedAction === 'visit', false)}
                                ${renderActionCard('follow_up', '<i class="fas fa-clock"></i>', 'Follow up', 'Set reminder to call', selectedAction === 'follow_up', false)}
                                ${renderActionCard('dead', '<i class="fas fa-circle-xmark"></i>', 'Dead', context === 'task' ? 'Mark as closed' : 'Unavailable on lead detail page', selectedAction === 'dead', deadDisabled)}
                            </div>

                            <div id="manager_follow_up_panel" class="manager-lead-followup-panel" style="display:none;">
                                <div class="manager-lead-followup-top">
                                    <div class="manager-lead-followup-title">Follow Up Required</div>
                                    <div class="manager-lead-followup-copy">Outcome section se hi next call schedule aur reminder controls manage karo.</div>
                                </div>
                                <label class="manager-lead-check manager-lead-check-primary">
                                    <input type="checkbox" name="follow_up_required" id="manager_form_follow_up_required">
                                    <span>${escapeHtml(outputConfig.follow_up.required_label)}</span>
                                </label>
                                <div class="manager-lead-followup-row">
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_follow_up_date">${escapeHtml(outputConfig.follow_up.date.label)} ${outputConfig.follow_up.date.required ? '<span class="req">*</span>' : ''}</label>
                                        <input class="manager-lead-input" type="datetime-local" name="follow_up_date" id="manager_form_follow_up_date" ${outputConfig.follow_up.date.required ? 'required' : ''}>
                                        <small>${escapeHtml(outputConfig.follow_up.date.help_text || '')}</small>
                                    </div>
                                </div>
                                <div class="manager-lead-followup-row">
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_follow_up_remark">${escapeHtml(outputConfig.follow_up.notes.label)}</label>
                                        <textarea class="manager-lead-textarea" id="manager_form_follow_up_remark" name="follow_up_remark" rows="3" placeholder="${escapeHtml(outputConfig.follow_up.notes.placeholder || '')}">${escapeHtml(formValues.follow_up_remark || formValues.manager_remark || '')}</textarea>
                                    </div>
                                </div>
                                <label class="manager-lead-check" id="createTelecallerTaskContainer" style="display:none;">
                                    <input type="checkbox" name="create_telecaller_task" id="create_telecaller_task_checkbox">
                                    <span>Create calling task for Sales Executive also</span>
                                </label>
                            </div>

                            <div id="manager_meeting_panel" class="manager-lead-followup-panel manager-lead-meeting-panel" style="display:none;">
                                <div class="manager-lead-followup-top">
                                    <div class="manager-lead-followup-title">Meeting Planning</div>
                                    <div class="manager-lead-followup-copy">Meeting select karte hi yahin se type, date, time aur mode set karo.</div>
                                </div>
                                <div class="manager-lead-grid">
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_meeting_type">${escapeHtml(outputConfig.meeting.type.label)} ${outputConfig.meeting.type.required ? '<span class="req">*</span>' : ''}</label>
                                        <select class="manager-lead-select" id="manager_form_meeting_type" name="meeting_type">
                                            ${renderPlainOptions(outputConfig.meeting.type.options, 'Select meeting type')}
                                        </select>
                                    </div>
                                    <div class="manager-lead-field">
                                        <label for="manager_form_meeting_date">${escapeHtml(outputConfig.meeting.date.label)} ${outputConfig.meeting.date.required ? '<span class="req">*</span>' : ''}</label>
                                        <input class="manager-lead-input" type="date" id="manager_form_meeting_date" name="meeting_date" ${outputConfig.meeting.date.required ? 'required' : ''}>
                                    </div>
                                    <div class="manager-lead-field">
                                        <label for="manager_form_meeting_time">${escapeHtml(outputConfig.meeting.time.label)} ${outputConfig.meeting.time.required ? '<span class="req">*</span>' : ''}</label>
                                        <input class="manager-lead-input" type="time" id="manager_form_meeting_time" name="meeting_time" ${outputConfig.meeting.time.required ? 'required' : ''}>
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_meeting_mode">${escapeHtml(outputConfig.meeting.mode.label)} ${outputConfig.meeting.mode.required ? '<span class="req">*</span>' : ''}</label>
                                        <select class="manager-lead-select" id="manager_form_meeting_mode" name="meeting_mode">
                                            ${renderMeetingModeOptions(outputConfig.meeting.mode.options)}
                                        </select>
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full" id="manager_form_meeting_link_wrap" style="display:none;">
                                        <label for="manager_form_meeting_link">${escapeHtml(outputConfig.meeting.link.label)}</label>
                                        <input class="manager-lead-input" type="url" id="manager_form_meeting_link" name="meeting_link" placeholder="${escapeHtml(outputConfig.meeting.link.placeholder || '')}">
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full" id="manager_form_meeting_location_wrap">
                                        <label for="manager_form_meeting_location">${escapeHtml(outputConfig.meeting.location.label)}</label>
                                        <input class="manager-lead-input" type="text" id="manager_form_meeting_location" name="meeting_location" placeholder="${escapeHtml(outputConfig.meeting.location.placeholder || '')}">
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_meeting_remark">${escapeHtml(outputConfig.meeting.notes.label)}</label>
                                        <textarea class="manager-lead-textarea" id="manager_form_meeting_remark" name="meeting_remark" rows="3" placeholder="${escapeHtml(outputConfig.meeting.notes.placeholder || '')}">${escapeHtml(formValues.manager_remark || '')}</textarea>
                                    </div>
                                </div>
                                <label class="manager-lead-check manager-lead-check-primary">
                                    <input type="checkbox" id="manager_form_meeting_reminder" name="meeting_reminder" checked>
                                    <span>${escapeHtml(outputConfig.meeting.reminder.label)}</span>
                                </label>
                            </div>

                            <div id="manager_visit_panel" class="manager-lead-followup-panel manager-lead-visit-panel" style="display:none;">
                                <div class="manager-lead-followup-top">
                                    <div class="manager-lead-followup-title">Visit Planning</div>
                                    <div class="manager-lead-followup-copy">Visit select karte hi yahin se date, time, project aur basic visit details set karo.</div>
                                </div>
                                <div class="manager-lead-grid">
                                    <div class="manager-lead-field">
                                        <label for="manager_form_visit_date">${escapeHtml(outputConfig.visit.date.label)} ${outputConfig.visit.date.required ? '<span class="req">*</span>' : ''}</label>
                                        <input class="manager-lead-input" type="date" id="manager_form_visit_date" name="visit_date" ${outputConfig.visit.date.required ? 'required' : ''}>
                                    </div>
                                    <div class="manager-lead-field">
                                        <label for="manager_form_visit_time">${escapeHtml(outputConfig.visit.time.label)} ${outputConfig.visit.time.required ? '<span class="req">*</span>' : ''}</label>
                                        <input class="manager-lead-input" type="time" id="manager_form_visit_time" name="visit_time" ${outputConfig.visit.time.required ? 'required' : ''}>
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_visit_type">${escapeHtml(outputConfig.visit.type.label)}</label>
                                        <select class="manager-lead-select" id="manager_form_visit_type" name="visit_type">
                                            ${renderVisitTypeOptions(outputConfig.visit.type.options)}
                                        </select>
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_visit_project">${escapeHtml(outputConfig.visit.project.label)}</label>
                                        <input class="manager-lead-input" type="text" id="manager_form_visit_project" name="visit_project" value="${escapeHtml(formValues.property_name || '')}" placeholder="${escapeHtml(outputConfig.visit.project.placeholder || '')}">
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_visit_location">${escapeHtml(outputConfig.visit.location.label)}</label>
                                        <input class="manager-lead-input" type="text" id="manager_form_visit_location" name="visit_location" placeholder="${escapeHtml(outputConfig.visit.location.placeholder || '')}">
                                    </div>
                                    <div class="manager-lead-field manager-lead-field-full">
                                        <label for="manager_form_visit_remark">${escapeHtml(outputConfig.visit.notes.label)}</label>
                                        <textarea class="manager-lead-textarea" id="manager_form_visit_remark" name="visit_remark" rows="3" placeholder="${escapeHtml(outputConfig.visit.notes.placeholder || '')}">${escapeHtml(formValues.manager_remark || '')}</textarea>
                                    </div>
                                </div>
                                <label class="manager-lead-check manager-lead-check-primary">
                                    <input type="checkbox" id="manager_form_visit_reminder" name="visit_reminder" checked>
                                    <span>${escapeHtml(outputConfig.visit.reminder.label)}</span>
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

        const meetingModeSelect = document.getElementById('manager_form_meeting_mode');
        if (meetingModeSelect) {
            meetingModeSelect.addEventListener('change', updateManagerMeetingModeFields);
        }

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
        const meetingPanel = document.getElementById('manager_meeting_panel');
        const visitPanel = document.getElementById('manager_visit_panel');
        const followCheckbox = document.getElementById('manager_form_follow_up_required');
        const followDate = document.getElementById('manager_form_follow_up_date');
        const telecallerContainer = document.getElementById('createTelecallerTaskContainer');
        const isFollowUp = action === 'follow_up';
        const isMeeting = action === 'meeting';
        const isVisit = action === 'visit';
        const meetingDate = document.getElementById('manager_form_meeting_date');
        const meetingTime = document.getElementById('manager_form_meeting_time');
        const visitDate = document.getElementById('manager_form_visit_date');
        const visitTime = document.getElementById('manager_form_visit_time');
        const meetingModeSelect = document.getElementById('manager_form_meeting_mode');

        if (followPanel) followPanel.style.display = isFollowUp ? 'block' : 'none';
        if (meetingPanel) meetingPanel.style.display = isMeeting ? 'block' : 'none';
        if (visitPanel) visitPanel.style.display = isVisit ? 'block' : 'none';
        if (followCheckbox) followCheckbox.checked = isFollowUp;
        if (followDate && !isFollowUp) followDate.value = '';
        if (meetingDate && isMeeting && !meetingDate.value) meetingDate.value = new Date().toISOString().split('T')[0];
        if (meetingTime && isMeeting && !meetingTime.value) meetingTime.value = '10:00';
        if (meetingModeSelect && isMeeting && !meetingModeSelect.value) meetingModeSelect.value = 'online';
        if (visitDate && isVisit && !visitDate.value) visitDate.value = new Date().toISOString().split('T')[0];
        if (visitTime && isVisit && !visitTime.value) visitTime.value = '11:00';
        if (telecallerContainer) telecallerContainer.style.display = isFollowUp && context === 'task' ? 'flex' : 'none';
        updateManagerMeetingModeFields();

        const submitButton = document.getElementById(context === 'task' ? 'managerLeadPrimarySubmitBtn' : 'leadReqSubmitBtn');
        if (submitButton) {
            if (action === 'meeting') submitButton.textContent = 'Save & schedule meeting';
            else if (action === 'visit') submitButton.textContent = 'Save & schedule visit';
            else if (action === 'dead') submitButton.textContent = 'Continue to reject';
            else submitButton.textContent = context === 'task' ? 'Save & continue' : 'Save requirements';
        }

        if (!silent) {
            if (isFollowUp && followDate) {
                setTimeout(function () {
                    followDate.focus();
                }, 50);
            } else if (isMeeting && meetingDate) {
                setTimeout(function () {
                    meetingDate.focus();
                }, 50);
            } else if (isVisit && visitDate) {
                setTimeout(function () {
                    visitDate.focus();
                }, 50);
            }
        }
    }

    function updateManagerTypeOptions(category, typeSelect, existingValue) {
        if (!typeSelect) return;
        const currentValue = existingValue || typeSelect.value || '';
        const typeOptionGroups = getLeadRequirementsFormConfig().type_option_groups || TYPE_OPTIONS;
        const options = typeOptionGroups[category] || typeOptionGroups.Both || TYPE_OPTIONS.Both;
        const typePlaceholder = getLeadRequirementsFormConfig().type.placeholder || 'Select type';

        typeSelect.innerHTML = `<option value="">${escapeHtml(typePlaceholder)}</option>`;
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
            const grid = document.getElementById('project-tags-grid');
            if (!grid) return;

            grid.innerHTML = '';
            const normalized = Array.isArray(selectedProjects) ? selectedProjects : [];

            normalized.forEach(function (item) {
                if (typeof item === 'object' && item && item.is_custom && item.name) {
                    addManagerProjectTag(item.name);
                } else if ((typeof item === 'string' || typeof item === 'number') && String(item).trim()) {
                    addManagerProjectTag(String(item));
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
        const baseManagerRemark = getValue('manager_form_manager_remark');
        const followUpRemark = getValue('manager_form_follow_up_remark');

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
            manager_remark: isFollowUp ? (followUpRemark || baseManagerRemark) : baseManagerRemark,
            follow_up_remark: followUpRemark,
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
            if (!payload.lead_id) return 'Lead ID not found for follow-up';
            if (!payload.follow_up_date) return 'Please select a Follow Up Date & Time';
            if (new Date(payload.follow_up_date) <= new Date()) return 'Follow Up Date & Time cannot be in the past';
        }

        if (payload.output_action === 'meeting') {
            const meetingType = document.getElementById('manager_form_meeting_type')?.value || '';
            const meetingDate = document.getElementById('manager_form_meeting_date')?.value || '';
            const meetingTime = document.getElementById('manager_form_meeting_time')?.value || '';
            const meetingMode = document.getElementById('manager_form_meeting_mode')?.value || '';
            const meetingLink = document.getElementById('manager_form_meeting_link')?.value.trim() || '';
            const meetingLocation = document.getElementById('manager_form_meeting_location')?.value.trim() || '';

            if (!payload.lead_id) return 'Lead ID not found for meeting';
            if (!meetingType) return 'Please select meeting type';
            if (!meetingDate) return 'Please select meeting date';
            if (!meetingTime) return 'Please select meeting time';
            if (!meetingMode) return 'Please select meeting mode';
            if (meetingMode === 'online' && !meetingLink) return 'Meeting link is required for online meetings';
            if (meetingMode === 'offline' && !meetingLocation) return 'Location is required for offline meetings';
            if (new Date(`${meetingDate}T${meetingTime}`) <= new Date()) return 'Meeting date & time cannot be in the past';
        }

        if (payload.output_action === 'visit') {
            const visitDate = document.getElementById('manager_form_visit_date')?.value || '';
            const visitTime = document.getElementById('manager_form_visit_time')?.value || '';
            const visitProject = document.getElementById('manager_form_visit_project')?.value.trim() || '';

            if (!payload.lead_id) return 'Lead ID not found for site visit';
            if (!visitDate) return 'Please select visit date';
            if (!visitTime) return 'Please select visit time';
            if (!visitProject) return 'Please enter project to visit';
            if (new Date(`${visitDate}T${visitTime}`) <= new Date()) return 'Visit date & time cannot be in the past';
        }

        return '';
    }

    function getSelectedProjectLabel(payload) {
        const firstProject = payload.interested_projects.find(function (item) {
            return typeof item === 'object' ? item.name : true;
        });
        return typeof firstProject === 'object'
            ? firstProject.name
            : document.querySelector('#project-tags-grid .project-tag.selected .project-tag-text')?.textContent || '';
    }

    function buildMeetingPayload(payload) {
        const meetingDate = document.getElementById('manager_form_meeting_date')?.value || '';
        const meetingTime = document.getElementById('manager_form_meeting_time')?.value || '';
        const meetingMode = document.getElementById('manager_form_meeting_mode')?.value || 'online';
        const meetingLink = document.getElementById('manager_form_meeting_link')?.value.trim() || '';
        const meetingLocation = document.getElementById('manager_form_meeting_location')?.value.trim() || '';
        const meetingRemark = document.getElementById('manager_form_meeting_remark')?.value.trim() || payload.manager_remark || '';

        return {
            lead_id: parseInt(payload.lead_id, 10),
            meeting_sequence: 1,
            scheduled_at: new Date(`${meetingDate}T${meetingTime}`).toISOString(),
            meeting_mode: meetingMode,
            meeting_link: meetingMode === 'online' ? (meetingLink || null) : null,
            location: meetingMode === 'offline' ? (meetingLocation || null) : null,
            reminder_enabled: document.getElementById('manager_form_meeting_reminder')?.checked === true,
            reminder_minutes: 5,
            meeting_notes: meetingRemark || null,
        };
    }

    function buildVisitPayload(payload) {
        const visitDate = document.getElementById('manager_form_visit_date')?.value || '';
        const visitTime = document.getElementById('manager_form_visit_time')?.value || '';
        const visitType = document.getElementById('manager_form_visit_type')?.value || 'site_visit';
        const visitProject = document.getElementById('manager_form_visit_project')?.value.trim() || getSelectedProjectLabel(payload);
        const visitLocation = document.getElementById('manager_form_visit_location')?.value.trim() || '';
        const visitRemark = document.getElementById('manager_form_visit_remark')?.value.trim() || payload.manager_remark || '';

        return {
            lead_id: parseInt(payload.lead_id, 10),
            prospect_id: payload.prospect_id ? parseInt(payload.prospect_id, 10) : null,
            assigned_to: null,
            property_name: visitProject || null,
            property_address: visitLocation || null,
            scheduled_at: new Date(`${visitDate}T${visitTime}`).toISOString(),
            visit_notes: visitRemark || null,
            customer_name: payload.name,
            phone: payload.phone,
            employee: '',
            occupation: payload.customer_job || null,
            date_of_visit: visitDate,
            project: visitProject || null,
            budget_range: BUDGET_RANGE_MAP[payload.budget] || payload.budget || null,
            team_leader: null,
            property_type: PROPERTY_TYPE_MAP[payload.type] || 'Just Exploring',
            payment_mode: 'Self Fund',
            tentative_period: 'Within 1 Month',
            lead_type: visitType === 'office_visit' ? 'Meeting' : 'New Visit',
        };
    }

    function buildFollowUpPayload(payload) {
        return {
            lead_id: parseInt(payload.lead_id, 10),
            type: 'call',
            notes: payload.manager_remark || 'Follow-up scheduled from task lead form',
            scheduled_at: new Date(payload.follow_up_date).toISOString()
        };
    }

    async function createDirectActionRecord(payload) {
        if (payload.output_action === 'meeting') {
            const response = await fetch('/api/sales-manager/meetings/quick-schedule-with-reminder', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(buildMeetingPayload(payload))
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result?.message || 'Failed to schedule meeting');
            return { message: 'Meeting scheduled successfully' };
        }

        if (payload.output_action === 'visit') {
            const response = await fetch('/api/sales-manager/site-visits', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(buildVisitPayload(payload))
            });
            const result = await response.json();
            if (!response.ok || result?.success === false) throw new Error(result?.message || 'Failed to schedule site visit');
            return { message: 'Site visit scheduled successfully' };
        }

        if (payload.output_action === 'follow_up') {
            const response = await fetch('/api/follow-ups', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify(buildFollowUpPayload(payload))
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result?.message || 'Failed to schedule follow-up');
            return { message: 'Follow-up scheduled successfully' };
        }

        return { message: 'Lead form submitted successfully' };
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
        const meetingType = document.getElementById('manager_form_meeting_type')?.value || '';
        const meetingDate = document.getElementById('manager_form_meeting_date')?.value || '';
        const meetingTime = document.getElementById('manager_form_meeting_time')?.value || '';
        const meetingMode = document.getElementById('manager_form_meeting_mode')?.value || 'online';
        const meetingLink = document.getElementById('manager_form_meeting_link')?.value?.trim() || '';
        const meetingLocation = document.getElementById('manager_form_meeting_location')?.value?.trim() || '';
        const meetingRemark = document.getElementById('manager_form_meeting_remark')?.value?.trim() || '';
        const visitDate = document.getElementById('manager_form_visit_date')?.value || '';
        const visitTime = document.getElementById('manager_form_visit_time')?.value || '';
        const visitType = document.getElementById('manager_form_visit_type')?.value || 'site_visit';
        const visitProject = document.getElementById('manager_form_visit_project')?.value?.trim() || '';
        const visitLocation = document.getElementById('manager_form_visit_location')?.value?.trim() || '';
        const visitRemark = document.getElementById('manager_form_visit_remark')?.value?.trim() || '';
        if (payload.lead_id) params.set('lead_id', payload.lead_id);
        if (payload.prospect_id) params.set('prospect_id', payload.prospect_id);
        params.set('prefill_name', payload.name);
        params.set('prefill_phone', payload.phone);
        if (visitProject || firstProjectLabel) params.set('prefill_project', visitProject || firstProjectLabel);
        if (payload.budget) params.set('prefill_budget', BUDGET_RANGE_MAP[payload.budget] || payload.budget);
        if (payload.type) params.set('prefill_property_type', PROPERTY_TYPE_MAP[payload.type] || 'Just Exploring');
        params.set('prefill_lead_type', action === 'meeting' ? 'Prospect' : 'Meeting');
        if (meetingType) params.set('prefill_meeting_type', meetingType);
        if (meetingMode) params.set('prefill_meeting_mode', meetingMode);
        if (meetingLink) params.set('prefill_meeting_link', meetingLink);
        if (meetingLocation) params.set('prefill_location', meetingLocation);
        if (visitLocation) params.set('prefill_location', visitLocation);
        if (visitType) params.set('prefill_visit_type', visitType);
        if (meetingRemark || visitRemark || payload.manager_remark) params.set('prefill_notes', meetingRemark || visitRemark || payload.manager_remark);
        params.set('prefill_date', meetingDate || visitDate || new Date().toISOString().split('T')[0]);
        if (meetingTime) params.set('prefill_time', meetingTime);
        if (visitTime) params.set('prefill_time', visitTime);
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
            const isInterestedOutcomeFlow = getTaskOutcomeContext() && getTaskOutcomeContext().outcome === 'interested';
            const endpoint = isInterestedOutcomeFlow
                ? `/tasks/${window.currentTaskId}/outcome`
                : `/tasks/${window.currentTaskId}/verify`;
            const body = isInterestedOutcomeFlow
                ? { outcome: 'interested', lead_form_payload: payload }
                : payload;

            const response = await apiCall(endpoint, {
                method: 'POST',
                body: JSON.stringify(body)
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

            const actionResult = payload.output_action && payload.output_action !== 'dead'
                ? await createDirectActionRecord(payload)
                : null;

            closeManagerLeadRequirementFormModal();
            const message = actionResult?.message
                || (isInterestedOutcomeFlow
                    ? 'Lead form submitted successfully'
                    : payload.output_action === 'follow_up'
                    ? 'Follow-up task created successfully'
                    : 'Lead requirements saved successfully');
            window.showAlert ? window.showAlert(message, 'success', 3000) : alert(message);

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

            if (payload.output_action === 'meeting' && typeof window.loadMeetings === 'function') {
                setTimeout(function () { window.loadMeetings(); }, 300);
            }

            if (payload.output_action === 'visit' && typeof window.loadVisits === 'function') {
                setTimeout(function () { window.loadVisits(); }, 300);
            }

            if (payload.output_action === 'follow_up' && typeof window.loadFollowUps === 'function') {
                setTimeout(function () { window.loadFollowUps(); }, 300);
            }

            if (payload.output_action === 'follow_up' && typeof window.loadProspects === 'function') {
                setTimeout(function () { window.loadProspects(); }, 350);
            }

            window.currentTaskId = null;
            window.managerTaskOutcomeContext = null;
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
                follow_up_remark: payload.follow_up_remark,
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
    window.validateManagerLeadPayload = validateManagerLeadPayload;
    window.buildLeadRequirementsPayload = buildLeadRequirementsPayload;
    window.createDirectActionRecord = createDirectActionRecord;
    window.submitManagerLeadRequirementForm = submitManagerLeadRequirementForm;
})();
