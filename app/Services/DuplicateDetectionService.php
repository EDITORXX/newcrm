<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\SiteVisit;
use App\Models\BlacklistedNumber;

class DuplicateDetectionService
{
    /**
     * Check if a phone number is blacklisted
     * 
     * @param string $phone
     * @return bool
     */
    public function isBlacklisted(string $phone): bool
    {
        // Sanitize phone number
        $phone = $this->sanitizePhone($phone);
        
        if (empty($phone)) {
            return false;
        }

        // Check blacklisted_numbers table
        return BlacklistedNumber::where('phone', $phone)->exists();
    }

    /**
     * Check if a phone number already exists in the system
     * 
     * @param string $phone
     * @return bool
     */
    public function isDuplicate(string $phone): bool
    {
        // Sanitize phone number
        $phone = $this->sanitizePhone($phone);
        
        if (empty($phone)) {
            return false;
        }

        // Check if blacklisted (blacklisted numbers should be treated as duplicates)
        if ($this->isBlacklisted($phone)) {
            return true;
        }

        // Check leads table
        if (Lead::where('phone', $phone)->exists()) {
            return true;
        }
        
        // Check lead_assignments via leads
        if (LeadAssignment::whereHas('lead', function($q) use ($phone) {
            $q->where('phone', $phone);
        })->exists()) {
            return true;
        }
        
        // Check site_visits via leads
        if (SiteVisit::whereHas('lead', function($q) use ($phone) {
            $q->where('phone', $phone);
        })->exists()) {
            return true;
        }
        
        return false;
    }

    /**
     * Sanitize phone number - remove non-numeric characters except +
     * 
     * @param string $phone
     * @return string
     */
    public function sanitizePhone(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        return trim($phone);
    }

    /**
     * Validate phone number format
     * 
     * @param string $phone
     * @return bool
     */
    public function isValidPhone(string $phone): bool
    {
        $phone = $this->sanitizePhone($phone);
        
        // E.164 max is 15 digits; require at least 10 (India/local)
        $digits = preg_replace('/[^0-9]/', '', $phone);
        
        $len = strlen($digits);
        return $len >= 10 && $len <= 15;
    }
}

