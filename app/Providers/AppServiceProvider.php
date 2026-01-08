<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\NoExecFilesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind a Filesystem implementation that avoids calling exec(), which may be disabled on shared hosting.
        $this->app->singleton('files', function ($app) {
            return new NoExecFilesystem();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
