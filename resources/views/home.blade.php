@extends('layouts.app')

@section('content')
    {{-- Hero --}}
{{--    <section class="border-b-2 border-[#1a1a1a]">--}}
{{--        @if($artist->heroImage?->url)--}}
{{--            <div class="relative h-64 md:h-96 overflow-hidden">--}}
{{--                <img src="{{ $artist->heroImage->url }}"--}}
{{--                     alt="{{ $artist->name }}"--}}
{{--                     class="w-full h-full object-cover">--}}
{{--            </div>--}}
{{--        @endif--}}
{{--        <div class="max-w-6xl mx-auto px-6 py-16 md:py-24">--}}
{{--            <h1 class="text-6xl md:text-8xl font-bold uppercase tracking-tight text-[#1a1a1a] mb-6">--}}
{{--                {{ $artist->name }}--}}
{{--            </h1>--}}
{{--            @if($artist->bio)--}}
{{--                <p class="text-base text-[#6b6b6b] max-w-2xl leading-relaxed">--}}
{{--                    {{ Str::limit($artist->bio, 280) }}--}}
{{--                </p>--}}
{{--            @endif--}}
{{--        </div>--}}
{{--    </section>--}}

    {{-- Recent Releases --}}
    <section class="max-w-6xl mx-auto px-6 py-16">
        <div class="flex items-center justify-between mb-8 border-b-2 border-[#1a1a1a] pb-4">
            <h2 class="text-xl font-bold uppercase tracking-wider text-[#1a1a1a]">Recent Releases</h2>
            <a href="{{ route('releases.index') }}" class="text-sm text-[#1da0c3] hover:underline">
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
            <p class="text-[#6b6b6b] text-sm">No releases yet.</p>
        @endif
    </section>
@endsection
