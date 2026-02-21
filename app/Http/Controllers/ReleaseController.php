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

        return view('releases.show', compact('release', 'isPreview'));
    }
}
