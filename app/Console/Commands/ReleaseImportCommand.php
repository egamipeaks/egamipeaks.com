<?php

namespace App\Console\Commands;

use App\Enums\ReleaseType;
use App\Enums\Visibility;
use App\Models\Artist;
use App\Models\Asset;
use App\Models\Release;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseImportCommand extends Command
{
    protected $signature = 'release:import {file : Path to the release JSON export file}';

    protected $description = 'Import a release from a JSON export file (upserts records)';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $payload = json_decode(file_get_contents($file), true);

        if (! $payload || ! isset($payload['artist'], $payload['release'], $payload['tracks'], $payload['assets'])) {
            $this->error('Invalid export file format.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($payload): void {
            $this->info('Upserting assets...');
            $assetsBySha256 = [];

            foreach ($payload['assets'] as $assetData) {
                $asset = Asset::query()->updateOrCreate(
                    ['sha256' => $assetData['sha256']],
                    [
                        'disk' => $assetData['disk'],
                        'path' => $assetData['path'],
                        'mime' => $assetData['mime'],
                        'bytes' => $assetData['bytes'],
                    ]
                );

                $assetsBySha256[$asset->sha256] = $asset;
                $this->info("Asset upserted: {$asset->path}");
            }

            $this->info('Upserting artist...');
            $artistData = $payload['artist'];
            $artist = Artist::query()->updateOrCreate(
                ['slug' => $artistData['slug']],
                [
                    'name' => $artistData['name'],
                    'bio' => $artistData['bio'],
                    'links' => $artistData['links'],
                ]
            );
            $this->info("Artist upserted: {$artist->name}");

            $this->info('Upserting release...');
            $releaseData = $payload['release'];
            $coverAssetId = isset($releaseData['cover_asset_sha256'])
                ? ($assetsBySha256[$releaseData['cover_asset_sha256']]->id ?? null)
                : null;

            $release = Release::query()->updateOrCreate(
                ['slug' => $releaseData['slug']],
                [
                    'artist_id' => $artist->id,
                    'title' => $releaseData['title'],
                    'type' => ReleaseType::from($releaseData['type']),
                    'release_date' => $releaseData['release_date'],
                    'description' => $releaseData['description'],
                    'credits' => $releaseData['credits'],
                    'visibility' => Visibility::from($releaseData['visibility']),
                    'share_token' => $releaseData['share_token'],
                    'cover_asset_id' => $coverAssetId,
                ]
            );
            $this->info("Release upserted: {$release->slug}");

            $this->info('Upserting tracks...');

            foreach ($payload['tracks'] as $trackData) {
                $audioAssetId = isset($trackData['audio_asset_sha256'])
                    ? ($assetsBySha256[$trackData['audio_asset_sha256']]->id ?? null)
                    : null;

                $track = Track::query()->updateOrCreate(
                    ['release_id' => $release->id, 'slug' => $trackData['slug']],
                    [
                        'title' => $trackData['title'],
                        'position' => $trackData['position'],
                        'duration_seconds' => $trackData['duration_seconds'],
                        'lyrics' => $trackData['lyrics'],
                        'credits' => $trackData['credits'],
                        'audio_asset_id' => $audioAssetId,
                    ]
                );
                $this->info("Track upserted: {$track->title}");
            }
        });

        $this->newLine();
        $this->comment('Import complete.');

        return self::SUCCESS;
    }
}
