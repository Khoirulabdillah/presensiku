<?php
// Run this from project root: php scripts/ensure_storage_link.php

function rrmdir_copy($src, $dst) {
    if (!is_dir($src)) return false;
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $items = scandir($src);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $s = $src . DIRECTORY_SEPARATOR . $item;
        $d = $dst . DIRECTORY_SEPARATOR . $item;
        if (is_dir($s)) {
            rrmdir_copy($s, $d);
        } else {
            copy($s, $d);
        }
    }
    return true;
}

$projectRoot = dirname(__DIR__);
$storagePublic = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public';
$publicStorage = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'storage';

echo "storage_public: $storagePublic\n";
echo "public_storage: $publicStorage\n";

if (!is_dir($storagePublic)) {
    echo "Source storage path not found: $storagePublic\n";
    exit(1);
}

if (is_link($publicStorage) || is_dir($publicStorage)) {
    echo "public/storage already exists.\n";
    exit(0);
}

// Try PHP symlink first
if (function_exists('symlink')) {
    try {
        if (@symlink($storagePublic, $publicStorage)) {
            echo "Created symlink public/storage -> storage/app/public\n";
            exit(0);
        }
    } catch (Throwable $e) {
        // fallthrough to copy
    }
}

// Fallback: copy files
echo "Symlink not available; copying files to public/storage (may duplicate).\n";
if (rrmdir_copy($storagePublic, $publicStorage)) {
    echo "Copied storage/app/public -> public/storage\n";
    exit(0);
}

echo "Failed to create storage link or copy files.\n";
exit(1);
