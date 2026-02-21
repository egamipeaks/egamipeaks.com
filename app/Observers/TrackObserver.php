<?php

namespace App\Observers;

use App\Jobs\ExtractAudioDuration;
use App\Models\Track;

class TrackObserver
{
    public function created(Track $track): void
    {
        if ($track->audio_asset_id) {
            ExtractAudioDuration::dispatch($track);
        }
    }

    public function updated(Track $track): void
    {
        if ($track->wasChanged('audio_asset_id') && $track->audio_asset_id) {
            ExtractAudioDuration::dispatch($track);
        }
    }
}
