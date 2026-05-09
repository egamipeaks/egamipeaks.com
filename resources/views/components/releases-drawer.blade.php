@props(['currentSlug' => null])

@php
    $releases = \App\Models\Release::query()
        ->public()
        ->with(['coverAsset', 'artist'])
        ->latest('release_date')
        ->get();
@endphp

<div x-data="{ drawerOpen: false }" @keydown.escape.window="drawerOpen = false">
    <button type="button"
            @click="drawerOpen = true"
            class="shrink-0 inline-flex items-center gap-2 px-3 py-2 border-2 border-[#1a1a1a] text-xs font-medium uppercase tracking-wider text-[#1a1a1a] hover:bg-[#1a1a1a] hover:text-white transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        All releases ({{ $releases->count() }})
    </button>

    <div x-show="drawerOpen"
         x-cloak
         x-transition.opacity
         @click="drawerOpen = false"
         class="fixed inset-0 bg-black/40 z-50"
         aria-hidden="true"></div>

    <aside x-show="drawerOpen"
           x-cloak
           x-transition:enter="transition transform ease-out duration-200"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition transform ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full"
           class="fixed top-0 right-0 h-full w-full max-w-sm bg-white border-l-2 border-[#1a1a1a] z-50 flex flex-col"
           aria-label="All releases">
        <div class="flex items-center justify-between px-6 py-4 border-b-2 border-[#1a1a1a] shrink-0">
            <h2 class="text-lg font-bold text-[#1a1a1a] uppercase tracking-wider">All Releases</h2>
            <button type="button"
                    @click="drawerOpen = false"
                    class="w-8 h-8 flex items-center justify-center border-2 border-[#1a1a1a] hover:bg-[#1a1a1a] hover:text-white transition-colors"
                    aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <ul class="flex-1 overflow-y-auto divide-y divide-[#1a1a1a]/10">
            @foreach($releases as $r)
                <li>
                    <a href="{{ route('releases.show', $r->slug) }}"
                       @class([
                           'flex items-center gap-3 px-6 py-3 transition-colors',
                           'bg-[#ddf4fb]' => $r->slug === $currentSlug,
                           'hover:bg-[#f5f3f0]' => $r->slug !== $currentSlug,
                       ])>
                        <div class="w-12 h-12 shrink-0 border border-[#1a1a1a] overflow-hidden bg-[#f5f3f0]">
                            @if($r->coverAsset?->url)
                                <img src="{{ $r->coverAsset->url }}"
                                     alt=""
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[#6b6b6b]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-[#1a1a1a] truncate">{{ $r->title }}</div>
                            <div class="text-xs text-[#6b6b6b] truncate">
                                {{ $r->artist->name }}@if($r->formatted_release_date) &middot; {{ $r->formatted_release_date }}@endif
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
</div>
