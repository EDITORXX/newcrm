@php
    $user = auth()->user();
    $fieldConfig = function ($form, string $key, array $defaults = []) {
        $field = $form?->fields?->firstWhere('field_key', $key);

        return [
            'label' => $field?->label ?? ($defaults['label'] ?? ''),
            'placeholder' => $field?->placeholder ?? ($defaults['placeholder'] ?? ''),
            'help_text' => $field?->help_text ?? ($defaults['help_text'] ?? ''),
            'required' => $field ? (bool) $field->required : ($defaults['required'] ?? false),
            'options' => ($field && is_array($field->options) && count($field->options))
                ? $field->options
                : ($defaults['options'] ?? []),
            'default_value' => $field?->default_value ?? ($defaults['default_value'] ?? ''),
        ];
    };

    $leadNameField = $fieldConfig($leadDetailRequirementsForm ?? null, 'name', [
        'label' => 'Customer name',
        'placeholder' => 'Enter lead name',
        'required' => true,
    ]);
    $leadPhoneField = $fieldConfig($leadDetailRequirementsForm ?? null, 'phone', [
        'label' => 'Phone',
        'placeholder' => 'Enter phone number',
        'required' => true,
    ]);
    $leadCategoryField = $fieldConfig($leadDetailRequirementsForm ?? null, 'category', [
        'label' => 'Category',
        'required' => true,
        'options' => ['Residential', 'Commercial', 'Both', 'N.A'],
    ]);
    $leadLocationField = $fieldConfig($leadDetailRequirementsForm ?? null, 'preferred_location', [
        'label' => 'Location',
        'required' => true,
        'options' => ['Inside City', 'Sitapur Road', 'Hardoi Road', 'Faizabad Road', 'Sultanpur Road', 'Shaheed Path', 'Raebareily Road', 'Kanpur Road', 'Outer Ring Road', 'Bijnor Road', 'Deva Road', 'Sushant Golf City', 'Vrindavan Yojana', 'N.A'],
    ]);
    $leadBudgetField = $fieldConfig($leadDetailRequirementsForm ?? null, 'budget', [
        'label' => 'Budget',
        'required' => true,
        'options' => ['Below 50 Lacs', '50-75 Lacs', '75 Lacs-1 Cr', 'Above 1 Cr', 'Above 2 Cr', 'N.A'],
    ]);
    $leadTypeField = $fieldConfig($leadDetailRequirementsForm ?? null, 'type', [
        'label' => 'Type',
        'required' => true,
        'placeholder' => 'Select type',
    ]);
    $leadPurposeField = $fieldConfig($leadDetailRequirementsForm ?? null, 'purpose', [
        'label' => 'Purpose',
        'required' => true,
        'options' => ['End Use', 'Short Term Investment', 'Long Term Investment', 'Rental Income', 'Investment + End Use', 'N.A'],
    ]);
    $leadPossessionField = $fieldConfig($leadDetailRequirementsForm ?? null, 'possession', [
        'label' => 'Possession',
        'required' => true,
        'options' => ['Under Construction', 'Ready To Move', 'Pre Launch', 'Both', 'N.A'],
    ]);
    $leadStatusField = $fieldConfig($leadDetailRequirementsForm ?? null, 'lead_status', [
        'label' => 'Status',
        'required' => true,
        'options' => ['hot', 'warm', 'cold', 'junk'],
    ]);
    $leadQualityField = $fieldConfig($leadDetailRequirementsForm ?? null, 'lead_quality', [
        'label' => 'Lead quality',
        'required' => true,
        'options' => ['1', '2', '3', '4', '5'],
    ]);
    $leadProjectsField = $fieldConfig($leadDetailRequirementsForm ?? null, 'interested_projects', [
        'label' => 'Interested projects',
        'placeholder' => 'Type a project and press Enter',
        'required' => true,
    ]);
    $leadCustomerJobField = $fieldConfig($leadDetailRequirementsForm ?? null, 'customer_job', [
        'label' => 'Customer job',
        'placeholder' => 'Enter job / occupation',
    ]);
    $leadIndustryField = $fieldConfig($leadDetailRequirementsForm ?? null, 'industry_sector', [
        'label' => 'Industry / sector',
        'options' => ['IT', 'Education', 'Healthcare', 'Business', 'FMCG', 'Government', 'Other'],
    ]);
    $leadBuyingFrequencyField = $fieldConfig($leadDetailRequirementsForm ?? null, 'buying_frequency', [
        'label' => 'Buying frequency',
        'options' => ['Regular', 'Occasional', 'First-time'],
    ]);
    $leadLivingCityField = $fieldConfig($leadDetailRequirementsForm ?? null, 'living_city', [
        'label' => 'Living city',
        'placeholder' => 'Enter living city',
    ]);
    $leadCityTypeField = $fieldConfig($leadDetailRequirementsForm ?? null, 'city_type', [
        'label' => 'City type',
        'options' => ['Metro', 'Tier 1', 'Tier 2', 'Tier 3', 'Local Resident'],
    ]);
    $leadRemarkField = $fieldConfig($leadDetailRequirementsForm ?? null, 'manager_remark', [
        'label' => 'Remark',
        'placeholder' => 'Enter remarks or notes...',
    ]);

    $leadDetailRequirementsFormConfig = [
        'name' => [
            'label' => $leadNameField['label'],
            'placeholder' => $leadNameField['placeholder'],
            'required' => $leadNameField['required'],
        ],
        'phone' => [
            'label' => $leadPhoneField['label'],
            'placeholder' => $leadPhoneField['placeholder'],
            'required' => $leadPhoneField['required'],
        ],
        'category' => [
            'label' => $leadCategoryField['label'],
            'required' => $leadCategoryField['required'],
            'options' => $leadCategoryField['options'],
        ],
        'preferred_location' => [
            'label' => $leadLocationField['label'],
            'required' => $leadLocationField['required'],
            'options' => $leadLocationField['options'],
        ],
        'budget' => [
            'label' => $leadBudgetField['label'],
            'required' => $leadBudgetField['required'],
            'options' => $leadBudgetField['options'],
        ],
        'type' => [
            'label' => $leadTypeField['label'],
            'required' => $leadTypeField['required'],
            'placeholder' => $leadTypeField['placeholder'],
        ],
        'purpose' => [
            'label' => $leadPurposeField['label'],
            'required' => $leadPurposeField['required'],
            'options' => $leadPurposeField['options'],
        ],
        'possession' => [
            'label' => $leadPossessionField['label'],
            'required' => $leadPossessionField['required'],
            'options' => $leadPossessionField['options'],
        ],
        'lead_status' => [
            'label' => $leadStatusField['label'],
            'required' => $leadStatusField['required'],
            'options' => $leadStatusField['options'],
        ],
        'lead_quality' => [
            'label' => $leadQualityField['label'],
            'required' => $leadQualityField['required'],
            'options' => $leadQualityField['options'],
        ],
        'interested_projects' => [
            'label' => $leadProjectsField['label'],
            'placeholder' => $leadProjectsField['placeholder'],
            'required' => $leadProjectsField['required'],
        ],
        'customer_job' => [
            'label' => $leadCustomerJobField['label'],
            'placeholder' => $leadCustomerJobField['placeholder'],
        ],
        'industry_sector' => [
            'label' => $leadIndustryField['label'],
            'options' => $leadIndustryField['options'],
        ],
        'buying_frequency' => [
            'label' => $leadBuyingFrequencyField['label'],
            'options' => $leadBuyingFrequencyField['options'],
        ],
        'living_city' => [
            'label' => $leadLivingCityField['label'],
            'placeholder' => $leadLivingCityField['placeholder'],
        ],
        'city_type' => [
            'label' => $leadCityTypeField['label'],
            'options' => $leadCityTypeField['options'],
        ],
        'manager_remark' => [
            'label' => $leadRemarkField['label'],
            'placeholder' => $leadRemarkField['placeholder'],
        ],
        'type_option_groups' => [
            'Residential' => ['Plots & Villas', 'Apartments', 'Studio', 'Farmhouse', 'N.A'],
            'Commercial' => ['Retail Shops', 'Office Space', 'Studio', 'N.A'],
            'Both' => ['Plots & Villas', 'Apartments', 'Retail Shops', 'Office Space', 'Studio', 'Farmhouse', 'Agricultural', 'Others', 'N.A'],
            'N.A' => ['N.A'],
        ],
    ];

    $followUpRequiredField = $fieldConfig($leadDetailFollowUpForm ?? null, 'followup_required', ['label' => 'Follow up required']);
    $followUpDateField = $fieldConfig($leadDetailFollowUpForm ?? null, 'scheduled_at', [
        'label' => 'Follow up date & time',
        'required' => true,
        'help_text' => 'Select a future date and time for the next follow-up.',
    ]);
    $followUpNotesField = $fieldConfig($leadDetailFollowUpForm ?? null, 'notes', [
        'label' => 'Remark',
        'placeholder' => 'Add follow-up note, context, or callback instruction...',
    ]);

    $visitDateField = $fieldConfig($leadDetailSiteVisitForm ?? null, 'visit_date', ['label' => 'Visit date', 'required' => true]);
    $visitTimeField = $fieldConfig($leadDetailSiteVisitForm ?? null, 'visit_time', ['label' => 'Visit time', 'required' => true]);
    $visitTypeField = $fieldConfig($leadDetailSiteVisitForm ?? null, 'visit_type', [
        'label' => 'Visit type',
        'options' => ['Site visit', 'Office visit'],
    ]);
    $visitProjectField = $fieldConfig($leadDetailSiteVisitForm ?? null, 'project_name', [
        'label' => 'Project to visit',
        'placeholder' => 'Enter project name',
    ]);
    $visitLocationField = $fieldConfig($leadDetailSiteVisitForm ?? null, 'visit_location', [
        'label' => 'Visit location',
        'placeholder' => 'Project site address or landmark',
    ]);
    $visitNotesField = $fieldConfig($leadDetailSiteVisitForm ?? null, 'visit_notes', [
        'label' => 'Remark',
        'placeholder' => 'Add visit note or instruction...',
    ]);
    $visitReminderField = $fieldConfig($leadDetailSiteVisitForm ?? null, 'visit_reminder', ['label' => 'Remind me before visit']);

    $meetingTypeField = $fieldConfig($leadDetailMeetingForm ?? null, 'meeting_type', [
        'label' => 'Meeting type',
        'required' => true,
        'options' => ['Initial Meeting', 'Follow-up Meeting', 'Negotiation Meeting', 'Closing Meeting'],
    ]);
    $meetingDateField = $fieldConfig($leadDetailMeetingForm ?? null, 'meeting_date', ['label' => 'Scheduled date', 'required' => true]);
    $meetingTimeField = $fieldConfig($leadDetailMeetingForm ?? null, 'meeting_time', ['label' => 'Scheduled time', 'required' => true]);
    $meetingModeField = $fieldConfig($leadDetailMeetingForm ?? null, 'meeting_mode', [
        'label' => 'Meeting mode',
        'required' => true,
        'options' => ['Online', 'Offline'],
    ]);
    $meetingLinkField = $fieldConfig($leadDetailMeetingForm ?? null, 'meeting_link', [
        'label' => 'Meeting link',
        'placeholder' => 'https://meet.google.com/...',
    ]);
    $meetingLocationField = $fieldConfig($leadDetailMeetingForm ?? null, 'location', [
        'label' => 'Location',
        'placeholder' => 'Office address, project site, etc.',
    ]);
    $meetingNotesField = $fieldConfig($leadDetailMeetingForm ?? null, 'meeting_notes', [
        'label' => 'Remark',
        'placeholder' => 'Any notes about this meeting...',
    ]);
    $meetingReminderField = $fieldConfig($leadDetailMeetingForm ?? null, 'reminder_enabled', ['label' => 'Remind me before meeting']);

    $leadDetailOutputFormConfig = [
        'follow_up' => [
            'required_label' => $followUpRequiredField['label'],
            'date' => [
                'label' => $followUpDateField['label'],
                'required' => $followUpDateField['required'],
                'help_text' => $followUpDateField['help_text'],
            ],
            'notes' => [
                'label' => $followUpNotesField['label'],
                'placeholder' => $followUpNotesField['placeholder'],
            ],
        ],
        'meeting' => [
            'type' => [
                'label' => $meetingTypeField['label'],
                'required' => $meetingTypeField['required'],
                'options' => $meetingTypeField['options'],
            ],
            'date' => [
                'label' => $meetingDateField['label'],
                'required' => $meetingDateField['required'],
            ],
            'time' => [
                'label' => $meetingTimeField['label'],
                'required' => $meetingTimeField['required'],
            ],
            'mode' => [
                'label' => $meetingModeField['label'],
                'required' => $meetingModeField['required'],
                'options' => $meetingModeField['options'],
            ],
            'link' => [
                'label' => $meetingLinkField['label'],
                'placeholder' => $meetingLinkField['placeholder'],
            ],
            'location' => [
                'label' => $meetingLocationField['label'],
                'placeholder' => $meetingLocationField['placeholder'],
            ],
            'notes' => [
                'label' => $meetingNotesField['label'],
                'placeholder' => $meetingNotesField['placeholder'],
            ],
            'reminder' => [
                'label' => $meetingReminderField['label'],
            ],
        ],
        'visit' => [
            'date' => [
                'label' => $visitDateField['label'],
                'required' => $visitDateField['required'],
            ],
            'time' => [
                'label' => $visitTimeField['label'],
                'required' => $visitTimeField['required'],
            ],
            'type' => [
                'label' => $visitTypeField['label'],
                'options' => $visitTypeField['options'],
            ],
            'project' => [
                'label' => $visitProjectField['label'],
                'placeholder' => $visitProjectField['placeholder'],
            ],
            'location' => [
                'label' => $visitLocationField['label'],
                'placeholder' => $visitLocationField['placeholder'],
            ],
            'notes' => [
                'label' => $visitNotesField['label'],
                'placeholder' => $visitNotesField['placeholder'],
            ],
            'reminder' => [
                'label' => $visitReminderField['label'],
            ],
        ],
    ];
