<?php

namespace App\Services;

use App\Models\PageView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AnalyticsRecorder
{
    private const BOT_REGEX = '/bot|crawler|spider|crawling|preview|facebookexternalhit|slurp|lighthouse|headless|monitor|pingdom|uptime|curl|wget|httpie/i';

    public function recordPageView(Request $request, ?Model $subject = null): ?PageView
    {
        if ($this->shouldIgnore($request)) {
            return null;
        }

        return PageView::create([
            'event_type' => PageView::EVENT_PAGE_VIEW,
            'path' => Str::limit($request->path(), 2000, ''),
            'route_name' => Route::currentRouteName(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'referer' => $this->normalizeReferer($request),
            'visitor_hash' => $this->visitorHash($request),
        ]);
    }

    public function recordTrackPlay(Request $request, Model $track): ?PageView
    {
        if ($this->isBot($request)) {
            return null;
        }

        return PageView::create([
            'event_type' => PageView::EVENT_TRACK_PLAY,
            'path' => Str::limit($request->path(), 2000, ''),
            'route_name' => Route::currentRouteName(),
            'subject_type' => $track->getMorphClass(),
            'subject_id' => $track->getKey(),
            'referer' => $this->normalizeReferer($request),
            'visitor_hash' => $this->visitorHash($request),
        ]);
    }

    public function visitorHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip() ?? '',
            (string) $request->userAgent(),
            (string) config('app.key'),
            now()->format('Y-m-d'),
        ]));
    }

    public function shouldIgnore(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return true;
        }

        if ($this->isBot($request)) {
            return true;
        }

        $path = $request->path();

        if ($path === 'up') {
            return true;
        }

        $ignoredPrefixes = ['admin', 'livewire', 'storage', 'vendor', 'filament', 'api'];

        foreach ($ignoredPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, "{$prefix}/")) {
                return true;
            }
        }

        if (preg_match('/\.(css|js|map|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|eot|mp3|mp4|webm|json|xml|txt)$/i', $path) === 1) {
            return true;
        }

        return false;
    }

    private function isBot(Request $request): bool
    {
        $ua = (string) $request->userAgent();

        if ($ua === '') {
            return true;
        }

        return preg_match(self::BOT_REGEX, $ua) === 1;
    }

    private function normalizeReferer(Request $request): ?string
    {
        $referer = $request->headers->get('referer');

        if (! is_string($referer) || $referer === '') {
            return null;
        }

        $host = parse_url($referer, PHP_URL_HOST);

        if (is_string($host) && $host !== '' && $host === $request->getHost()) {
            return null;
        }

        return Str::limit($referer, 2000, '');
    }
}
