<?php

namespace App\Helpers;

class CollateralHelper
{
    /**
     * Detect link type from URL.
     */
    public static function detectLinkType(string $url): ?string
    {
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        } elseif (str_contains($url, 'drive.google.com')) {
            return 'google_drive';
        }

        return null;
    }

    /**
     * Validate YouTube link.
     */
    public static function validateYouTubeLink(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false &&
               (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be'));
    }

    /**
     * Validate Google Drive link.
     */
    public static function validateDriveLink(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false &&
               str_contains($url, 'drive.google.com');
    }

    /**
     * Get category icon.
     */
    public static function getCategoryIcon(string $category): string
    {
        return match($category) {
            'brochure' => '📄',
            'floor_plans' => '📐',
            'layout_plan' => '🗺',
            'videos' => '🎥',
            'price_sheet' => '💰',
            'legal_approvals' => '📁',
            default => '📋',
        };
    }

    /**
     * Generate button HTML (for Blade views).
     */
    public static function generateButtonHtml(array $buttonData): string
    {
        $icon = $buttonData['icon'] ?? '📋';
        $category = $buttonData['category'] ?? 'other';
        $count = $buttonData['count'] ?? 0;
        $hasLatest = $buttonData['has_latest'] ?? false;
        $items = $buttonData['items'] ?? [];

        $buttonClass = $hasLatest ? 'btn-primary' : 'btn-secondary';
        $badge = $count > 1 ? " <span class='badge'>{$count}</span>" : '';
        $latestBadge = $hasLatest ? " <span class='badge badge-latest'>Latest</span>" : '';

        $html = "<button class='btn {$buttonClass} collateral-btn' data-category='{$category}'>";
        $html .= "{$icon} " . ucfirst(str_replace('_', ' ', $category));
        $html .= $badge . $latestBadge;
        $html .= "</button>";

        return $html;
    }
}