@endphp
@extends($layout ?? 'layouts.app')

@section('title', $lead->name . ' - Lead Details')
@section('page-title', 'Lead Details')

@section('content')
<div class="space-y-6 lead-detail-container" style="width: 100%; max-width: 100%; overflow-x: hidden; box-sizing: border-box;">
    <!-- Professional Header Section -->
    <div class="bg-gradient-to-r from-[#063A1C] via-[#205A44] to-[#063A1C] rounded-2xl shadow-xl border border-emerald-800/20 overflow-hidden mb-6">
        <div class="p-4 sm:p-6 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4 sm:mb-6">
                <div class="flex items-start gap-3 sm:gap-4 flex-1 min-w-0">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-xl sm:rounded-2xl bg-white/10 backdrop-blur-sm border-2 border-white/20 flex items-center justify-center text-white text-xl sm:text-2xl md:text-3xl font-bold shadow-lg flex-shrink-0">
                        {{ strtoupper(substr($lead->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-2 leading-tight break-words word-wrap">{{ $lead->name }}</h1>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-2 sm:mt-3">
                            <a href="tel:{{ $lead->phone }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-lg text-white text-xs sm:text-sm md:text-base font-medium transition-all duration-200 border border-white/20 shadow-sm whitespace-nowrap">
                                <i class="fas fa-phone text-xs sm:text-sm"></i>
                                <span class="truncate max-w-[120px] sm:max-w-none">{{ $lead->phone }}</span>
                            </a>
                            @if($lead->email)
                            <a href="mailto:{{ $lead->email }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-lg text-white text-xs sm:text-sm md:text-base font-medium transition-all duration-200 border border-white/20 shadow-sm whitespace-nowrap">
                                <i class="fas fa-envelope text-xs sm:text-sm"></i>
                                <span class="hidden sm:inline truncate max-w-xs">{{ $lead->email }}</span>
                                <span class="sm:hidden">Email</span>
                            </a>
                            @endif
                            <span class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-semibold whitespace-nowrap {{
                                $lead->status === 'dead' ? 'bg-red-500/20 text-red-100 border border-red-400/30' : 
                                ($lead->status === 'closed' ? 'bg-green-500/20 text-green-100 border border-green-400/30' : 
                                'bg-blue-500/20 text-blue-100 border border-blue-400/30') 
                            }}">
                                {{ ucfirst(str_replace('_', ' ', $lead->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('leads.index') }}" class="px-3 sm:px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-lg text-white text-xs sm:text-sm font-medium transition-all duration-200 border border-white/20 shadow-sm whitespace-nowrap">
                        <i class="fas fa-arrow-left mr-1.5 sm:mr-2"></i><span class="hidden sm:inline">Back</span><span class="sm:hidden">Back</span>
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions Section -->
            <div class="pt-4 sm:pt-6 border-t border-white/10">
                <h3 class="text-xs sm:text-sm font-semibold text-white/90 mb-3 sm:mb-4 uppercase tracking-wider">Quick Actions</h3>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <!-- Call Button -->
                    <button onclick="openCallModal()" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[80px] sm:min-w-0">
                        <i class="fas fa-phone text-xs sm:text-sm"></i>
                        <span>Call</span>
                    </button>
                    
                    <!-- WhatsApp Button -->
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}?text=Hello%20{{ urlencode($lead->name) }}" 
                       target="_blank"
                       class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[100px] sm:min-w-0">
                        <i class="fab fa-whatsapp text-xs sm:text-sm"></i>
                        <span>WhatsApp</span>
                    </a>
                    
                    <!-- Follow-up Button -->
                    <button onclick="openFollowupModal()" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[100px] sm:min-w-0">
                        <i class="fas fa-calendar-check text-xs sm:text-sm"></i>
                        <span>Follow-up</span>
                    </button>
                    
                    <!-- Site Visit Button -->
                    <button onclick="openSiteVisitModal()" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[110px] sm:min-w-0">
                        <i class="fas fa-map-marker-alt text-xs sm:text-sm"></i>
                        <span>Site Visit</span>
                    </button>
                    
                    <!-- Meeting Button -->
                    <button onclick="openMeetingModal()" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[100px] sm:min-w-0">
                        <i class="fas fa-handshake text-xs sm:text-sm"></i>
                        <span>Meeting</span>
                    </button>
                    
                    <!-- Schedule Call Task Button -->
                    <button onclick="openScheduleCallTaskModal()" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[120px] sm:min-w-0">
                        <i class="fas fa-calendar-alt text-xs sm:text-sm"></i>
                        <span>Schedule Task</span>
                    </button>

                    @if($user && ($user->isAdmin() || $user->isCrm()))
                    <button onclick="openOwnerTransferModal()" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[130px] sm:min-w-0">
                        <i class="fas fa-user-edit text-xs sm:text-sm"></i>
                        <span>Change Owner</span>
                    </button>
                    <form method="POST" action="{{ route('leads.destroy', $lead->id) }}" class="inline" onsubmit="return confirm('Remove this lead from the list? It will be moved to trash and can be recovered.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-red-600/90 hover:bg-red-700 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-red-500/50 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[120px] sm:min-w-0">
                            <i class="fas fa-trash-alt text-xs sm:text-sm"></i>
                            <span>Delete lead</span>
                        </button>
                    </form>
                    @endif
                    
                    <!-- Edit Requirements Button - Show for roles that can use centralized form -->
                    @if($user)
                        <button type="button" onclick="openLeadRequirementsModal({{ $lead->id }})" class="flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-lg transition-all duration-200 border border-white/20 shadow-sm font-medium text-xs sm:text-sm whitespace-nowrap min-w-[140px] sm:min-w-0">
                            <i class="fas fa-edit text-xs sm:text-sm"></i>
                            <span>Edit Requirements</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6" style="width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;">
        <!-- Lead Information Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-md border border-slate-200/80 p-4 sm:p-6 md:p-8 mb-4 sm:mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">Lead Information</p>
                        <h2 class="text-xl md:text-2xl font-bold text-slate-900">Contact Details</h2>
                    </div>
                    <div class="hidden md:flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-slate-600 to-slate-700 text-white shadow-sm">
                        <i class="fas fa-user-circle text-lg"></i>
                    </div>
                </div>
                
                <div class="space-y-3 sm:space-y-4 md:space-y-5">
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Name</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">{{ $lead->name }}</p>
                    </div>
                    
                    @if($lead->email)
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Email</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900 break-all">
                            <a href="mailto:{{ $lead->email }}" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors break-all">
                                {{ $lead->email }}
                            </a>
                        </p>
                    </div>
                    @endif
                    
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Phone</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">
                            <a href="tel:{{ $lead->phone }}" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors break-words">
                                {{ $lead->phone }}
                            </a>
                        </p>
                    </div>
                    
                    @if($lead->address)
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Address</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">{{ $lead->address }}</p>
                        @if($lead->city || $lead->state || $lead->pincode)
                            <p class="text-xs sm:text-sm text-slate-600 mt-1 break-words">
                                {{ trim(implode(', ', array_filter([$lead->city, $lead->state, $lead->pincode]))) }}
                            </p>
                        @endif
                    </div>
                    @endif
                    
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Source</p>
                        @if($user && ($user->isAdmin() || $user->isCrm()))
                            <form method="POST" action="{{ route('leads.update', $lead->id) }}" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="source_inline_update" value="1">
                                <select name="source" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-900 focus:border-[#205A44] focus:ring-2 focus:ring-[#205A44]">
                                    @foreach(\App\Models\Lead::sourceOptions() as $value => $label)
                                        <option value="{{ $value }}" {{ \App\Models\Lead::normalizeSource($lead->source) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="inline-flex items-center rounded-lg bg-gradient-to-r from-[#063A1C] to-[#205A44] px-3 py-2 text-xs font-semibold text-white">
                                    Save Source
                                </button>
                            </form>
                        @else
                            <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">{{ $lead->source_label }}</p>
                        @endif
                    </div>
                    
                    @if($lead->budget)
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Budget</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">{{ $lead->budget }}</p>
                    </div>
                    @endif
                    
                    @if($lead->property_type)
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Property Type</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">{{ ucfirst($lead->property_type) }}</p>
                    </div>
                    @endif
                    
                    @php
                        $allInterestedProjects = collect();
                        foreach ($lead->prospects as $prospect) {
                            if ($prospect->interestedProjects) {
                                $allInterestedProjects = $allInterestedProjects->merge($prospect->interestedProjects);
                            }
                        }
                        $uniqueProjects = $allInterestedProjects->unique('id');
                        $uniqueProjectNames = $uniqueProjects->pluck('name')->toArray();
                    @endphp
                    
                    @if($uniqueProjects->count() > 0)
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-2 sm:mb-3">Interested Projects</p>
                        <div class="flex flex-wrap gap-1.5 sm:gap-2">
                            @foreach($uniqueProjects as $project)
                                <span class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-semibold bg-gradient-to-r from-slate-600 to-slate-700 text-white shadow-sm break-words">
                                    {{ $project->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Created By</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">{{ $lead->creator->name ?? 'N/A' }}</p>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">{{ $lead->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    
                    @if($lead->activeAssignments->count() > 0)
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Assigned To</p>
                        <div class="space-y-1.5 sm:space-y-2">
                            @foreach($lead->activeAssignments as $assignment)
                                <p class="text-sm sm:text-base font-semibold text-slate-900 break-words">{{ $assignment->assignedTo->name ?? 'N/A' }}</p>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Response Time Section -->
                    @if(isset($responseTimeData) && $responseTimeData['assigned_at'])
                    <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/70 mt-4">
                        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-clock text-blue-600"></i>
                            Response Time
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1">Assigned At</p>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $responseTimeData['assigned_at']->format('M d, Y h:i A') }}
                                </p>
                            </div>
                            @if($responseTimeData['called_at'])
                            <div>
                                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1">Called At</p>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ $responseTimeData['called_at']->format('M d, Y h:i A') }}
                                </p>
                            </div>
                            @if($responseTimeData['response_time_minutes'] !== null)
                            <div>
                                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1">Response Time</p>
                                <p class="text-base font-bold text-blue-600">
                                    @if($responseTimeData['response_time_minutes'] < 60)
                                        {{ $responseTimeData['response_time_minutes'] }} minutes
                                    @else
                                        {{ floor($responseTimeData['response_time_minutes'] / 60) }}h {{ $responseTimeData['response_time_minutes'] % 60 }}m
                                    @endif
                                </p>
                            </div>
                            @endif
                            @else
                            <div>
                                <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1">Status</p>
                                <p class="text-sm font-semibold text-orange-600">Not Called Yet</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    @if($lead->notes)
                    <div class="p-3 sm:p-4 rounded-lg sm:rounded-xl bg-slate-50 border border-slate-200/70">
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-1.5 sm:mb-2">Notes</p>
                        <p class="text-xs sm:text-sm text-slate-900 whitespace-pre-wrap leading-relaxed break-words">{{ $lead->notes }}</p>
                    </div>
                    @endif
                    
                    <!-- Source Information -->
                    @php
                        $sourceInfo = [];
                        $sheetAssignment = $lead->activeAssignments->firstWhere('sheet_config_id', '!=', null);
                        if ($sheetAssignment && $sheetAssignment->sheetConfig) {
                            $sourceInfo['type'] = 'Google Sheets';
                            $sourceInfo['sheet_name'] = $sheetAssignment->sheetConfig->sheet_name;
                            $sourceInfo['sheet_id'] = $sheetAssignment->sheetConfig->sheet_id;
                            $sourceInfo['row_number'] = $sheetAssignment->sheet_row_number;
                        } elseif (in_array($lead->source, ['google_sheets', 'sheet'], true)) {
                            $sourceInfo['type'] = 'Sheet';
                        } elseif (in_array($lead->source, ['pabbly', 'facebook_lead_ads', 'social_media', 'meta'], true)) {
                            $sourceInfo['type'] = 'Meta';
                        } elseif (in_array($lead->source, ['csv'], true)) {
                            $sourceInfo['type'] = 'Sheet';
                        } elseif (in_array($lead->source, ['mcube', 'call', 'ivr'], true)) {
                            $sourceInfo['type'] = 'Ivr';
                        } else {
                            $sourceInfo['type'] = \App\Models\Lead::displaySourceLabel($lead->source);
                        }
                        
                        // Get form field values for source tracking (excluding sheet_name and sheet_id as per user request)
                        $sourceFields = $lead->formFieldValues()->whereIn('field_key', [
                            'source_row_number'
                        ])->get()->keyBy('field_key');

                        if ($sourceFields->has('source_row_number')) {
                            $sourceInfo['row_number'] = $sourceFields['source_row_number']->field_value;
                        }
                    @endphp
                    
                    @if(!empty($sourceInfo) && isset($sourceInfo['type']))
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/70 mt-4">
                        <p class="text-[12px] font-semibold uppercase tracking-[0.08em] text-slate-500 mb-3">Source Information</p>
                        <div class="space-y-2">
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Type</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $sourceInfo['type'] ?? 'N/A' }}</p>
                            </div>
                            @if(isset($sourceInfo['row_number']))
                            <div>
                                <p class="text-xs text-slate-500 mb-1">Row Number</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $sourceInfo['row_number'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Form Data / Custom Fields -->
            @php
                $formFieldValues = $lead->formFieldValues()
                    ->whereNotIn('field_key', ['source_sheet_name', 'source_sheet_id', 'source_row_number'])
                    ->orderBy('created_at')
                    ->get()
                    ->groupBy(function($fv) {
                        // Group by source: meta_* for Meta/Facebook, custom_* for custom, etc.
                        if (str_starts_with($fv->field_key, 'meta_')) {
                            return 'Meta/Facebook Form';
                        } elseif (str_starts_with($fv->field_key, 'custom_')) {
                            return 'Custom Fields';
                        } else {
                            return 'Other Fields';
                        }
                    });
            @endphp
            
            @if($formFieldValues->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-md border border-slate-200/80 p-6 md:p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Form Data</p>
                        <h2 class="text-xl md:text-2xl font-bold text-slate-900">Additional Information</h2>
                    </div>
                    <div class="hidden md:flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-slate-600 to-slate-700 text-white shadow-sm">
                        <i class="fas fa-list-alt"></i>
                    </div>
                </div>
                <div class="space-y-5">
                    @foreach($formFieldValues as $groupName => $fields)
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 md:p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-semibold uppercase">
                                {{ substr($groupName, 0, 1) }}
                            </div>
                            <h3 class="text-sm md:text-base font-semibold text-slate-800">{{ $groupName }}</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                            @foreach($fields as $fieldValue)
                            @php
                                $fieldConfig = $fieldValue->fieldConfig();
                                $displayLabel = $fieldConfig ? $fieldConfig->field_label : str_replace('_', ' ', ucfirst(str_replace(['meta_', 'custom_'], '', $fieldValue->field_key)));
                                $fieldValueText = $fieldValue->field_value ?? 'N/A';
                                
                                // Determine icon based on field key
                                $icon = 'fa-file-alt';
                                $fieldKeyLower = strtolower($fieldValue->field_key);
                                if (str_contains($fieldKeyLower, 'name') || str_contains($fieldKeyLower, 'ad')) {
                                    $icon = 'fa-building';
                                } elseif (str_contains($fieldKeyLower, 'budget') || str_contains($fieldKeyLower, 'price') || str_contains($fieldKeyLower, 'amount')) {
                                    $icon = 'fa-money-bill-wave';
                                } elseif (str_contains($fieldKeyLower, 'email')) {
                                    $icon = 'fa-envelope';
                                } elseif (str_contains($fieldKeyLower, 'phone') || str_contains($fieldKeyLower, 'mobile')) {
                                    $icon = 'fa-phone';
                                } elseif (str_contains($fieldKeyLower, 'location') || str_contains($fieldKeyLower, 'address') || str_contains($fieldKeyLower, 'city') || str_contains($fieldKeyLower, 'lucknow')) {
                                    $icon = 'fa-map-marker-alt';
                                } elseif (str_contains($fieldKeyLower, 'property') || str_contains($fieldKeyLower, 'plot') || str_contains($fieldKeyLower, 'villa')) {
                                    $icon = 'fa-home';
                                } elseif (str_contains($fieldKeyLower, 'purpose') || str_contains($fieldKeyLower, 'use')) {
                                    $icon = 'fa-key';
                                } elseif (str_contains($fieldKeyLower, 'job') || str_contains($fieldKeyLower, 'title') || str_contains($fieldKeyLower, 'occupation')) {
                                    $icon = 'fa-briefcase';
                                } elseif (str_contains($fieldKeyLower, 'buy') || str_contains($fieldKeyLower, 'when') || str_contains($fieldKeyLower, 'time')) {
                                    $icon = 'fa-calendar';
                                } elseif (str_contains($fieldKeyLower, 'row') || str_contains($fieldKeyLower, 'number')) {
                                    $icon = 'fa-hashtag';
                                }
                                
                                // Check if value should be displayed as a badge/pill
                                $isBadgeValue = in_array(strtolower($fieldValueText), ['yes', 'no', 'end use / reside', 'end_use_/_reside', 'plots_/_villas', 'plots & villas', 'within_month', 'within a month', 'immediate_use_(_1_yr)', 'immediate use (1 yr)']) || 
                                               str_contains(strtolower($fieldValueText), '₹') || 
                                               str_contains(strtolower($fieldValueText), 'lk') || 
                                               str_contains(strtolower($fieldValueText), 'cr');
                            @endphp
                            <div class="p-4 rounded-xl bg-white border border-slate-200/70 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas {{ $icon }} text-slate-600 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.1em] text-slate-500 mb-2">
                                            {{ $displayLabel }}
                                        </p>
                                        @if($isBadgeValue)
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $fieldValueText }}
                                        </span>
                                        @elseif(str_contains(strtolower($fieldValueText), '@') && str_contains(strtolower($fieldValueText), '.'))
                                        <a href="mailto:{{ $fieldValueText }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline break-all">
                                            {{ $fieldValueText }}
                                        </a>
                                        @else
                                        <p class="text-sm font-semibold text-slate-900 leading-snug break-words">
                                            {{ $fieldValueText }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Stats</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Calls</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->tasks()->where('task_type', 'calling')->count() + $lead->managerTasks()->where('type', 'phone_call')->count() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Site Visits</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->siteVisits->count() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Follow-ups</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->followUps->count() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Meetings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $lead->meetings->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Activity Timeline</h2>
                
                @php
                    $timelineItems = collect($timeline);
                @endphp

                @if($timelineItems->count() > 0)
                    <div class="relative">
                        <!-- Timeline Line -->
                        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        
                        <div class="space-y-6">
                            @php
                                $currentDate = null;
                            @endphp
                            
                            @foreach($timelineItems as $activity)
                                @php
                                    $activityDate = $activity['timestamp']->format('Y-m-d');
                                    $showDateHeader = $currentDate !== $activityDate;
                                    if ($showDateHeader) {
                                        $currentDate = $activityDate;
                                    }
                                @endphp
                                
                                @if($showDateHeader)
                                    <div class="relative">
                                        <div class="flex items-center mb-4">
                                            <div class="flex-1 border-t border-gray-200"></div>
                                            <span class="px-4 text-sm font-semibold text-gray-500">
                                                @if($activity['timestamp']->isToday())
                                                    Today
                                                @elseif($activity['timestamp']->isYesterday())
                                                    Yesterday
                                                @elseif($activity['timestamp']->isCurrentWeek())
                                                    {{ $activity['timestamp']->format('l, M d') }}
                                                @else
                                                    {{ $activity['timestamp']->format('M d, Y') }}
                                                @endif
                                            </span>
                                            <div class="flex-1 border-t border-gray-200"></div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="relative pl-16 pb-6">
                                    <!-- Timeline Dot -->
                                    <div class="absolute left-0 top-1">
                                        <div class="w-12 h-12 rounded-full border-4 border-white shadow-md flex items-center justify-center" style="background-color: {{ $activity['color'] }}20;">
                                            <i class="fas {{ $activity['icon'] }} text-sm" style="color: {{ $activity['color'] }};"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Activity Card -->
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1">{{ $activity['title'] }}</h3>
                                                <p class="text-sm text-gray-600 mb-2">{{ $activity['description'] }}</p>
                                                
                                                @if(isset($activity['metadata']) && !empty($activity['metadata']))
                                                    <div class="mt-2 space-y-1">
                                                        @if(isset($activity['metadata']['status']))
                                                            <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                                                Status: {{ ucfirst($activity['metadata']['status']) }}
                                                            </span>
                                                        @endif
                                                        @if(isset($activity['metadata']['duration']))
                                                            <span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">
                                                                Duration: {{ $activity['metadata']['duration'] }}
                                                            </span>
                                                        @endif
                                                        @if(isset($activity['metadata']['lead_score']))
                                                            <span class="inline-block px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">
                                                                Lead Score: {{ $activity['metadata']['lead_score'] }}/5
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="text-right ml-4">
                                                <p class="text-xs text-gray-500">
                                                    {{ $activity['timestamp']->format('h:i A') }}
                                                </p>
                                                @if($activity['user'])
                                                    <p class="text-xs text-gray-400 mt-1">
                                                        by {{ $activity['user']->name }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-history text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">No activities found for this lead.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modals for Quick Actions -->
<!-- Call Modal -->
<div id="callModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Log Call</h3>
                <button onclick="closeCallModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="callForm" onsubmit="submitCall(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Call Type *</label>
                        <select name="call_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="outgoing">Outgoing</option>
                            <option value="incoming">Incoming</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input type="text" name="phone_number" value="{{ $lead->phone }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time *</label>
                            <input type="datetime-local" name="start_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (seconds) *</label>
                            <input type="number" name="duration" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Call Outcome</label>
                        <select name="call_outcome" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select outcome</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="callback">Callback</option>
                            <option value="no_answer">No Answer</option>
                            <option value="busy">Busy</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeCallModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Log Call</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Follow-up Modal -->
<div id="followupModal" class="fixed inset-0 bg-black/40 hidden overflow-y-auto h-full w-full z-50">
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-5xl rounded-[28px] bg-white shadow-2xl overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900">Follow Up</h3>
                <button onclick="closeFollowupModal()" class="text-slate-400 hover:text-slate-600 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="followupForm" onsubmit="submitFollowup(event)" class="p-6 md:p-8">
                <div class="rounded-[24px] border border-emerald-100 bg-gradient-to-br from-white to-slate-50 p-5 md:p-7">
                    <div class="mb-5">
                        <h4 class="text-xl font-bold text-slate-900">Follow Up Required</h4>
                        <p class="mt-1 text-sm text-slate-600">Outcome section se hi next call schedule aur reminder controls manage karo.</p>
                    </div>
                    <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-900 mb-5">
                        <input type="checkbox" id="followup_required" checked class="h-4 w-4 rounded border-slate-300 text-red-500 focus:ring-red-400">
                        <span>{{ $followUpRequiredField['label'] }}</span>
                    </label>
                    <input type="hidden" name="type" value="call">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $followUpDateField['label'] }} @if($followUpDateField['required'])<span class="text-red-500">*</span>@endif</label>
                        <input type="datetime-local" name="scheduled_at" @if($followUpDateField['required']) required @endif class="w-full rounded-2xl border border-emerald-400 px-4 py-3 text-base text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        @if($followUpDateField['help_text'])
                            <small class="mt-2 block text-sm text-slate-500">{{ $followUpDateField['help_text'] }}</small>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $followUpNotesField['label'] }}</label>
                        <textarea name="notes" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="{{ $followUpNotesField['placeholder'] }}"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeFollowupModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white">Save Follow Up</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Site Visit Modal -->
<div id="siteVisitModal" class="fixed inset-0 bg-black/40 hidden overflow-y-auto h-full w-full z-50">
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-6xl rounded-[28px] bg-white shadow-2xl overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900">Site Visit</h3>
                <button onclick="closeSiteVisitModal()" class="text-slate-400 hover:text-slate-600 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="siteVisitForm" onsubmit="submitSiteVisit(event)" class="p-6 md:p-8">
                <div class="rounded-[24px] border border-sky-100 bg-gradient-to-br from-slate-50 to-sky-50 p-5 md:p-7">
                    <div class="mb-5">
                        <h4 class="text-xl font-bold text-slate-900">Visit Planning</h4>
                        <p class="mt-1 text-sm text-slate-600">Visit select karte hi yahin se date, time, project aur basic visit details set karo.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $visitDateField['label'] }} @if($visitDateField['required'])<span class="text-red-500">*</span>@endif</label>
                            <input type="date" name="visit_date" @if($visitDateField['required']) required @endif class="w-full rounded-2xl border border-emerald-400 px-4 py-3 text-base text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $visitTimeField['label'] }} @if($visitTimeField['required'])<span class="text-red-500">*</span>@endif</label>
                            <input type="time" name="visit_time" @if($visitTimeField['required']) required @endif class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $visitTypeField['label'] }}</label>
                            <select name="visit_type" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                                @foreach($visitTypeField['options'] as $option)
                                    @php
                                        $optionValue = \Illuminate\Support\Str::of($option)->lower()->replace([' ', '-'], '_')->value();
                                    @endphp
                                    <option value="{{ $optionValue }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $visitProjectField['label'] }}</label>
                            <input type="text" id="siteVisitProjectInput" name="project_name" placeholder="{{ $visitProjectField['placeholder'] }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                            <input type="hidden" name="project" id="siteVisitProjectHidden">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $visitLocationField['label'] }}</label>
                            <input type="text" name="visit_location" placeholder="{{ $visitLocationField['placeholder'] }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $visitNotesField['label'] }}</label>
                            <textarea name="visit_notes" rows="4" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" placeholder="{{ $visitNotesField['placeholder'] }}"></textarea>
                        </div>
                    </div>
                    <label class="mt-4 inline-flex items-center gap-3 text-sm font-semibold text-slate-900">
                        <input type="checkbox" name="visit_reminder" checked class="h-4 w-4 rounded border-slate-300 text-red-500 focus:ring-red-400">
                        <span>{{ $visitReminderField['label'] }}</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeSiteVisitModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white">Save Visit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Call Task Modal -->
<div id="scheduleCallTaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Schedule Call Task</h3>
                <button onclick="closeScheduleCallTaskModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="scheduleCallTaskForm" onsubmit="submitScheduleCallTask(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time *</label>
                        <input type="datetime-local" name="scheduled_at" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <small class="text-xs text-gray-500 mt-1 block">Select when you want to make the call</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Task Notes (Optional)</label>
                        <textarea name="notes" rows="3" placeholder="Add any notes or reminders for this call task..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeScheduleCallTaskModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200">Schedule Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($user && ($user->isAdmin() || $user->isCrm()))
@php
    $currentOwnerId = optional($lead->activeAssignments->first())->assigned_to;
@endphp
<div id="ownerTransferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Change Lead Owner</h3>
                <button onclick="closeOwnerTransferModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="ownerTransferForm" onsubmit="submitOwnerTransfer(event)">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Owner *</label>
                        <select id="ownerTransferAssignedTo" name="assigned_to" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select user</option>
                            @foreach(($ownerTransferUsers ?? collect()) as $transferUser)
                                <option value="{{ $transferUser->id }}" {{ (int) $currentOwnerId === (int) $transferUser->id ? 'selected' : '' }}>
                                    {{ $transferUser->name }} ({{ $transferUser->role->name ?? 'User' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" id="ownerTransferCreateCallingTask" name="create_calling_task" checked class="mt-1">
                            <span class="text-sm text-gray-700">Create calling task for new owner</span>
                        </label>
                    </div>
                    <input type="hidden" id="ownerTransferExistingTasks" name="transfer_existing_tasks" value="1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                        <textarea id="ownerTransferNotes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Reason for owner change"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeOwnerTransferModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-all duration-200">Transfer Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Meeting Modal -->
<div id="meetingModal" class="fixed inset-0 bg-black/40 hidden overflow-y-auto h-full w-full z-50">
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-6xl rounded-[28px] bg-white shadow-2xl overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                <h3 class="text-xl font-bold text-slate-900">Meeting</h3>
                <button onclick="closeMeetingModal()" class="text-slate-400 hover:text-slate-600 text-xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="meetingForm" onsubmit="submitMeeting(event)" class="p-6 md:p-8">
                <div class="rounded-[24px] border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 md:p-7">
                    <div class="mb-5">
                        <h4 class="text-xl font-bold text-emerald-800">Meeting Planning</h4>
                        <p class="mt-1 text-sm text-slate-600">Meeting select karte hi yahin se type, date, time aur mode set karo.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $meetingTypeField['label'] }} @if($meetingTypeField['required'])<span class="text-red-500">*</span>@endif</label>
                            <select id="meeting_type" name="meeting_type" @if($meetingTypeField['required']) required @endif class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select meeting type</option>
                                @foreach($meetingTypeField['options'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="meeting_sequence" name="meeting_sequence" value="1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $meetingDateField['label'] }} @if($meetingDateField['required'])<span class="text-red-500">*</span>@endif</label>
                            <input type="date" name="meeting_date" id="meeting_date" @if($meetingDateField['required']) required @endif class="w-full rounded-2xl border border-emerald-400 px-4 py-3 text-base text-slate-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $meetingTimeField['label'] }} @if($meetingTimeField['required'])<span class="text-red-500">*</span>@endif</label>
                            <input type="time" name="meeting_time" id="meeting_time" @if($meetingTimeField['required']) required @endif class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $meetingModeField['label'] }} @if($meetingModeField['required'])<span class="text-red-500">*</span>@endif</label>
                            <select name="meeting_mode" id="meeting_mode" @if($meetingModeField['required']) required @endif class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" onchange="toggleMeetingModeFields()">
                                @foreach($meetingModeField['options'] as $option)
                                    @php
                                        $optionValue = strtolower($option) === 'offline' ? 'offline' : 'online';
                                    @endphp
                                    <option value="{{ $optionValue }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="meetingLinkField" class="md:col-span-2 hidden">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $meetingLinkField['label'] }}</label>
                            <input type="url" name="meeting_link" placeholder="{{ $meetingLinkField['placeholder'] }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div id="meetingLocationField" class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $meetingLocationField['label'] }}</label>
                            <input type="text" name="location" id="location_input" placeholder="{{ $meetingLocationField['placeholder'] }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $meetingNotesField['label'] }}</label>
                            <textarea name="meeting_notes" rows="4" placeholder="{{ $meetingNotesField['placeholder'] }}" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 resize-none"></textarea>
                        </div>
                    </div>
                    <label class="mt-4 inline-flex items-center gap-3 text-sm font-semibold text-slate-900">
                        <input type="checkbox" name="reminder_enabled" checked class="h-4 w-4 rounded border-slate-300 text-red-500 focus:ring-red-400">
                        <span>{{ $meetingReminderField['label'] }}</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeMeetingModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white">Save Meeting</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Meeting Post-Call Popup Component -->
@include('components.meeting-post-call-popup')


{{-- Lead Requirements Modal --}}
<div id="leadRequirementsModal" class="lead-requirements-overlay" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.6)">
    <div class="lead-requirements-modal">
        <div class="lead-requirements-modal-head">
            <h2 id="leadReqModalTitle">Lead Detail Form</h2>
            <button onclick="closeLeadRequirementsModal()" class="lead-requirements-close-btn" aria-label="Close lead detail form">&times;</button>
        </div>
        <div class="lead-requirements-modal-body">
            <div id="managerLeadFormContainer" style="overflow-y:auto;flex:1">
                <div id="leadReqLoading" style="text-align:center;padding:40px;color:#666">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
let _lrLeadId = null;
window.managerLeadMeetingCreateUrl = '{{ route("sales-manager.meetings.create") }}';
window.managerLeadSiteVisitCreateUrl = '{{ route("sales-manager.site-visits.create") }}';
window.leadDetailRequirementsFormConfig = @json($leadDetailRequirementsFormConfig);
window.leadDetailOutputFormConfig = @json($leadDetailOutputFormConfig);
const LEAD_DETAIL_API_TOKEN = document.querySelector('meta[name="api-token"]')?.content || @json(session('api_token') ?? '');

if (LEAD_DETAIL_API_TOKEN) {
    localStorage.setItem('sales_manager_token', LEAD_DETAIL_API_TOKEN);
}

function getLeadRequirementHeaders(includeJson = false) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const apiToken = LEAD_DETAIL_API_TOKEN || localStorage.getItem('sales_manager_token') || '';
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
    };

    if (includeJson) {
        headers['Content-Type'] = 'application/json';
    }

    if (apiToken) {
        headers['Authorization'] = `Bearer ${apiToken}`;
    }

    return headers;
}

window.openLeadRequirementsModal = async function(leadId) {
    _lrLeadId = leadId;
    const modal = document.getElementById('leadRequirementsModal');
    modal.style.display = 'flex';
    document.getElementById('managerLeadFormContainer').innerHTML = '<div style="text-align:center;padding:40px;color:#666">Loading...</div>';

    try {
        const res = await fetch('/api/leads/' + leadId + '/requirement-form', {
            headers: getLeadRequirementHeaders()
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load');

        // Use same renderManagerLeadForm from tasks.blade.php (loaded via layout)
        if (typeof renderManagerLeadForm === 'function') {
            window.currentTaskId = null;
            renderManagerLeadForm(data);
        } else {
            document.getElementById('managerLeadFormContainer').innerHTML = '<p style="color:red">Form renderer not available. Please use the tasks page.</p>';
        }
    } catch(e) {
        document.getElementById('managerLeadFormContainer').innerHTML = '<p style="color:red">' + e.message + '</p>';
    }
};

window.closeLeadRequirementsModal = function() {
    document.getElementById('leadRequirementsModal').style.display = 'none';
    _lrLeadId = null;
};

window.submitLeadRequirementsFromShow = async function() {
    if (!_lrLeadId) return;
    const btn = document.getElementById('leadReqSubmitBtn');
    const defaultButtonText = btn ? btn.textContent : 'Save requirements';
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Saving...';
    }

    const payload = window.buildLeadRequirementsPayload
        ? window.buildLeadRequirementsPayload()
        : null;
    const validationError = !payload || !payload.customer_name || !payload.phone
        ? 'Please complete the lead requirements form'
        : '';

    if (validationError) {
        if (window.showAlert) {
            window.showAlert(validationError, 'warning');
        } else {
            alert(validationError);
        }
        if (btn) {
            btn.disabled = false;
            btn.textContent = defaultButtonText;
        }
        return;
    }

    try {
        const res = await fetch('/api/leads/' + _lrLeadId + '/update-requirements', {
            method: 'POST',
            headers: getLeadRequirementHeaders(true),
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Save failed');

        closeLeadRequirementsModal();
        if (window.showAlert) {
            window.showAlert('Requirements saved successfully!', 'success', 3000);
        } else {
            const t = document.createElement('div');
            t.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#205A44;color:white;padding:12px 20px;border-radius:8px;z-index:99999;font-size:14px';
            t.textContent = 'Requirements saved successfully!';
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }
        setTimeout(() => {
            window.location.reload();
        }, 350);
    } catch(e) {
        document.getElementById('managerLeadFormContainer').insertAdjacentHTML('afterbegin',
            '<p style="color:red;margin-bottom:12px">' + e.message + '</p>');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = defaultButtonText;
        }
    }
};

document.getElementById('leadRequirementsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeLeadRequirementsModal();
    }
});
</script>

<script src="/js/manager-lead-form.js"></script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/manager-lead-form.css') }}">
<style>
    #leadRequirementsModal .lead-requirements-modal {
        width: min(1100px, calc(100vw - 24px));
        max-height: calc(100vh - 24px);
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.35);
        border: 1px solid rgba(15, 23, 42, 0.14);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    #leadRequirementsModal .lead-requirements-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: linear-gradient(135deg, #063A1C 0%, #205A44 100%);
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
    }
    #leadRequirementsModal .lead-requirements-modal-head h2 {
        color: #ffffff;
        font-size: 1.85rem;
        line-height: 1.1;
        font-weight: 700;
        margin: 0;
    }
    #leadRequirementsModal .lead-requirements-close-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.28);
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        font-size: 28px;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    #leadRequirementsModal .lead-requirements-modal-body {
        flex: 1 1 auto;
        overflow: hidden;
    }
    #leadRequirementsModal #managerLeadFormContainer {
        height: 100%;
        overflow-y: auto !important;
        padding: 14px;
        background: #f8faf9;
    }

    .timeline-item {
        position: relative;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    
    /* Lead Detail Page Responsive Fixes */
    .lead-detail-container {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: 0;
        box-sizing: border-box;
        overflow-x: hidden; /* Prevent horizontal scroll */
    }
    
    /* Ensure proper word wrapping */
    .word-wrap {
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
    }
    
    /* Desktop view fixes - prevent overflow and ensure proper layout */
    @media (min-width: 1024px) {
        .lead-detail-container {
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        
        /* Ensure grid uses full width properly - respect Tailwind lg:grid-cols-3 */
        .lead-detail-container > .grid.lg\:grid-cols-3 {
            width: 100% !important;
            max-width: 100% !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 1.5rem !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
            display: grid !important;
        }
        
        /* Ensure col-span classes work properly */
        .lead-detail-container > .grid > .lg\:col-span-1 {
            grid-column: span 1 / span 1 !important;
            min-width: 0 !important;
            max-width: 100% !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        
        .lead-detail-container > .grid > .lg\:col-span-2 {
            grid-column: span 2 / span 2 !important;
            min-width: 0 !important;
            max-width: 100% !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        
        /* Prevent any child from causing overflow */
        .lead-detail-container > .grid > * {
            min-width: 0 !important;
            box-sizing: border-box !important;
        }
        
        /* Ensure all cards use proper width */
        .lead-detail-container .bg-white {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        
        /* Fix grid inside cards */
        .lead-detail-container .grid.grid-cols-2 {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        
        /* Ensure no horizontal scroll */
        .lead-detail-container * {
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        /* Ensure grid children don't force full width on desktop */
        .lead-detail-container > .grid > .lg\:col-span-1 > *,
        .lead-detail-container > .grid > .lg\:col-span-2 > * {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
    }
    
    /* Mobile specific fixes */
    @media (max-width: 640px) {
        .lead-detail-container {
            padding: 0 8px;
        }
        
        /* Ensure grid takes full width on mobile */
        .lead-detail-container .grid {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        
        /* Fix contact details spacing */
        .lead-detail-container .space-y-3 > * + * {
            margin-top: 0.75rem;
        }
        
        /* Ensure buttons don't overflow */
        .lead-detail-container button,
        .lead-detail-container a {
            max-width: 100%;
            box-sizing: border-box;
        }
        
        /* Meeting Modal - Mobile optimization */
        #meetingModal {
            padding: 0.5rem !important;
        }
        
        #meetingModal > div {
            max-width: 100% !important;
        }
        
        #meetingModal .bg-white {
            max-height: calc(100vh - 1rem) !important;
            margin-bottom: 0 !important;
        }
        
        /* Compact spacing */
        #meetingModal .px-6 {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        
        #meetingModal .py-5 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
        
        /* Compact button footer on mobile */
        #meetingModal .bg-gray-50 {
            padding: 0.75rem 1rem !important;
        }
        
        /* Smaller buttons on mobile */
        #meetingModal button {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
            font-size: 0.875rem !important;
        }
    }
    
    /* Prevent content cutoff */
    .lead-detail-container * {
        box-sizing: border-box;
    }
    
    /* Ensure text doesn't overflow */
    .lead-detail-container p,
    .lead-detail-container span,
    .lead-detail-container a {
        word-break: break-word;
        overflow-wrap: break-word;
    }

    /* Meeting Modal Clean Layout */
    #meetingModal {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    #meetingModal.hidden {
        display: none !important;
    }
    
    /* Remove extra spacing in form */
    #meetingForm .space-y-4 > * + * {
        margin-top: 1rem !important;
    }
    
    /* Compact button footer - NO extra space */
    #meetingModal .bg-gray-50 {
        min-height: auto !important;
        padding-bottom: 0.875rem !important;
    }
    
    /* Ensure buttons are properly styled */
    #meetingModal button {
        white-space: nowrap;
    }
    
    /* Remove any bottom margin/padding from modal container */
    #meetingModal .bg-white {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    @media (max-width: 1024px) {
        #leadRequirementsModal .lead-requirements-modal {
            width: calc(100vw - 16px);
            max-height: calc(100vh - 16px);
            border-radius: 12px;
        }
        #leadRequirementsModal .lead-requirements-modal-head {
            padding: 12px 14px;
        }
        #leadRequirementsModal .lead-requirements-modal-head h2 {
            font-size: 1.2rem;
        }
        #leadRequirementsModal .lead-requirements-close-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            font-size: 24px;
        }
        #leadRequirementsModal #managerLeadFormContainer {
            padding: 10px;
        }
    }
