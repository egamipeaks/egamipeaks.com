<?php

namespace App\Providers;

use App\Models\Track;
use App\Observers\TrackObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Track::observe(TrackObserver::class);
    }
}
