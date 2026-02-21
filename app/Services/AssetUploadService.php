<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AssetUploadService
{
    public function upload(TemporaryUploadedFile $file, string $disk = 'spaces'): Asset
    {
        $contents = file_get_contents($file->getRealPath());
        $sha256 = hash('sha256', $contents);

        $existing = Asset::query()->where('sha256', $sha256)->first();

        if ($existing) {
            return $existing;
        }

        $extension = $file->getClientOriginalExtension();
        $path = 'uploads/'.now()->format('Y-m').'/'.\Illuminate\Support\Str::uuid().'.'.$extension;

        Storage::disk($disk)->put($path, $contents);

        return Asset::create([
            'disk' => $disk,
            'path' => $path,
            'mime' => $file->getMimeType(),
            'bytes' => strlen($contents),
            'sha256' => $sha256,
        ]);
    }
}
