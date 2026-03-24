<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'template_id',
        'name',
        'content',
        'category',
        'language',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Sync templates from API
     */
    public static function syncFromAPI(array $templates): void
    {
        foreach ($templates as $template) {
            self::updateOrCreate(
                ['template_id' => $template['id'] ?? $template['template_id']],
                [
                    'name' => $template['name'] ?? '',
                    'content' => $template['content'] ?? $template['body'] ?? '',
                    'category' => $template['category'] ?? null,
                    'language' => $template['language'] ?? 'en',
                    'is_active' => $template['status'] === 'APPROVED' ?? true,
                ]
            );
        }
    }

    /**
     * Get available active templates
     */
    public static function getAvailableTemplates()
    {
        return self::where('is_active', true)->orderBy('name')->get();
    }
}
