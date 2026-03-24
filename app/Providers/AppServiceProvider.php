<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Task;
use App\Models\PricingConfig;
use App\Models\UnitType;
use App\Observers\UserObserver;
use App\Observers\TaskObserver;
use App\Observers\PricingConfigObserver;
use App\Observers\UnitTypeObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register User Observer for manager change detection
        User::observe(UserObserver::class);

        // Register Task Observer for activity logging
        Task::observe(TaskObserver::class);

        // Register Pricing and Unit Type Observers
        PricingConfig::observe(PricingConfigObserver::class);
        UnitType::observe(UnitTypeObserver::class);

        // Ensure PHP upload temp dir is writable (prevents Request::Startup warnings)
        $uploadTmpDir = storage_path('app/tmp');
        if (!is_dir($uploadTmpDir)) {
            File::ensureDirectoryExists($uploadTmpDir);
        }
        if (is_dir($uploadTmpDir) && is_writable($uploadTmpDir)) {
            ini_set('upload_tmp_dir', $uploadTmpDir);
        }
    }
}
