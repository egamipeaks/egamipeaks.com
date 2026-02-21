@extends('layouts.app')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-gray-800">
        @if($artist->heroImage?->url)
            <div class="absolute inset-0">
                <img src="{{ $artist->heroImage->url }}"
                     alt="{{ $artist->name }}"
                     class="w-full h-full object-cover opacity-20">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-950"></div>
            </div>
        @endif
        <div class="relative max-w-6xl mx-auto px-6 py-24 md:py-36">
            <h1 class="text-5xl md:text-7xl font-semibold tracking-tight text-white mb-6">
                {{ $artist->name }}
            </h1>
            @if($artist->bio)
                <p class="text-lg text-gray-300 max-w-2xl leading-relaxed">
                    {{ Str::limit($artist->bio, 280) }}
                </p>
            @endif
        </div>
    </section>

    {{-- Recent Releases --}}
    <section class="max-w-6xl mx-auto px-6 py-16">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-semibold text-white">Recent Releases</h2>
            <a href="{{ route('releases.index') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                View all releases →
            </a>
        </div>

        @if($recentReleases->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($recentReleases as $release)
                    <x-release-card :release="$release" />
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">No releases yet.</p>
        @endif
    </section>
@endsection
