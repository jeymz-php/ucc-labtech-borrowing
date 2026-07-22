<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (
                Schema::hasTable('settings')
                && Schema::hasColumn('settings', 'key')
            ) {
                $appName = Setting::getValue('app_name');
                $timezone = Setting::getValue('timezone');

                if (is_string($appName) && $appName !== '') {
                    config(['app.name' => $appName]);
                }

                if (
                    is_string($timezone)
                    && in_array($timezone, timezone_identifiers_list(), true)
                ) {
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
