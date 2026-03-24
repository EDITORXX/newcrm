<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FbPage extends Model
{
    protected $table = 'fb_pages';

    protected $fillable = [
        'page_id',
        'page_name',
        'page_access_token',
        'token_reference',
    ];

    protected $casts = [
        'page_access_token' => 'encrypted',
    ];

    protected $hidden = [
        'page_access_token',
    ];

    public function forms(): HasMany
    {
        return $this->hasMany(FbForm::class, 'fb_page_id');
    }
}
