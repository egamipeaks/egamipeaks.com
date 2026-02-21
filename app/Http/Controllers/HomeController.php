<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $artist = Artist::query()->with('heroImage')->firstOrFail();

        $recentReleases = $artist->releases()
            ->public()
            ->with('coverAsset')
            ->orderByDesc('release_date')
            ->limit(6)
            ->get();

        return view('home', compact('artist', 'recentReleases'));
    }
}
