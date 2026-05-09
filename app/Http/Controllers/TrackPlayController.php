<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Services\AnalyticsRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrackPlayController extends Controller
{
    public function __invoke(Request $request, Track $track, AnalyticsRecorder $recorder): Response
    {
        $recorder->recordTrackPlay($request, $track);

        return response()->noContent();
    }
}
