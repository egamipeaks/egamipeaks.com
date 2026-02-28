<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $artist = Artist::query()->with('heroImage')->firstOrFail();

        $releases = $artist->releases()
            ->public()
            ->with('coverAsset')
            ->latest('release_date')
            ->get();

        return view('home', compact('artist', 'releases'));
    }
}
