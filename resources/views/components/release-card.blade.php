@props(['release'])

<a href="{{ route('releases.show', ['slug' => $release->slug]) }}"
   class="group block rounded-xl overflow-hidden bg-gray-900 border border-gray-800 hover:border-gray-600 transition-all duration-200">
    <div class="aspect-square bg-gray-800 overflow-hidden">
        @if($release->coverAsset?->url)
            <img src="{{ $release->coverAsset->url }}"
                 alt="{{ $release->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
        @endif
    </div>
    <div class="p-4 space-y-2">
        <h3 class="font-semibold text-white group-hover:text-gray-100 leading-tight line-clamp-2">
            {{ $release->title }}
        </h3>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-700 text-gray-300">
                {{ $release->type->getLabel() }}
            </span>
        </div>
        @if($release->formatted_release_date)
            <p class="text-xs text-gray-500">{{ $release->formatted_release_date }}</p>
        @endif
    </div>
</a>
