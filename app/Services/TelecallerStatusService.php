<?php

namespace App\Services;

use App\Models\TelecallerProfile;
use App\Models\User;
use App\Models\Role;
use App\Models\Lead;
use App\Models\LeadAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TelecallerStatusService
{
    /**
     * Get or create telecaller profile
     */
    public function getOrCreateProfile(int $userId): TelecallerProfile
    {
        return TelecallerProfile::firstOrCreate(
            ['user_id' => $userId],
            [
                'max_pending_leads' => 50,
                'is_absent' => false,
            ]
        );
    }

    /**
     * Toggle absent status
     */
    public function toggleAbsentStatus(int $userId, bool $isAbsent, ?string $reason = null, ?Carbon $absentUntil = null): TelecallerProfile
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
     * Check if telecaller is absent
     */
    public function isTelecallerAbsent(int $userId): bool
    {
        $profile = TelecallerProfile::where('user_id', $userId)->first();
        
        if (!$profile) {
            return false;
        }

        return $profile->isCurrentlyAbsent();
    }

    /**
     * Get pending leads count for telecaller
     */
    public function getPendingLeadsCount(int $userId): int
    {
        return LeadAssignment::where('assigned_to', $userId)
            ->where('is_active', true)
            ->whereHas('lead', function ($query) {
                $query->whereIn('status', ['new', 'contacted']);
            })
            ->count();
    }

    /**
     * Check if telecaller has reached pending threshold
     */
    public function hasReachedPendingThreshold(int $userId): bool
    {
        $profile = $this->getOrCreateProfile($userId);
        $pendingCount = $this->getPendingLeadsCount($userId);

        if ($profile->max_pending_leads <= 0) {
            return false; // No threshold set
        }

        return $pendingCount >= $profile->max_pending_leads;
    }

    /**
     * Get available telecallers (not absent, within limits, not at threshold)
     */
    public function getAvailableTelecallers(?array $excludeUserIds = []): \Illuminate\Database\Eloquent\Collection
    {
        $salesExecutiveRoleId = Role::where('slug', Role::SALES_EXECUTIVE)->value('id');

        $query = User::where('role_id', $salesExecutiveRoleId)
            ->where('is_active', true)
            ->whereDoesntHave('telecallerProfile', function ($q) {
                $q->where('is_absent', true)
                  ->where(function ($q2) {
                      $q2->whereNull('absent_until')
                         ->orWhere('absent_until', '>', Carbon::now());
                  });
            });

        if (!empty($excludeUserIds)) {
            $query->whereNotIn('id', $excludeUserIds);
        }

        return $query->get();
    }

    /**
     * Check if telecaller can receive assignment
     */
    public function canReceiveAssignment(int $userId): array
    {
        $isAbsent = $this->isTelecallerAbsent($userId);
        $hasReachedThreshold = $this->hasReachedPendingThreshold($userId);
        $pendingCount = $this->getPendingLeadsCount($userId);
        $profile = $this->getOrCreateProfile($userId);

        return [
            'can_receive' => !$isAbsent && !$hasReachedThreshold,
            'is_absent' => $isAbsent,
            'has_reached_threshold' => $hasReachedThreshold,
            'pending_count' => $pendingCount,
            'max_pending' => $profile->max_pending_leads,
        ];
    }
}

