<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AssetsExportImages extends Command
{
    protected $signature = 'assets:export-images
        {--output=exports/optimized-assets.json : Path within storage/app/private}';

    protected $description = 'Export image Asset rows (id/path/mime/bytes/sha256) for syncing to production';

    public function handle(): int
    {
        $assets = Asset::query()
            ->where('mime', 'like', 'image/%')
            ->orderBy('id')
            ->get(['id', 'disk', 'path', 'mime', 'bytes', 'sha256']);

        if ($assets->isEmpty()) {
            $this->info('No image assets found.');

            return self::SUCCESS;
        }

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'assets' => $assets->map(fn (Asset $asset) => [
                'id' => $asset->id,
                'disk' => $asset->disk,
                'path' => $asset->path,
                'mime' => $asset->mime,
                'bytes' => $asset->bytes,
                'sha256' => $asset->sha256,
            ])->values()->toArray(),
        ];

        $output = $this->option('output');
        Storage::disk('local')->makeDirectory(dirname($output));
        Storage::disk('local')->put($output, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Exported {$assets->count()} image asset(s) to: ".Storage::disk('local')->path($output));

        return self::SUCCESS;
    }
}
