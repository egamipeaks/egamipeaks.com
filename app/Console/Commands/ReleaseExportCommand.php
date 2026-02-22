<?php

namespace App\Console\Commands;

use App\Models\Release;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ReleaseExportCommand extends Command
{
    protected $signature = 'release:export {release : Release ID or slug}';

    protected $description = 'Export a release and its tracks to a JSON file for production import';

    public function handle(): int
    {
        $identifier = $this->argument('release');

        $release = Release::query()
            ->with(['artist', 'coverAsset', 'tracks.audioAsset'])
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();

        if (! $release) {
            $this->error("Release not found: {$identifier}");

            return self::FAILURE;
        }

        $this->info("Exporting release: {$release->slug}");

        $assets = collect();

        if ($release->coverAsset) {
            $assets->push($release->coverAsset);
        }

        foreach ($release->tracks as $track) {
            if ($track->audioAsset) {
                $assets->push($track->audioAsset);
            }
        }

        $payload = [
            'artist' => $release->artist->only(['name', 'slug', 'bio', 'links']),
            'release' => $release->only([
                'title',
                'slug',
                'type',
                'release_date',
                'description',
                'credits',
                'visibility',
                'share_token',
            ]) + [
                'type' => $release->type->value,
                'visibility' => $release->visibility->value,
                'release_date' => $release->release_date?->toDateString(),
                'cover_asset_sha256' => $release->coverAsset?->sha256,
            ],
            'tracks' => $release->tracks->map(fn ($track) => [
                'title' => $track->title,
                'slug' => $track->slug,
                'position' => $track->position,
                'duration_seconds' => $track->duration_seconds,
                'lyrics' => $track->lyrics,
                'credits' => $track->credits,
                'audio_asset_sha256' => $track->audioAsset?->sha256,
            ])->toArray(),
            'assets' => $assets->unique('id')->map(fn ($asset) => $asset->only([
                'disk',
                'path',
                'mime',
                'bytes',
                'sha256',
            ]))->values()->toArray(),
        ];

        $outputPath = "exports/release-{$release->slug}.json";
        Storage::disk('local')->makeDirectory('exports');
        Storage::disk('local')->put($outputPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $fullPath = Storage::disk('local')->path($outputPath);
        $this->comment("Exported to: {$fullPath}");

        return self::SUCCESS;
    }
}
