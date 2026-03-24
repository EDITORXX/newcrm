<?php

namespace App\Services;

use App\Models\GoogleSheetsColumnMapping;
use App\Models\GoogleSheetsConfig;
use Illuminate\Support\Str;

class FieldMappingService
{
    /**
     * Map fields from payload using configuration
     */
    public function mapFields(array $payload, GoogleSheetsConfig $config): array
    {
        $mappedData = [];
        $columnMappings = $config->columnMappings;

        // Build mapping array: sheet_column => lead_field_key
        $mappingArray = [];
        foreach ($columnMappings as $mapping) {
            $mappingArray[$mapping->sheet_column] = $mapping->lead_field_key;
        }

        // Map each field from payload
        foreach ($payload as $key => $value) {
            // Try to find mapping by column letter (if key is column letter)
            if (isset($mappingArray[$key])) {
                $mappedData[$mappingArray[$key]] = $value;
            } else {
                // Try alias matching
                $mappedKey = $this->detectFieldName($key, array_values($mappingArray));
                if ($mappedKey) {
                    $mappedData[$mappedKey] = $value;
                } else {
                    // Store in notes if not mapped
                    if (!isset($mappedData['notes'])) {
                        $mappedData['notes'] = '';
                    }
                    $mappedData['notes'] .= ($mappedData['notes'] ? "\n" : '') . ucfirst(str_replace('_', ' ', $key)) . ": " . $value;
                }
            }
        }

        return $mappedData;
    }

    /**
     * Map fields from Google Apps Script payload (field names, not column letters)
     */
    public function mapFieldsFromPayload(array $payload, GoogleSheetsConfig $config): array
    {
        $mappedData = [];
        $columnMappings = $config->columnMappings;

        // Build reverse mapping: sheet column header => lead_field_key
        $mappingArray = [];
        foreach ($columnMappings as $mapping) {
            // Use field_label as key (this is the sheet column header)
            $mappingArray[strtolower($mapping->field_label ?? '')] = $mapping->lead_field_key;
        }

        // Also try direct field name matching
        foreach ($payload as $key => $value) {
            $normalizedKey = $this->normalizeFieldName($key);
            
            // Try direct mapping
            if (isset($mappingArray[$normalizedKey])) {
                $mappedData[$mappingArray[$normalizedKey]] = $value;
            } else {
                // Try alias matching
                $mappedKey = $this->detectFieldName($key, array_values($mappingArray));
                if ($mappedKey) {
                    $mappedData[$mappedKey] = $value;
                } else {
                    // Store in notes
                    if (!isset($mappedData['notes'])) {
                        $mappedData['notes'] = '';
                    }
                    $mappedData['notes'] .= ($mappedData['notes'] ? "\n" : '') . ucfirst(str_replace('_', ' ', $key)) . ": " . $value;
                }
            }
        }

        return $mappedData;
    }

    /**
     * Auto-detect field name by value or common aliases
     */
    public function detectFieldName(string $fieldName, array $possibleNames): ?string
    {
        $normalized = $this->normalizeFieldName($fieldName);

        // Common field name aliases
        $aliases = [
            'name' => ['full_name', 'customer_name', 'contact_name', 'client_name', 'name'],
            'phone' => ['phone_number', 'mobile', 'contact_number', 'phone', 'mobile_number', 'whatsapp_number'],
            'email' => ['email_address', 'email', 'e_mail'],
            'city' => ['city', 'location', 'are_u_from_lucknow'],
            'state' => ['state', 'province'],
            'property_type' => ['what_kind_of_property', 'property_type', 'property'],
            'budget' => ['budget_approx', 'budget', 'price_range'],
            'requirements' => ['great_what_are_you_looking_for', 'requirements', 'requirement', 'needs'],
            'notes' => ['notes', 'additional_info', 'comments'],
        ];

        // Check aliases
        foreach ($aliases as $standardField => $aliasList) {
            foreach ($aliasList as $alias) {
                if (stripos($normalized, $alias) !== false || stripos($alias, $normalized) !== false) {
                    if (in_array($standardField, $possibleNames)) {
                        return $standardField;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Normalize field name for matching
     */
    public function normalizeFieldName(string $fieldName): string
    {
        return strtolower(trim(str_replace(['_', '-', ' ', '?', '??'], '_', $fieldName)));
    }

    /**
     * Get standard field mappings
     */
    public function getStandardMappings(): array
    {
        return [
            'name' => ['required' => true, 'label' => 'Customer Name'],
            'phone' => ['required' => true, 'label' => 'Phone Number'],
            'email' => ['required' => false, 'label' => 'Email Address'],
            'city' => ['required' => false, 'label' => 'City'],
            'state' => ['required' => false, 'label' => 'State'],
            'property_type' => ['required' => false, 'label' => 'Property Type'],
            'budget' => ['required' => false, 'label' => 'Budget'],
            'requirements' => ['required' => false, 'label' => 'Requirements'],
            'notes' => ['required' => false, 'label' => 'Notes'],
            'source' => ['required' => false, 'label' => 'Lead Source'],
            'preferred_location' => ['required' => false, 'label' => 'Preferred Location'],
            'preferred_size' => ['required' => false, 'label' => 'Preferred Size'],
            'use_end_use' => ['required' => false, 'label' => 'End Use'],
            'possession_status' => ['required' => false, 'label' => 'Possession Status'],
        ];
    }

    /**
     * Get pre-configured form template
     */
    public function getFormTemplate(string $formType): array
    {
        $templates = [
            'meta_facebook' => [
                'full_name' => 'name',
                'phone_number' => 'phone',
                'whatsapp_number' => 'notes', // Store in notes
                'email' => 'email',
                'are_u_from_lucknow_??' => 'city',
                'great_,_what_are_you_looking_for' => 'requirements',
                'purpose_for_purchase' => 'use_end_use',
                'what_kind_of_property' => 'property_type',
                'budget_approx_?' => 'budget',
                'when_to_buy' => 'possession_status',
                'meeting_time_to_discuss_in_details' => 'notes', // Store in notes
                'job_title' => 'notes', // Store in notes
            ],
            'google_forms' => [
                'Name' => 'name',
                'Email' => 'email',
                'Phone' => 'phone',
                'Mobile' => 'phone',
            ],
        ];

        return $templates[$formType] ?? [];
    }
}
