<?php

namespace App\Services;

use App\Models\UserProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserStatusService
{
    /**
     * Get or create user profile
     */
    public function getOrCreateProfile(int $userId): UserProfile
    {
        return UserProfile::firstOrCreate(
            ['user_id' => $userId],
            [
                'is_absent' => false,
            ]
        );
    }

    /**
     * Toggle absent status
     */
    public function toggleAbsentStatus(int $userId, bool $isAbsent, ?string $reason = null, ?Carbon $absentUntil = null): UserProfile
    {
        $profile = $this->getOrCreateProfile($userId);
        
        $profile->update([
            'is_absent' => $isAbsent,
            'absent_reason' => $isAbsent ? $reason : null,
            'absent_until' => $isAbsent ? $absentUntil : null,
        ]);

        return $profile;
    }

    /**
     * Mark user as present (not absent)
     */
    public function markAsPresent(int $userId): UserProfile
    {
        $profile = $this->getOrCreateProfile($userId);
        
        $profile->update([
            'is_absent' => false,
            'absent_reason' => null,
            'absent_until' => null,
        ]);

        return $profile;
    }

    /**
     * Check if user is absent
     */
    public function isUserAbsent(int $userId): bool
    {
        $profile = UserProfile::where('user_id', $userId)->first();
        
        if (!$profile) {
            return false; // No profile means not absent
        }

        return $profile->isCurrentlyAbsent();
    }

    /**
     * Check if user can receive leads (based on absent status only)
     */
    public function canUserReceiveLeads(int $userId): bool
    {
        return !$this->isUserAbsent($userId);
    }
}
