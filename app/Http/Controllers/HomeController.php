<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Release;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $releases = Release::query()
            ->public()
            ->with(['coverAsset', 'artist'])
            ->withCount('tracks')
            ->latest('release_date')
            ->get();

        return view('home', compact('releases'));
    }
}
