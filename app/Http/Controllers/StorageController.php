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
}
