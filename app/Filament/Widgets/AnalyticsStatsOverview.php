<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class AnalyticsStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Site analytics';

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $sevenDaysAgo = now()->subDays(7)->startOfDay();
        $thirtyDaysAgo = now()->subDays(30)->startOfDay();

        $viewsToday = $this->views()->where('created_at', '>=', $today)->count();
        $views7d = $this->views()->where('created_at', '>=', $sevenDaysAgo)->count();
        $views30d = $this->views()->where('created_at', '>=', $thirtyDaysAgo)->count();
        $uniques7d = (int) $this->views()
            ->where('created_at', '>=', $sevenDaysAgo)
            ->distinct()
            ->count('visitor_hash');
        $playsToday = $this->plays()->where('created_at', '>=', $today)->count();
        $plays7d = $this->plays()->where('created_at', '>=', $sevenDaysAgo)->count();

        return [
            Stat::make('Page views today', number_format($viewsToday))
                ->description('Last 7 days: '.number_format($views7d))
                ->chart($this->dailyChart(PageView::EVENT_PAGE_VIEW, 7))
                ->color('primary'),

            Stat::make('Unique visitors (7d)', number_format($uniques7d))
                ->description('30-day views: '.number_format($views30d))
                ->color('info'),

            Stat::make('Track plays today', number_format($playsToday))
                ->description('Last 7 days: '.number_format($plays7d))
                ->chart($this->dailyChart(PageView::EVENT_TRACK_PLAY, 7))
                ->color('success'),
        ];
    }

    private function views(): Builder
    {
        return PageView::query()->where('event_type', PageView::EVENT_PAGE_VIEW);
    }

    private function plays(): Builder
    {
        return PageView::query()->where('event_type', PageView::EVENT_TRACK_PLAY);
    }

    /**
     * @return array<int, int>
     */
    private function dailyChart(string $eventType, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = PageView::query()
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->where('event_type', $eventType)
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->pluck('total', 'day');

        $values = [];
        for ($i = 0; $i < $days; $i++) {
            $key = $start->copy()->addDays($i)->format('Y-m-d');
            $values[] = (int) ($rows[$key] ?? 0);
        }

        return $values;
    }
}
