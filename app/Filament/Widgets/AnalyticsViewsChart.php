<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\ChartWidget;

class AnalyticsViewsChart extends ChartWidget
{
    protected ?string $heading = 'Page views (last 30 days)';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 30);
        $start = now()->subDays($days - 1)->startOfDay();

        $views = $this->dailyTotals(PageView::EVENT_PAGE_VIEW, $start, $days);
        $uniques = $this->dailyUniques($start, $days);
        $plays = $this->dailyTotals(PageView::EVENT_TRACK_PLAY, $start, $days);

        $labels = [];
        for ($i = 0; $i < $days; $i++) {
            $labels[] = $start->copy()->addDays($i)->format('M j');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Page views',
                    'data' => $views,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.3,
                    'fill' => true,
                ],
                [
                    'label' => 'Unique visitors',
                    'data' => $uniques,
                    'borderColor' => '#1da0c3',
                    'backgroundColor' => 'rgba(29, 160, 195, 0.1)',
                    'tension' => 0.3,
                    'fill' => false,
                ],
                [
                    'label' => 'Track plays',
                    'data' => $plays,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.1)',
                    'tension' => 0.3,
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<int, int>
     */
    private function dailyTotals(string $eventType, \Carbon\Carbon $start, int $days): array
    {
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

    /**
     * @return array<int, int>
     */
    private function dailyUniques(\Carbon\Carbon $start, int $days): array
    {
        $rows = PageView::query()
            ->selectRaw('date(created_at) as day, count(distinct visitor_hash) as total')
            ->where('event_type', PageView::EVENT_PAGE_VIEW)
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
