<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class TrackHeartController extends Controller
{
    private const COOKIE_NAME = 'hearted_tracks';

    private const COOKIE_LIFETIME_MINUTES = 60 * 24 * 365 * 5;

    public function __invoke(Request $request, Track $track): JsonResponse
    {
        $hearted = $this->heartedIds($request);

        if (in_array($track->id, $hearted, true)) {
            return response()->json([
                'count' => $track->hearts_count,
                'hearted' => true,
            ]);
        }

        $track->increment('hearts_count');
        $hearted[] = $track->id;

        return response()
            ->json([
                'count' => $track->fresh()->hearts_count,
                'hearted' => true,
            ])
            ->withCookie(Cookie::create(
                name: self::COOKIE_NAME,
                value: implode(',', $hearted),
                expire: now()->addMinutes(self::COOKIE_LIFETIME_MINUTES)->getTimestamp(),
                sameSite: 'lax',
            ));
    }

    /**
     * @return array<int, int>
     */
    private function heartedIds(Request $request): array
    {
        $raw = $request->cookie(self::COOKIE_NAME, '');

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $raw))));
    }
}
