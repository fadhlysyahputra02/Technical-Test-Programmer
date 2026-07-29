<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Query logging for local environment if execution time exceeds 500ms
        if ($this->app->environment('local')) {
            DB::listen(function ($query) {
                if ($query->time > 500) {
                    Log::warning("Slow query detected: [{$query->time}ms] {$query->sql}", [
                        'bindings' => $query->bindings,
                        'connection' => $query->connectionName,
                    ]);
                }
            });
        }
    }
}
