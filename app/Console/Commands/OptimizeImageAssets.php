<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Asset;
use App\Models\Release;
use App\Services\AssetUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OptimizeImageAssets extends Command
{
    protected $signature = 'assets:optimize-images
        {--dry-run : Show what would change without writing}
        {--force : Re-optimize images even if already image/webp}';

    protected $description = 'Re-encode existing image assets to optimized WebP and update their records.';

    public function handle(AssetUploadService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $query = Asset::query()->where('mime', 'like', 'image/%');

        if (! $force) {
            $query->where('mime', '!=', 'image/webp');
        }

        $assets = $query->get();

        if ($assets->isEmpty()) {
            $this->info('No image assets to optimize.');

            return self::SUCCESS;
        }

        $this->info("Found {$assets->count()} image asset(s) to process.".($dryRun ? ' (dry-run)' : ''));

        $bytesBefore = 0;
        $bytesAfter = 0;
        $optimized = 0;
        $skipped = 0;
        $merged = 0;
        $failed = 0;

        foreach ($assets as $asset) {
            $this->line("Asset #{$asset->id} ({$asset->path}, {$this->formatBytes($asset->bytes)})");

            try {
                $disk = Storage::disk($asset->disk);

                if (! $disk->exists($asset->path)) {
                    $this->warn('  missing file, skipping');
                    $skipped++;

                    continue;
                }

                $original = $disk->get($asset->path);
                $optimizedContents = $service->optimizeImage($original);
                $newSha = hash('sha256', $optimizedContents);
                $newBytes = strlen($optimizedContents);

                $bytesBefore += $asset->bytes;
                $bytesAfter += $newBytes;

                if ($newSha === $asset->sha256) {
                    $this->line('  already optimal, skipping');
                    $skipped++;

                    continue;
                }

                $duplicate = Asset::query()
                    ->where('sha256', $newSha)
                    ->where('id', '!=', $asset->id)
                    ->first();

                if ($duplicate) {
                    $this->line("  matches existing asset #{$duplicate->id}, repointing references");

                    if (! $dryRun) {
                        $this->repointReferences($asset->id, $duplicate->id);
                        $disk->delete($asset->path);
                        $asset->delete();
                    }

                    $merged++;

                    continue;
                }

                $newPath = 'uploads/'.now()->format('Y-m').'/'.Str::uuid().'.webp';

                $this->line("  {$this->formatBytes($asset->bytes)} -> {$this->formatBytes($newBytes)} (".$this->percentChange($asset->bytes, $newBytes).')');

                if (! $dryRun) {
                    $disk->put($newPath, $optimizedContents, ['visibility' => 'public']);
                    $oldPath = $asset->path;

                    $asset->update([
                        'path' => $newPath,
                        'mime' => 'image/webp',
                        'bytes' => $newBytes,
                        'sha256' => $newSha,
                    ]);

                    $disk->delete($oldPath);
                }

                $optimized++;
            } catch (\Throwable $e) {
                $this->error("  failed: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Optimized: {$optimized}, merged: {$merged}, skipped: {$skipped}, failed: {$failed}");
        $this->info("Total: {$this->formatBytes($bytesBefore)} -> {$this->formatBytes($bytesAfter)} (".$this->percentChange($bytesBefore, $bytesAfter).')');

        return self::SUCCESS;
    }

    private function repointReferences(int $oldAssetId, int $newAssetId): void
    {
        DB::transaction(function () use ($oldAssetId, $newAssetId): void {
            Release::query()->where('cover_asset_id', $oldAssetId)->update(['cover_asset_id' => $newAssetId]);
            Artist::query()->where('hero_image_asset_id', $oldAssetId)->update(['hero_image_asset_id' => $newAssetId]);
        });
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    private function percentChange(int $before, int $after): string
    {
        if ($before === 0) {
            return '0%';
        }

        $pct = round((($after - $before) / $before) * 100, 1);

        return ($pct > 0 ? '+' : '').$pct.'%';
    }
}
