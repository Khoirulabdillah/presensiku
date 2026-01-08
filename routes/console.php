<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ensure public/storage exists. Tries symlink first, falls back to copying files.
Artisan::command('storage:ensure', function () {
    $storage = storage_path('app/public');
    $public = public_path('storage');

    if (is_link($public) || file_exists($public)) {
        $this->info('public/storage already exists.');
        return;
    }

    // Try PHP symlink first (may be disabled on shared hosting)
    try {
        if (@symlink($storage, $public)) {
            $this->info('Symlink created: ' . $public . ' -> ' . $storage);
            return;
        }
    } catch (\Throwable $e) {
        // ignore and attempt fallback
    }

    // Fallback: copy directory contents into public/storage
    try {
        \Illuminate\Support\Facades\File::ensureDirectoryExists($public);
        if (\Illuminate\Support\Facades\File::copyDirectory($storage, $public)) {
            $this->info('Copied storage files to public/storage');
            return;
        }
    } catch (\Throwable $e) {
        $this->error('Fallback copy failed: ' . $e->getMessage());
        return;
    }

    $this->error('Failed to create public/storage. Create symlink manually via cPanel or enable symlink/php functions.');
})->purpose('Ensure public/storage exists (symlink or copy)');
