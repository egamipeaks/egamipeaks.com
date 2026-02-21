<?php

namespace App\Jobs;

use App\Models\Track;
use getID3;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExtractAudioDuration implements ShouldQueue
{
    use Queueable;

    public function __construct(public Track $track) {}

    public function handle(): void
    {
        if (! $this->track->audio_asset_id) {
            return;
        }

        $asset = $this->track->audioAsset;

        if (! $asset) {
            return;
        }

        $contents = Storage::disk($asset->disk)->get($asset->path);

        $tmpPath = sys_get_temp_dir().'/'.Str::uuid().'.audio';

        file_put_contents($tmpPath, $contents);

        try {
            $info = (new getID3)->analyze($tmpPath);
            $seconds = $info['playtime_seconds'] ?? null;

            if ($seconds !== null) {
                $this->track->update(['duration_seconds' => (int) $seconds]);
            }
        } finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }
}
