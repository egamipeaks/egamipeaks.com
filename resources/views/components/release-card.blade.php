@props(['release'])

<a href="{{ route('releases.show', ['slug' => $release->slug]) }}"
   class="group block overflow-hidden bg-white border-2 border-[#1a1a1a] hover:border-[#1da0c3] transition-colors duration-150">
    <div class="aspect-square bg-[#f5f3f0] overflow-hidden">
        @if($release->coverAsset?->url)
            <img src="{{ $release->coverAsset->url }}"
                 alt="{{ $release->title }}"
                 class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center text-[#6b6b6b]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
        @endif
    </div>
    <div class="p-4 space-y-2">
        <h3 class="font-bold text-[#1a1a1a] leading-tight line-clamp-2">
            {{ $release->title }}
        </h3>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-medium px-2 py-0.5 border border-[#1a1a1a] text-[#1a1a1a]">
                {{ $release->type->getLabel() }}
            </span>
        </div>
        @if($release->formatted_release_date)
            <p class="text-xs text-[#6b6b6b]">{{ $release->formatted_release_date }}</p>
        @endif
    </div>
</a>
