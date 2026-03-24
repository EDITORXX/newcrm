<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Builder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the projects for the builder.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the contacts for the builder.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(BuilderContact::class);
    }

    /**
     * Get active contacts for the builder.
     */
    public function activeContacts(): HasMany
    {
        return $this->hasMany(BuilderContact::class)->where('is_active', true);
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        return asset('storage/builders/logos/' . $this->logo);
    }

    /**
     * Check if builder is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get word count of description.
     */
    public function getDescriptionWordCount(): int
    {
        if (!$this->description) {
            return 0;
        }

        return str_word_count($this->description);
    }
}
