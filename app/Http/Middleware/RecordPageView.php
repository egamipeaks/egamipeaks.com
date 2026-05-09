<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordPageView
{
    public function __construct(private AnalyticsRecorder $recorder) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->isSuccessful() && $this->isHtml($response)) {
            try {
                $this->recorder->recordPageView($request);
            } catch (\Throwable) {
                // Swallow analytics failures so they never break the request.
            }
        }

        return $response;
    }

    private function isHtml(Response $response): bool
    {
        $contentType = (string) $response->headers->get('Content-Type', '');

        if ($contentType === '') {
            return true;
        }

        return str_contains(strtolower($contentType), 'text/html');
    }
}
