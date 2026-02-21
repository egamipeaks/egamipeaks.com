@extends('layouts.app')

@section('title', $release->title)

@section('content')
    @if($isPreview)
        <div class="bg-amber-900/50 border-b border-amber-700/50 px-6 py-3">
            <div class="max-w-6xl mx-auto text-sm text-amber-300 text-center">
                You're viewing a private preview of this release.
            </div>
        </div>
    @endif

    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="grid md:grid-cols-[320px_1fr] gap-12">
            {{-- Cover Art --}}
            <div>
                <div class="aspect-square rounded-xl overflow-hidden bg-gray-800 sticky top-8">
                    @if($release->coverAsset?->url)
                        <img src="{{ $release->coverAsset->url }}"
                             alt="{{ $release->title }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="space-y-8">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gray-800 text-gray-300">
                            {{ $release->type->getLabel() }}
                        </span>
                        @if($release->formatted_release_date)
                            <span class="text-sm text-gray-500">{{ $release->formatted_release_date }}</span>
                        @endif
                    </div>
                    <h1 class="text-4xl font-semibold text-white mb-2">{{ $release->title }}</h1>
                    <p class="text-gray-400">{{ $release->artist->name }}</p>
                </div>

                @if($release->description)
                    <div class="text-gray-300 leading-relaxed">
                        {!! nl2br(e($release->description)) !!}
                    </div>
                @endif

                @if($release->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($release->tags as $tag)
                            <span class="text-xs px-3 py-1 rounded-full bg-gray-800 text-gray-400 border border-gray-700">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Tracklist --}}
                @if($release->tracks->isNotEmpty())
                    <div>
                        <h2 class="text-lg font-semibold text-white mb-4">Tracklist</h2>
                        <div class="rounded-xl overflow-hidden border border-gray-800">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-800">
                                    @foreach($release->tracks as $track)
                                        <tr class="group">
                                            <td class="px-4 py-3 text-gray-500 text-right w-8 tabular-nums">
                                                {{ $track->position }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-100 font-medium">
                                                {{ $track->title }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 text-right tabular-nums">
                                                {{ $track->formatted_duration ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 w-10">
                                                @if($track->audioAsset?->url)
                                                    <audio controls class="h-7 w-32 opacity-70 group-hover:opacity-100 transition-opacity" preload="none">
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
                        <h2 class="text-lg font-semibold text-white mb-3">Credits</h2>
                        <p class="text-gray-400 text-sm leading-relaxed">{{ $release->credits }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
