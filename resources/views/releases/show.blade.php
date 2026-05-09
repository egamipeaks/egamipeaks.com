@extends('layouts.app')

@section('title', $release->title)

@section('content')
    @if($isPreview)
        <div class="bg-amber-100 border-b-2 border-amber-600 py-3">
            <div class="max-w-6xl mx-auto text-sm text-amber-800 text-center">
                You're viewing a private preview of this release.
            </div>
        </div>
    @endif

    <div class="px-6">
    <div class="max-w-6xl mx-auto py-16 pb-32 space-y-10">
        {{-- Release navigation --}}
        @if($olderRelease || $newerRelease)
            <nav class="flex items-center justify-between gap-4 text-sm" aria-label="Release navigation">
                @if($olderRelease)
                    <a href="{{ route('releases.show', $olderRelease->slug) }}"
                       class="inline-flex items-center gap-2 text-xs uppercase tracking-wider text-[#6b6b6b] hover:text-[#1a1a1a] transition-colors">
                        <span>&larr;</span>
                        <span>Older</span>
                    </a>
                @else
                    <span></span>
                @endif

                @if($newerRelease)
                    <a href="{{ route('releases.show', $newerRelease->slug) }}"
                       class="inline-flex items-center gap-2 text-xs uppercase tracking-wider text-[#6b6b6b] hover:text-[#1a1a1a] transition-colors">
                        <span>Newer</span>
                        <span>&rarr;</span>
                    </a>
                @else
                    <span></span>
                @endif
            </nav>
        @endif

        {{-- Header: title left, cover art right --}}
        <div class="flex items-start gap-8">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-xs font-medium px-2.5 py-1 border border-[#1a1a1a] text-[#1a1a1a]">
                        {{ $release->type->getLabel() }}
                    </span>
                    @if($release->formatted_release_date)
                        <span class="text-sm text-[#6b6b6b]">{{ $release->formatted_release_date }}</span>
                    @endif
                </div>
                <h1 class="text-4xl font-bold text-[#1a1a1a] mb-2">{{ $release->title }}</h1>
                <p class="text-[#6b6b6b]">{{ $release->artist->name }}</p>

                @if($release->description)
                    <div class="text-[#1a1a1a] leading-relaxed mt-6">
                        {!! nl2br(e($release->description)) !!}
                    </div>
                @endif

                @if($release->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mt-6">
                        @foreach($release->tags as $tag)
                            <span class="text-xs px-3 py-1 border border-[#1a1a1a] text-[#1a1a1a]">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Cover Art --}}
            @if($release->coverAsset?->url)
                <div class="w-40 shrink-0">
                    <div class="aspect-square overflow-hidden border-2 border-[#1a1a1a]">
                        <img src="{{ $release->coverAsset->url }}"
                             alt="{{ $release->title }}"
                             class="w-full h-full object-cover">
                    </div>
                </div>
            @endif
        </div>

        {{-- Tracklist --}}
        @if($release->tracks->isNotEmpty())
            <div>
                <h2 class="text-lg font-bold text-[#1a1a1a] mb-4 uppercase tracking-wider">Tracklist</h2>
                <div class="border-2 border-[#1a1a1a] overflow-hidden">
                    <table class="w-full text-sm" id="tracklist">
                        <tbody class="divide-y divide-[#1a1a1a]">
                            @foreach($release->tracks as $index => $track)
                                <tr class="odd:bg-white even:bg-[#f5f3f0] transition-colors"
                                    @if($track->audioAsset?->url)
                                        data-audio-url="{{ $track->audioAsset->url }}"
                                        data-audio-mime="{{ $track->audioAsset->mime }}"
                                        data-track-title="{{ $track->title }}"
                                        data-track-index="{{ $index }}"
                                    @endif
                                >
                                    <td class="px-4 py-3 w-8 tabular-nums">
                                        @if($track->audioAsset?->url)
                                            <span class="track-position text-[#6b6b6b] text-right block">{{ $track->position }}</span>
                                        @else
                                            <span class="text-[#6b6b6b] text-right block">{{ $track->position }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-[#1a1a1a] font-medium">
                                        {{ $track->title }}
                                    </td>
                                    <td class="px-4 py-3 text-[#6b6b6b] text-right tabular-nums">
                                        {{ $track->formatted_duration ?? '—' }}
                                    </td>
                                    <td class="pr-4 py-3 w-28 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            @if($track->is_highlighted)
                                                <span class="text-[#e8590c]" title="Hot pick" aria-label="Hot pick">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M13.5 0.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14a8 8 0 1 0 16 0C20 9.9 18.04 6.24 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                            <button
                                                type="button"
                                                x-data="trackHeart({{ $track->id }}, {{ $track->hearts_count }}, {{ in_array($track->id, $heartedTrackIds, true) ? 'true' : 'false' }})"
                                                x-on:click.stop="toggle()"
                                                :disabled="hearted || loading"
                                                :class="hearted ? 'text-[#1da0c3]' : 'text-[#6b6b6b] hover:text-[#1a1a1a]'"
                                                class="inline-flex items-center gap-1 text-xs tabular-nums transition-colors disabled:cursor-default"
                                                :aria-pressed="hearted"
                                                aria-label="Heart this track"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" :fill="hearted ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.5-9.5-9A5.5 5.5 0 0 1 12 6a5.5 5.5 0 0 1 9.5 6c-2.5 4.5-9.5 9-9.5 9Z"/>
                                                </svg>
                                                <span x-text="count"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <audio id="player" preload="none"></audio>
            </div>
        @endif

        {{-- Credits --}}
        @if($release->credits)
            <div>
                <h2 class="text-lg font-bold text-[#1a1a1a] mb-3 uppercase tracking-wider">Credits</h2>
                <p class="text-[#6b6b6b] text-sm leading-relaxed">{{ $release->credits }}</p>
            </div>
        @endif

        <div class="pt-10 border-t-2 border-[#1a1a1a]">
            <h2 class="text-lg font-bold text-[#1a1a1a] mb-3 uppercase tracking-wider">Get notified</h2>
            <p class="text-sm text-[#6b6b6b] mb-4">Subscribe to be the first to hear new releases.</p>
            <livewire:subscribe-form />
        </div>

        <div class="pt-10 border-t-2 border-[#1a1a1a]">
            <h2 class="text-lg font-bold text-[#1a1a1a] mb-6 uppercase tracking-wider">Comments</h2>
            <x-commenter::index :model="$release" />
        </div>
    </div>

    {{-- Fixed Player Bar --}}
    <div id="player-bar" class="fixed bottom-0 left-0 right-0 border-t-2 border-[#1a1a1a] bg-white px-6 py-3 z-50">
        <div class="max-w-6xl mx-auto flex items-center gap-4">
            <button id="player-toggle" class="w-8 h-8 flex items-center justify-center border-2 border-[#1a1a1a] hover:bg-[#1da0c3] hover:border-[#1da0c3] hover:text-white transition-colors shrink-0">
                <svg id="icon-play" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <svg id="icon-pause" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 hidden" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                </svg>
            </button>
            <span id="player-title" class="text-sm font-bold text-[#1a1a1a] truncate w-48 shrink-0">— select a track —</span>
            <span id="player-current" class="text-xs text-[#6b6b6b] tabular-nums shrink-0">0:00</span>
            <input id="player-seek" type="range" min="0" max="100" value="0"
                   class="flex-1 accent-[#1da0c3] cursor-pointer">
            <span id="player-duration" class="text-xs text-[#6b6b6b] tabular-nums shrink-0">0:00</span>
            <div class="flex items-center gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-[#6b6b6b]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
                </svg>
                <input id="player-volume" type="range" min="0" max="100" value="100"
                       class="w-20 accent-[#1da0c3] cursor-pointer">
            </div>
        </div>
    </div>
    </div>

    <script>
        window.trackHeart = function (id, initialCount, initialHearted) {
            return {
                count: initialCount,
                hearted: initialHearted,
                loading: false,
                async toggle() {
                    if (this.hearted || this.loading) {
                        return;
                    }
                    this.loading = true;
                    try {
                        const res = await fetch(`/tracks/${id}/heart`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        if (!res.ok) {
                            return;
                        }
                        const data = await res.json();
                        this.count = data.count;
                        this.hearted = data.hearted;
                    } finally {
                        this.loading = false;
                    }
                },
            };
        };

        const player = document.getElementById('player');
        const playerBar = document.getElementById('player-bar');
        const playerToggle = document.getElementById('player-toggle');
        const playerTitle = document.getElementById('player-title');
        const playerCurrent = document.getElementById('player-current');
        const playerDuration = document.getElementById('player-duration');
        const playerSeek = document.getElementById('player-seek');
        const playerVolume = document.getElementById('player-volume');
        const iconPlay = document.getElementById('icon-play');
        const iconPause = document.getElementById('icon-pause');

        let activeRow = null;

        function formatTime(s) {
            if (!s || isNaN(s)) return '0:00';
            const m = Math.floor(s / 60);
            const sec = String(Math.floor(s % 60)).padStart(2, '0');
            return `${m}:${sec}`;
        }

        function setPlaying(isPlaying) {
            iconPlay.classList.toggle('hidden', isPlaying);
            iconPause.classList.toggle('hidden', !isPlaying);
        }

        function activateRow(row) {
            if (activeRow) {
                activeRow.classList.remove('bg-[#1da0c3]/10', '!bg-[#ddf4fb]');
                const pos = activeRow.querySelector('.track-position');
                if (pos) pos.innerHTML = pos.dataset.position;
            }
            activeRow = row;
            row.classList.add('!bg-[#ddf4fb]');
            const pos = row.querySelector('.track-position');
            if (pos) {
                pos.dataset.position = pos.textContent.trim();
                pos.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 inline text-[#1da0c3]" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>`;
            }
        }

        document.querySelectorAll('#tracklist tr[data-audio-url]').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', () => {
                const url = row.dataset.audioUrl;
                const mime = row.dataset.audioMime;
                const title = row.dataset.trackTitle;

                if (activeRow === row) {
                    player.paused ? player.play() : player.pause();
                    return;
                }

                player.src = url;
                player.load();
                player.play();

                playerTitle.textContent = title;
                activateRow(row);
            });
        });

        playerToggle.addEventListener('click', () => {
            player.paused ? player.play() : player.pause();
        });

        playerSeek.addEventListener('input', () => {
            if (player.duration) {
                player.currentTime = (playerSeek.value / 100) * player.duration;
            }
        });

        playerVolume.addEventListener('input', () => {
            player.volume = playerVolume.value / 100;
        });

        player.addEventListener('play', () => setPlaying(true));
        player.addEventListener('pause', () => setPlaying(false));
        player.addEventListener('ended', () => {
            setPlaying(false);
            if (activeRow) {
                const pos = activeRow.querySelector('.track-position');
                if (pos && pos.dataset.position) {
                    pos.innerHTML = pos.dataset.position;
                }
                activeRow = null;
            }
        });
        player.addEventListener('timeupdate', () => {
            playerCurrent.textContent = formatTime(player.currentTime);
            if (player.duration) {
                playerSeek.value = (player.currentTime / player.duration) * 100;
            }
        });
        player.addEventListener('loadedmetadata', () => {
            playerDuration.textContent = formatTime(player.duration);
        });
    </script>
@endsection
