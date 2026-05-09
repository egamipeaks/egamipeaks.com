<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssetsImportImageRecords extends Command
{
    protected $signature = 'assets:import-image-records
        {file : Path to the optimized-assets.json export file}
        {--dry-run : Show what would change without writing}';

    protected $description = 'Update Asset rows (path/mime/bytes/sha256) from a local export to repair production after a storage-only optimize run';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $payload = json_decode(file_get_contents($file), true);

        if (! is_array($payload) || ! isset($payload['assets']) || ! is_array($payload['assets'])) {
            $this->error('Invalid export file format.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $rows = $payload['assets'];
        $this->info('Importing '.count($rows).' asset record(s).'.($dryRun ? ' (dry-run)' : ''));

        $updated = 0;
        $alreadyOk = 0;
        $missing = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $dryRun, &$updated, &$alreadyOk, &$missing, &$skipped): void {
            foreach ($rows as $row) {
                $asset = Asset::query()->find($row['id']);

                if (! $asset) {
                    $this->warn("  asset #{$row['id']} not found, skipping");
                    $missing++;

                    continue;
                }

                if ($asset->sha256 === $row['sha256'] && $asset->path === $row['path']) {
                    $alreadyOk++;

                    continue;
                }

                if (! str_starts_with((string) $asset->mime, 'image/')) {
                    $this->warn("  asset #{$asset->id} is not an image (mime={$asset->mime}), skipping");
                    $skipped++;

                    continue;
                }

                $this->line("  asset #{$asset->id}: {$asset->path} -> {$row['path']}");

                if (! $dryRun) {
                    $asset->update([
                        'disk' => $row['disk'],
                        'path' => $row['path'],
                        'mime' => $row['mime'],
                        'bytes' => $row['bytes'],
                        'sha256' => $row['sha256'],
                    ]);
                }

                $updated++;
            }
        });

        $this->newLine();
        $this->info("Updated: {$updated}, already-ok: {$alreadyOk}, missing: {$missing}, skipped: {$skipped}");

        return self::SUCCESS;
    }
}
