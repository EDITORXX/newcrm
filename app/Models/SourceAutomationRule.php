<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceAutomationRule extends Model
{
    protected $fillable = [
        'name',
        'source',
        'fb_form_id',
        'google_sheet_config_id',
        'assignment_method',
        'single_user_id',
        'auto_create_task',
        'daily_limit',
        'fallback_user_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'auto_create_task' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(SourceAutomationRuleUser::class, 'rule_id');
    }

    public function fbForm()
    {
        return $this->belongsTo(FbForm::class, 'fb_form_id');
    }

    public function googleSheetConfig()
    {
        return $this->belongsTo(GoogleSheetsConfig::class, 'google_sheet_config_id');
    }

    public function singleUser()
    {
        return $this->belongsTo(User::class, 'single_user_id');
    }

    public function fallbackUser()
    {
        return $this->belongsTo(User::class, 'fallback_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getSourceLabel(string $source): string
    {
        return match($source) {
            'facebook_lead_ads' => 'Facebook Lead Ads',
            'pabbly'            => 'Pabbly',
            'mcube'             => 'MCube',
            'google_sheets'     => 'Google Sheets',
            'csv'               => 'CSV Import',
            'all'               => 'All Sources',
            default             => ucfirst($source),
        };
    }

    public static function getMethodLabel(string $method): string
    {
        return match($method) {
            'round_robin'     => 'Round Robin',
            'first_available' => 'First Available',
            'percentage'      => 'Percentage',
            'single_user'     => 'Single User',
            default           => ucfirst($method),
        };
    }
}
