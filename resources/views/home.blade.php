@extends('layouts.app')

@section('content')
    <section class="px-6">
    <div class="max-w-6xl mx-auto py-16">
        @if($releases->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($releases as $release)
                    <x-release-card :release="$release" />
                @endforeach
            </div>
        @else
            <p class="text-[#6b6b6b] text-sm">No releases yet.</p>
        @endif
    </div>
    </section>
@endsection
