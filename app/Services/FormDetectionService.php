<?php

namespace App\Services;

class FormDetectionService
{
    /**
     * Get field definitions for known form types
     */
    public function getFieldDefinitions(string $formType, string $locationPath = ''): array
    {
        $definitions = [];

        switch ($formType) {
            case 'meeting':
                $definitions = $this->getMeetingFormFields();
                break;
            case 'lead':
                $definitions = $this->getLeadFormFields();
                break;
            case 'prospect':
                // Check if it's the prospect details form from tasks
                if (str_contains($locationPath, 'prospect-details')) {
                    $definitions = $this->getProspectDetailsFormFields();
                } else {
                    $definitions = $this->getProspectFormFields();
                }
                break;
            case 'site_visit':
                $definitions = $this->getSiteVisitFormFields();
                break;
            default:
                // Try to detect from location path
                $definitions = $this->detectFieldsFromLocationPath($locationPath);
        }

        return $definitions;
    }

    /**
     * Get meeting form field definitions
     */
    protected function getMeetingFormFields(): array
    {
        return [
            [
                'field_key' => 'customer_name',
                'field_type' => 'text',
                'label' => 'Customer Name',
                'placeholder' => 'Enter customer name',
                'required' => true,
                'order' => 0,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'phone',
                'field_type' => 'text',
                'label' => 'Phone',
                'placeholder' => 'Enter phone number',
                'required' => true,
                'order' => 1,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'employee',
                'field_type' => 'text',
                'label' => 'Employee',
                'placeholder' => 'Enter employee name',
                'required' => false,
                'order' => 2,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'occupation',
                'field_type' => 'text',
                'label' => 'Occupation',
                'placeholder' => 'e.g. IT / Business',
                'required' => false,
                'order' => 3,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'date_of_visit',
                'field_type' => 'date',
                'label' => 'Date of Visit',
                'placeholder' => 'Select date',
                'required' => true,
                'order' => 4,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'time',
                'field_type' => 'text',
                'label' => 'Time',
                'placeholder' => 'Enter meeting time',
                'required' => false,
                'order' => 5,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'address',
                'field_type' => 'textarea',
                'label' => 'Address',
                'placeholder' => 'Enter meeting address',
                'required' => false,
                'order' => 6,
                'section' => 'Additional Information',
            ],
            [
                'field_key' => 'notes',
                'field_type' => 'textarea',
                'label' => 'Notes',
                'placeholder' => 'Additional notes',
                'required' => false,
                'order' => 7,
                'section' => 'Additional Information',
            ],
        ];
    }

    /**
     * Get lead form field definitions
     */
    protected function getLeadFormFields(): array
    {
        return [
            [
                'field_key' => 'name',
                'field_type' => 'text',
                'label' => 'Name',
                'placeholder' => 'Enter lead name',
                'required' => true,
                'order' => 0,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'phone',
                'field_type' => 'text',
                'label' => 'Phone',
                'placeholder' => 'Enter phone number',
                'required' => true,
                'order' => 1,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'email',
                'field_type' => 'email',
                'label' => 'Email',
                'placeholder' => 'Enter email address',
                'required' => false,
                'order' => 2,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'address',
                'field_type' => 'textarea',
                'label' => 'Address',
                'placeholder' => 'Enter full address',
                'required' => false,
                'order' => 3,
                'section' => 'Location Details',
            ],
            [
                'field_key' => 'city',
                'field_type' => 'text',
                'label' => 'City',
                'placeholder' => 'Enter city',
                'required' => false,
                'order' => 4,
                'section' => 'Location Details',
            ],
            [
                'field_key' => 'state',
                'field_type' => 'text',
                'label' => 'State',
                'placeholder' => 'Enter state',
                'required' => false,
                'order' => 5,
                'section' => 'Location Details',
            ],
            [
                'field_key' => 'pincode',
                'field_type' => 'text',
                'label' => 'Pincode',
                'placeholder' => 'Enter pincode',
                'required' => false,
                'order' => 6,
                'section' => 'Location Details',
            ],
            [
                'field_key' => 'preferred_location',
                'field_type' => 'text',
                'label' => 'Preferred Location',
                'placeholder' => 'Enter preferred location',
                'required' => false,
                'order' => 7,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'preferred_size',
                'field_type' => 'text',
                'label' => 'Preferred Size',
                'placeholder' => 'e.g., 2 BHK, 1200 sqft',
                'required' => false,
                'order' => 8,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'budget_min',
                'field_type' => 'number',
                'label' => 'Budget Min',
                'placeholder' => 'Enter minimum budget',
                'required' => false,
                'order' => 9,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'budget_max',
                'field_type' => 'number',
                'label' => 'Budget Max',
                'placeholder' => 'Enter maximum budget',
                'required' => false,
                'order' => 10,
                'section' => 'Property Preferences',
            ],
        ];
    }

    /**
     * Get prospect form field definitions
     */
    protected function getProspectFormFields(): array
    {
        return [
            [
                'field_key' => 'customer_name',
                'field_type' => 'text',
                'label' => 'Customer Name',
                'placeholder' => 'Enter customer name',
                'required' => true,
                'order' => 0,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'phone',
                'field_type' => 'text',
                'label' => 'Phone',
                'placeholder' => 'Enter phone number',
                'required' => true,
                'order' => 1,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'budget',
                'field_type' => 'number',
                'label' => 'Budget',
                'placeholder' => 'Enter budget',
                'required' => false,
                'order' => 2,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'preferred_location',
                'field_type' => 'text',
                'label' => 'Preferred Location',
                'placeholder' => 'Enter preferred location',
                'required' => false,
                'order' => 3,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'size',
                'field_type' => 'text',
                'label' => 'Size',
                'placeholder' => 'e.g., 2 BHK, 1200 sqft',
                'required' => false,
                'order' => 4,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'purpose',
                'field_type' => 'select',
                'label' => 'Purpose',
                'placeholder' => 'Select purpose',
                'required' => false,
                'options' => ['end_user', 'investment'],
                'order' => 5,
                'section' => 'Property Preferences',
            ],
        ];
    }

