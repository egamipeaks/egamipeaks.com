<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use App\Models\Track;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TopTracksWidget extends Widget
{
    protected string $view = 'filament.widgets.top-tracks';

    protected int|string|array $columnSpan = 1;

    /**
     * @return Collection<int, object{title: string, artist: ?string, plays: int, uniques: int}>
     */
    public function getRows(): Collection
    {
        $start = now()->subDays(30)->startOfDay();

        $rows = PageView::query()
            ->selectRaw('subject_id, count(*) as plays, count(distinct visitor_hash) as uniques')
            ->where('event_type', PageView::EVENT_TRACK_PLAY)
            ->where('subject_type', (new Track)->getMorphClass())
            ->where('created_at', '>=', $start)
            ->groupBy('subject_id')
            ->orderByDesc('plays')
            ->limit(10)
            ->get();

        $tracks = Track::query()
            ->with('release.artist')
            ->whereIn('id', $rows->pluck('subject_id'))
            ->get()
            ->keyBy('id');

        return $rows->map(function ($row) use ($tracks) {
            $track = $tracks->get($row->subject_id);

            return (object) [
                'title' => $track?->title ?? '(deleted track)',
                'artist' => $track?->release?->artist?->name,
                'plays' => (int) $row->plays,
                'uniques' => (int) $row->uniques,
            ];
        });
    }
}
