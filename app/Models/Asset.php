<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'mime',
        'bytes',
        'sha256',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function getUrlAttribute(): ?string
    {
        $cdnUrl = config("filesystems.disks.{$this->disk}.cdn_url");

        if ($cdnUrl) {
            return rtrim($cdnUrl, '/').'/'.ltrim($this->path, '/');
        }

        try {
            return Storage::disk($this->disk)->temporaryUrl($this->path, now()->addHour());
        } catch (\Exception) {
            return null;
        }
    }
}
