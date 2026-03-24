<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_type',
        'file_name',
        'google_sheet_id',
        'google_sheet_name',
        'total_leads',
        'imported_leads',
        'failed_leads',
        'status',
        'assignment_rule_id',
        'automation_id',
        'error_log',
    ];

    protected $casts = [
        'error_log' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignmentRule(): BelongsTo
    {
        return $this->belongsTo(AssignmentRule::class);
    }


    public function importedLeads(): HasMany
    {
        return $this->hasMany(ImportedLead::class);
    }
}

