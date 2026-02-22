@extends('layouts.app')

@section('title', $release->title)

@section('content')
    @if($isPreview)
        <div class="bg-amber-100 border-b-2 border-amber-600 px-6 py-3">
            <div class="max-w-6xl mx-auto text-sm text-amber-800 text-center">
                You're viewing a private preview of this release.
            </div>
        </div>
    @endif

    <div class="max-w-6xl mx-auto px-6 py-16 space-y-10">
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
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-[#1a1a1a]">
                            @foreach($release->tracks as $track)
                                <tr class="odd:bg-white even:bg-[#f5f3f0]">
                                    <td class="px-4 py-3 text-[#6b6b6b] text-right w-8 tabular-nums">
                                        {{ $track->position }}
                                    </td>
                                    <td class="px-4 py-3 text-[#1a1a1a] font-medium">
                                        {{ $track->title }}
                                    </td>
                                    <td class="px-4 py-3 text-[#6b6b6b] text-right tabular-nums">
                                        {{ $track->formatted_duration ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 w-96">
                                        @if($track->audioAsset?->url)
                                            <audio controls class="h-7 w-full" preload="none">
                                                <source src="{{ $track->audioAsset->url }}" type="{{ $track->audioAsset->mime }}">
                                            </audio>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
@endsection
