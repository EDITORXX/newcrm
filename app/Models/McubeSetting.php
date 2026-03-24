<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class McubeSetting extends Model
{
    protected $table = 'mcube_settings';

    protected $fillable = ['token', 'is_enabled'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /** Always return the single settings row (create if missing). */
    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'token'      => null,
            'is_enabled' => false,
        ]);
    }
}
