<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\NoExecFilesystem;
use Illuminate\Support\Facades\Config;

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
        // Detect whether the app is running inside Docker. Set by docker-compose (DOCKER=1).
        $isDocker = (bool) env('DOCKER', false);

        if ($isDocker) {
            // Running locally in Docker: keep drivers that assume external services
            // (e.g. redis, database) are configured via .env/docker-compose.
            return;
        }

        // Running on shared hosting / cPanel: prefer sync/file drivers and avoid
        // background workers and remote mounts which are typically unavailable.
        Config::set('queue.default', env('QUEUE_DRIVER', 'sync'));
        Config::set('cache.default', env('CACHE_DRIVER', 'file'));
        Config::set('session.driver', env('SESSION_DRIVER', 'file'));
        Config::set('filesystems.default', env('FILESYSTEM_DRIVER', 'local'));
    }
}