</style>
@endpush

@push('styles')
<style>
    .site-visit-project-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background-color: #10b981;
        color: white;
        border-radius: 9999px;
        font-size: 14px;
        font-weight: 500;
    }
    .site-visit-project-tag-remove {
        cursor: pointer;
        margin-left: 4px;
        opacity: 0.8;
        font-size: 12px;
    }
    .site-visit-project-tag-remove:hover {
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
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
</script>

<style>
    @keyframes tickDraw {
        0% {
            stroke-dasharray: 0, 100;
            stroke-dashoffset: 0;
        }
        100% {
            stroke-dasharray: 100, 0;
            stroke-dashoffset: 0;
        }
    }

    @keyframes tickScale {
        0% {
            transform: scale(0);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes circleDraw {
        0% {
            stroke-dasharray: 0, 138;
            stroke-dashoffset: 0;
        }
        100% {
            stroke-dasharray: 138, 0;
            stroke-dashoffset: 0;
        }
    }

    .success-tick-container {
        animation: tickScale 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .success-tick .tick-circle {
        stroke-dasharray: 0, 138;
        animation: circleDraw 0.6s ease-out forwards;
    }

    .success-tick .tick-path {
        stroke-dasharray: 0, 30;
        animation: tickDraw 0.4s ease-out 0.3s forwards;
    }
</style>
@endpush
