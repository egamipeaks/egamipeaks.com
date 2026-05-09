<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AssetUploadService
{
    public const IMAGE_MAX_EDGE = 1600;

    public const IMAGE_QUALITY = 82;

    public function upload(TemporaryUploadedFile $file, string $disk = 'spaces'): Asset
    {
        return $this->store(
            file_get_contents($file->getRealPath()),
            $file->getMimeType(),
            $file->getClientOriginalExtension(),
            $disk,
        );
    }

    public function uploadFromPath(string $localPath, string $mime, string $disk = 'spaces'): Asset
    {
        return $this->store(
            file_get_contents($localPath),
            $mime,
            pathinfo($localPath, PATHINFO_EXTENSION),
            $disk,
        );
    }

    /**
     * Re-encode an image binary to WebP at the configured size and quality.
     */
    public function optimizeImage(string $contents): string
    {
        $manager = new ImageManager(new ImagickDriver);
        $image = $manager->decode($contents);

        $image->scaleDown(width: self::IMAGE_MAX_EDGE, height: self::IMAGE_MAX_EDGE);

        return (string) $image->encode(new WebpEncoder(quality: self::IMAGE_QUALITY));
    }

    private function store(string $contents, ?string $mime, string $extension, string $disk): Asset
    {
        if ($this->isOptimizableImage($mime)) {
            $contents = $this->optimizeImage($contents);
            $mime = 'image/webp';
            $extension = 'webp';
        }

        $sha256 = hash('sha256', $contents);

        $existing = Asset::query()->where('sha256', $sha256)->first();

        if ($existing) {
            return $existing;
        }

        $path = 'uploads/'.now()->format('Y-m').'/'.Str::uuid().'.'.$extension;

        Storage::disk($disk)->put($path, $contents, ['visibility' => 'public']);

        return Asset::create([
            'disk' => $disk,
            'path' => $path,
            'mime' => $mime,
            'bytes' => strlen($contents),
            'sha256' => $sha256,
        ]);
    }

    private function isOptimizableImage(?string $mime): bool
    {
        if ($mime === null) {
            return false;
        }

        return in_array($mime, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff',
            'image/webp',
        ], true);
    }
}
