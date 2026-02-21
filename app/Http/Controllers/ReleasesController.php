<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Release;
use Illuminate\Contracts\View\View;

class ReleasesController extends Controller
{
    public function __invoke(): View
    {
        $artist = Artist::query()->firstOrFail();

        $releases = Release::query()
            ->public()
            ->with('coverAsset')
            ->orderByDesc('release_date')
            ->paginate(12);

        return view('releases.index', compact('artist', 'releases'));
    }
}
