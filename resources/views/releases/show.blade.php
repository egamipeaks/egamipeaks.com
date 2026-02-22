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

    <script>
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
