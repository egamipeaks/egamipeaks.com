<?php

namespace App\Console\Commands;

use App\Enums\ReleaseType;
use App\Enums\Visibility;
use App\Models\Artist;
use App\Models\Release;
use App\Models\Track;
use App\Services\AssetUploadService;
use getID3;
use getid3_lib;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportReleaseCommand extends Command
{
    protected $signature = 'import-release {path : Path to folder containing MP3 files}';

    protected $description = 'Import a folder of MP3s as a new Release with Tracks';

    public function handle(AssetUploadService $uploadService): int
    {
        $path = rtrim($this->argument('path'), '/');

        if (! is_dir($path)) {
            $this->error("Directory not found: {$path}");

            return self::FAILURE;
        }

        $files = glob("{$path}/*.mp3");

        if (empty($files)) {
            $this->error("No MP3 files found in: {$path}");

            return self::FAILURE;
        }

        sort($files);

        $this->info('Found '.count($files).' MP3 file(s). Reading ID3 tags...');

        $id3 = new getID3;
        $trackData = [];
        $coverPicture = null;

        foreach ($files as $file) {
            $info = $id3->analyze($file);
            getid3_lib::CopyTagsToComments($info);

            $comments = $info['comments'] ?? [];
            $title = $comments['title'][0] ?? pathinfo($file, PATHINFO_FILENAME);
            $trackNumber = (int) ($comments['track_number'][0] ?? 0);
            $duration = (int) round($info['playtime_seconds'] ?? 0);

            if (! $coverPicture && ! empty($comments['picture'][0])) {
                $coverPicture = $comments['picture'][0];
            }

            $trackData[] = [
                'file' => $file,
                'title' => $title,
                'track_number' => $trackNumber,
                'duration' => $duration,
            ];
        }

        usort($trackData, fn ($a, $b) => $a['track_number'] <=> $b['track_number'] ?: strnatcmp($a['file'], $b['file']));

        $albumTitle = $this->ask('Release title?', basename($path));

        $releaseDateInput = $this->ask('Release date? (YYYY-MM-DD, leave blank to skip)');
        $releaseDate = null;

        if ($releaseDateInput) {
            $parsed = date_create_from_format('Y-m-d', $releaseDateInput);

            if (! $parsed || $parsed->format('Y-m-d') !== $releaseDateInput) {
                $this->error("Invalid date format: {$releaseDateInput}. Expected YYYY-MM-DD.");

                return self::FAILURE;
            }

            $releaseDate = $releaseDateInput;
        }

        $artist = $this->resolveArtist();

        if (! $artist) {
            $this->error('No artist selected. Aborting.');

            return self::FAILURE;
        }

        $trackCount = count($trackData);
        $releaseType = match (true) {
            $trackCount === 1 => ReleaseType::Single,
            $trackCount <= 6 => ReleaseType::EP,
            default => ReleaseType::Album,
        };

        $release = Release::create([
            'artist_id' => $artist->id,
            'title' => $albumTitle,
            'type' => $releaseType,
            'visibility' => Visibility::Draft,
            'release_date' => $releaseDate,
        ]);

        $this->info("Created release: {$release->slug} (type: {$releaseType->getLabel()})");

        if ($coverPicture) {
            $this->info('Uploading cover art...');
            $coverPath = tempnam(sys_get_temp_dir(), 'cover_').'.'.(Str::after($coverPicture['image_mime'] ?? 'image/jpeg', '/'));
            file_put_contents($coverPath, $coverPicture['data']);

            $coverAsset = $uploadService->uploadFromPath($coverPath, $coverPicture['image_mime'] ?? 'image/jpeg');
            unlink($coverPath);

            $release->update(['cover_asset_id' => $coverAsset->id]);
            $this->info('Cover art uploaded.');
        }

        foreach ($trackData as $index => $track) {
            $position = $track['track_number'] > 0 ? $track['track_number'] : ($index + 1);
            $this->info("Processing track {$position}: {$track['title']}...");

            $audioAsset = $uploadService->uploadFromPath($track['file'], 'audio/mpeg');

            Track::create([
                'release_id' => $release->id,
                'title' => $track['title'],
                'position' => $position,
                'duration_seconds' => $track['duration'],
                'audio_asset_id' => $audioAsset->id,
            ]);
        }

        $this->newLine();
        $this->comment("Successfully imported {$trackCount} track(s) into release: {$release->slug}");

        return self::SUCCESS;
    }

    private function resolveArtist(): ?Artist
    {
        $artists = Artist::query()->orderBy('name')->get();

        if ($artists->isEmpty()) {
            $name = $this->ask('Artist name?');

            if (! $name) {
                return null;
            }

            $artist = Artist::create(['name' => $name]);
            $this->info("Created artist: {$artist->name}");

            return $artist;
        }

        $choices = $artists->pluck('name')->toArray();
        $choices[] = '+ Create new artist';

        $choice = $this->choice('Select artist:', $choices);

        if ($choice === '+ Create new artist') {
            $name = $this->ask('Artist name?');

            if (! $name) {
                return null;
            }

            $artist = Artist::create(['name' => $name]);
            $this->info("Created artist: {$artist->name}");

            return $artist;
        }

        $artist = $artists->firstWhere('name', $choice);
        $this->info("Using artist: {$artist->name}");

        return $artist;
    }
}
