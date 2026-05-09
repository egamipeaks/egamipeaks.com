<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TopReferrersWidget extends Widget
{
    protected string $view = 'filament.widgets.top-referrers';

    protected int|string|array $columnSpan = 1;

    /**
     * @return Collection<int, object{host: string, views: int, uniques: int}>
     */
    public function getRows(): Collection
    {
        $start = now()->subDays(30)->startOfDay();

        $rows = PageView::query()
            ->select(['referer', 'visitor_hash'])
            ->where('event_type', PageView::EVENT_PAGE_VIEW)
            ->where('created_at', '>=', $start)
            ->whereNotNull('referer')
            ->get();

        $grouped = $rows
            ->groupBy(fn ($row) => parse_url((string) $row->referer, PHP_URL_HOST) ?: 'unknown')
            ->map(fn ($group, $host) => (object) [
                'host' => (string) $host,
                'views' => $group->count(),
                'uniques' => $group->pluck('visitor_hash')->unique()->count(),
            ])
            ->sortByDesc('views')
            ->values()
            ->take(10);

        return $grouped;
    }
}
