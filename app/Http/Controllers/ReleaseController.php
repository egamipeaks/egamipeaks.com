<?php

namespace App\Http\Controllers;

use App\Enums\Visibility;
use App\Models\Release;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReleaseController extends Controller
{
    public function __invoke(Request $request, string $slug): View
    {
        $release = Release::query()
            ->where('slug', $slug)
            ->with([
                'coverAsset',
                'artist',
                'tags',
                'tracks' => fn ($q) => $q->with('audioAsset')->orderBy('position'),
            ])
            ->firstOrFail();

        $isPublic = $release->visibility === Visibility::Public;
        $hasValidToken = $request->query('token') === $release->share_token;

        if (! $isPublic && ! $hasValidToken) {
            abort(404);
        }

        $isPreview = ! $isPublic && $hasValidToken;

        $heartedTrackIds = collect(explode(',', (string) $request->cookie('hearted_tracks', '')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $siblingReleases = Release::query()
            ->public()
            ->latest('release_date')
            ->get(['id', 'slug', 'title', 'release_date']);

        $currentIndex = $siblingReleases->search(fn (Release $r) => $r->id === $release->id);
        $newerRelease = $currentIndex !== false && $currentIndex > 0
            ? $siblingReleases[$currentIndex - 1]
            : null;
        $olderRelease = $currentIndex !== false && $currentIndex < $siblingReleases->count() - 1
            ? $siblingReleases[$currentIndex + 1]
            : null;

        return view('releases.show', compact(
            'release',
            'isPreview',
            'heartedTrackIds',
            'newerRelease',
            'olderRelease',
        ));
    }
}