    /**
     * Get prospect details form field definitions (Senior Manager Tasks)
     */
    protected function getProspectDetailsFormFields(): array
    {
        return [
            [
                'field_key' => 'customer_name',
                'field_type' => 'text',
                'label' => 'Customer Name',
                'placeholder' => 'Enter customer name',
                'required' => true,
                'order' => 0,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'phone',
                'field_type' => 'text',
                'label' => 'Phone Number',
                'placeholder' => 'Enter phone number',
                'required' => true,
                'order' => 1,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'email',
                'field_type' => 'email',
                'label' => 'Email',
                'placeholder' => 'Enter email address',
                'required' => false,
                'order' => 2,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'address',
                'field_type' => 'textarea',
                'label' => 'Address',
                'placeholder' => 'Enter full address',
                'required' => false,
                'order' => 3,
                'section' => 'Address Information',
            ],
            [
                'field_key' => 'city',
                'field_type' => 'text',
                'label' => 'City',
                'placeholder' => 'Enter city',
                'required' => false,
                'order' => 4,
                'section' => 'Address Information',
            ],
            [
                'field_key' => 'state',
                'field_type' => 'text',
                'label' => 'State',
                'placeholder' => 'Enter state',
                'required' => false,
                'order' => 5,
                'section' => 'Address Information',
            ],
            [
                'field_key' => 'pincode',
                'field_type' => 'text',
                'label' => 'Pincode',
                'placeholder' => 'Enter pincode',
                'required' => false,
                'order' => 6,
                'section' => 'Address Information',
            ],
            [
                'field_key' => 'budget',
                'field_type' => 'number',
                'label' => 'Budget',
                'placeholder' => 'Enter budget',
                'required' => false,
                'order' => 7,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'preferred_location',
                'field_type' => 'text',
                'label' => 'Preferred Location',
                'placeholder' => 'e.g., South Mumbai, Bandra',
                'required' => false,
                'order' => 8,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'size',
                'field_type' => 'text',
                'label' => 'Size',
                'placeholder' => 'e.g., 2 BHK, 1200 sqft',
                'required' => false,
                'order' => 9,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'purpose',
                'field_type' => 'select',
                'label' => 'Purpose',
                'placeholder' => 'Select purpose',
                'required' => false,
                'options' => ['End User', 'Investment'],
                'order' => 10,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'possession',
                'field_type' => 'text',
                'label' => 'Possession',
                'placeholder' => 'Enter possession details',
                'required' => false,
                'order' => 11,
                'section' => 'Property Preferences',
            ],
            [
                'field_key' => 'lead_status',
                'field_type' => 'select',
                'label' => 'Lead Status',
                'placeholder' => 'Select lead status',
                'required' => true,
                'options' => ['Hot', 'Warm', 'Cold', 'Junk'],
                'order' => 12,
                'section' => 'Verification',
            ],
            [
                'field_key' => 'manager_remark',
                'field_type' => 'textarea',
                'label' => 'Manager Remark',
                'placeholder' => 'Enter remarks or notes...',
                'required' => false,
                'order' => 13,
                'section' => 'Verification',
            ],
        ];
    }

    /**
     * Get site visit form field definitions
     */
    protected function getSiteVisitFormFields(): array
    {
        return [
            [
                'field_key' => 'customer_name',
                'field_type' => 'text',
                'label' => 'Customer Name',
                'placeholder' => 'Enter customer name',
                'required' => true,
                'order' => 0,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'phone',
                'field_type' => 'text',
                'label' => 'Phone',
                'placeholder' => 'Enter phone number',
                'required' => true,
                'order' => 1,
                'section' => 'Basic Information',
            ],
            [
                'field_key' => 'visit_date',
                'field_type' => 'date',
                'label' => 'Visit Date',
                'placeholder' => 'Select visit date',
                'required' => true,
                'order' => 2,
                'section' => 'Visit Details',
            ],
            [
                'field_key' => 'visit_time',
                'field_type' => 'text',
                'label' => 'Visit Time',
                'placeholder' => 'Enter visit time',
                'required' => false,
                'order' => 3,
                'section' => 'Visit Details',
            ],
            [
                'field_key' => 'property_address',
                'field_type' => 'textarea',
                'label' => 'Property Address',
                'placeholder' => 'Enter property address',
                'required' => false,
                'order' => 4,
                'section' => 'Visit Details',
            ],
            [
                'field_key' => 'notes',
                'field_type' => 'textarea',
                'label' => 'Notes',
                'placeholder' => 'Additional notes',
                'required' => false,
                'order' => 5,
                'section' => 'Additional Information',
            ],
        ];
    }

    /**
     * Detect fields from location path
     */
    protected function detectFieldsFromLocationPath(string $locationPath): array
    {
        // Try to match location path to form type
        if (str_contains($locationPath, 'meeting')) {
            return $this->getMeetingFormFields();
        }
        if (str_contains($locationPath, 'lead')) {
            return $this->getLeadFormFields();
        }
        if (str_contains($locationPath, 'prospect-details')) {
            return $this->getProspectDetailsFormFields();
        }
        if (str_contains($locationPath, 'prospect')) {
            return $this->getProspectFormFields();
        }
        if (str_contains($locationPath, 'site-visit') || str_contains($locationPath, 'site_visit')) {
            return $this->getSiteVisitFormFields();
        }

        return [];
    }
}
