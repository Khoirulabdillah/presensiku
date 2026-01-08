<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    /**
     * Serve a file from storage/app/public when public/storage symlink is missing.
     */
    public function image(Request $request, $path)
    {
        $clean = ltrim($path, '/');
        $full = storage_path('app/public/' . $clean);

        if (! file_exists($full)) {
            abort(404);
        }

        $mime = Storage::disk('public')->mimeType($clean) ?? mime_content_type($full);

        return response()->file($full, ['Content-Type' => $mime]);
    }

    /**
     * Return storage status useful for debugging hosting symlink issues.
     */
    public function status(Request $request)
    {
        $publicStorage = public_path('storage');
        $storagePublic = storage_path('app/public');

        $response = [
            'public_storage_path' => $publicStorage,
            'public_storage_exists' => file_exists($publicStorage),
            'public_storage_is_link' => is_link($publicStorage),
            'storage_app_public' => $storagePublic,
            'storage_app_public_exists' => file_exists($storagePublic),
            'disk_public_root_exists' => \Illuminate\Support\Facades\Storage::disk('public')->exists(''),
        ];

        return response()->json($response);
    }
}
