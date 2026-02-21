@extends('layouts.app')

@section('title', 'Releases')

@section('content')
    <div class="max-w-6xl mx-auto px-6 py-16">
        <h1 class="text-3xl font-semibold text-white mb-10">Releases</h1>

        @if($releases->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-12">
                @foreach($releases as $release)
                    <x-release-card :release="$release" />
                @endforeach
            </div>

            {{ $releases->links() }}
        @else
            <p class="text-gray-500">No releases yet.</p>
        @endif
    </div>
@endsection
