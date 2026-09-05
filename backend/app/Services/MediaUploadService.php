<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaUploadService
{
    private const CONTEXTS = ['products', 'brands', 'categories'];

    /**
     * Store an uploaded image on the public disk and return its public URL.
     */
    public function storeImage(UploadedFile $file, string $context = 'products'): string
    {
        abort_unless(in_array($context, self::CONTEXTS, true), 422, 'Invalid upload context.');

        $path = $file->store("images/{$context}", 'public');

        return Storage::disk('public')->url($path);
    }

    /**
     * Safely delete an image referenced by a public URL created with storeImage().
     */
    public function deleteImage(?string $url): void
    {
        if ($url === null || $url === '') {
            return;
        }

        $storageUrl = rtrim((string) config('filesystems.disks.public.url'), '/');

        $relative = null;

        if (str_starts_with($url, $storageUrl)) {
            $relative = ltrim(substr($url, strlen($storageUrl)), '/');
        } elseif (str_starts_with($url, '/storage/')) {
            $relative = ltrim(substr($url, strlen('/storage/')), '/');
        }

        if ($relative === null || !str_starts_with($relative, 'images/')) {
            return;
        }

        Storage::disk('public')->delete($relative);
    }
}