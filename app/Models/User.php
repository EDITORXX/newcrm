<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_picture',
        'role_id',
        'manager_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(LeadAssignment::class, 'assigned_to');
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class, 'assigned_to');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class, 'user_id');
    }

    // Permission checks
    public function isAdmin(): bool
    {
        // Ensure role relationship is loaded
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        
        return $this->role && $this->role->slug === Role::ADMIN;
    }

    public function isCrm(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::CRM;
    }

    public function isSalesManager(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::SALES_MANAGER;
    }

    /**
     * Check if user is Sales Head (Senior Manager with no manager or top-level manager)
     */
    public function isSalesHead(): bool
    {
        // Ensure role is loaded
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        
        if (!$this->isSalesManager()) {
            return false;
        }
        
        // Sales Head is a Senior Manager with no manager (manager_id is null)
        return $this->manager_id === null;
    }

    /**
     * Get all team members including nested teams (for Sales Head)
     */
    public function getAllTeamMembers(): \Illuminate\Support\Collection
    {
        $allMembers = collect();
        
        // Get direct team members
        $directMembers = $this->teamMembers()->with('role')->get();
        $allMembers = $allMembers->merge($directMembers);
        
        // Recursively get nested team members
        foreach ($directMembers as $member) {
            if ($member->isSalesManager() || $member->isAssistantSalesManager() || $member->isSeniorManager()) {
                $nestedMembers = $member->getAllTeamMembers();
                $allMembers = $allMembers->merge($nestedMembers);
            }
        }
        
        return $allMembers->unique('id');
    }

    /**
     * Get all team member IDs including nested teams (for Sales Head)
     */
    public function getAllTeamMemberIds(): array
    {
        return $this->getAllTeamMembers()->pluck('id')->toArray();
    }

    /**
     * Check if this user is a senior (in the hierarchy chain) of another user
     * A user is considered senior if they are:
     * - The direct manager of the user
     * - A Sales Head and the user is in their team (directly or indirectly)
     * - In the manager chain above the user
     */
    public function isSeniorOf(User $otherUser): bool
    {
        // If other user has no manager, they are Sales Head - only Sales Head can verify Sales Head's meetings
        if (!$otherUser->manager_id) {
            return $this->isSalesHead() && $this->id === $otherUser->id;
        }
        
        // Check if current user is the direct manager
        if ($otherUser->manager_id === $this->id) {
            return true;
        }
        
        // Check if current user is Sales Head and other user is in their team
        if ($this->isSalesHead()) {
            $teamMemberIds = $this->getAllTeamMemberIds();
            return in_array($otherUser->id, $teamMemberIds);
        }
        
        // Recursively check manager chain
        $manager = $otherUser->manager;
        while ($manager) {
            if ($manager->id === $this->id) {
                return true;
            }
            $manager = $manager->manager;
        }
        
        return false;
    }

    /**
     * Check if user is Sales Executive (previously Telecaller)
     */
    public function isSalesExecutive(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::SALES_EXECUTIVE;
    }

    /**
     * Check if user is Assistant Sales Manager (previously Sales Executive)
     */
    public function isAssistantSalesManager(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::ASSISTANT_SALES_MANAGER;
    }

    /**
     * Check if user is Manager (role slug senior_manager)
     */
    public function isSeniorManager(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::SENIOR_MANAGER;
    }

    /**
     * Check if user is HR Manager
     */
    public function isHrManager(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::HR_MANAGER;
    }

    /**
     * Check if user is Finance Manager
     */
    public function isFinanceManager(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::FINANCE_MANAGER;
    }

    /**
     * Check if user is Telecaller (either Telecaller or Sales Executive role).
     * Both roles get telecaller token and same behaviour for tasks/leads/verification/profile.
     */
    public function isTelecaller(): bool
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        return $this->role && $this->role->slug === Role::SALES_EXECUTIVE;
    }

    /**
     * Get display role name for the user
     * Returns "Associate Director" for Sales Head, otherwise returns role name
     */
    public function getDisplayRoleName(): string
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        
        // Sales Head (Senior Manager with no manager) displays as "Associate Director"
        if ($this->isSalesHead()) {
            return 'Associate Director';
        }
        
        return $this->role ? $this->role->name : 'Unknown';
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin() || $this->isCrm() || $this->isSalesHead();
    }

    public function canViewAllLeads(): bool
    {
        return in_array($this->role->slug, [Role::ADMIN, Role::CRM, Role::SALES_MANAGER]) || $this->isSalesHead();
    }

    public function canAssignLeads(): bool
    {
        return in_array($this->role->slug, [Role::ADMIN, Role::CRM, Role::SALES_MANAGER]) || $this->isSalesHead();
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function telecallerProfile(): HasOne
    {
        return $this->hasOne(TelecallerProfile::class);
    }

    public function telecallerDailyLimit(): HasOne
    {
        return $this->hasOne(TelecallerDailyLimit::class);
    }

    public function salesManagerProfile(): HasOne
    {
        return $this->hasOne(SalesManagerProfile::class);
    }

    public function crmAssignments(): HasMany
    {
        return $this->hasMany(CrmAssignment::class, 'assigned_to');
    }

    public function createdProspects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'created_by');
    }

    public function managedProspects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'assigned_manager');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(Target::class);
    }

    /**
     * Get the full URL for the profile picture
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        if (!$this->profile_picture) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($this->profile_picture, FILTER_VALIDATE_URL)) {
            return $this->profile_picture;
        }

        // Return the storage URL
        return asset('storage/' . $this->profile_picture);
    }
}

