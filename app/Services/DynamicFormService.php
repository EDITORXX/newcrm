<?php

namespace App\Services;

use App\Models\DynamicForm;

class DynamicFormService
{
    /**
     * Get a published dynamic form by location path
     *
     * @param string $locationPath
     * @return DynamicForm|null
     */
    public function getPublishedFormByLocation(string $locationPath): ?DynamicForm
    {
        return DynamicForm::where('location_path', $locationPath)
            ->where('status', 'published')
            ->where('is_active', true)
            ->with(['fields' => function($query) {
                $query->orderBy('order');
            }])
            ->first();
    }

    /**
     * Check if a published dynamic form exists for a location path
     *
     * @param string $locationPath
     * @return bool
     */
    public function hasPublishedForm(string $locationPath): bool
    {
        return DynamicForm::where('location_path', $locationPath)
            ->where('status', 'published')
            ->where('is_active', true)
            ->exists();
    }
}
