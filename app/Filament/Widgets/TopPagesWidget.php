<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TopPagesWidget extends Widget
{
    protected string $view = 'filament.widgets.top-pages';

    protected int|string|array $columnSpan = 1;

    /**
     * @return Collection<int, object{path: string, route_name: ?string, views: int, uniques: int}>
     */
    public function getRows(): Collection
    {
        $start = now()->subDays(30)->startOfDay();

        return PageView::query()
            ->selectRaw('path, route_name, count(*) as views, count(distinct visitor_hash) as uniques')
            ->where('event_type', PageView::EVENT_PAGE_VIEW)
            ->where('created_at', '>=', $start)
            ->groupBy('path', 'route_name')
            ->orderByDesc('views')
            ->limit(10)
            ->get();
    }
}
