@extends('layouts.app')

@section('title', 'Subscription confirmed')

@section('content')
    <div class="max-w-2xl mx-auto px-6 py-16 text-center">
        <h1 class="text-3xl font-bold uppercase tracking-widest mb-4">
            @if ($alreadyVerified)
                You're already subscribed
            @else
                You're in
            @endif
        </h1>
        <p class="text-[#6b6b6b]">
            @if ($alreadyVerified)
                Your subscription was already confirmed. We'll let you know when there's a new release.
            @else
                Thanks for confirming. You'll get an email whenever there's a new release.
            @endif
        </p>
        <a href="{{ route('home') }}" class="inline-block mt-8 border-2 border-[#1a1a1a] px-6 py-2 uppercase tracking-widest text-sm font-bold hover:bg-[#1a1a1a] hover:text-[#f5f3f0] transition-colors">
            Back to home
        </a>
    </div>
@endsection
