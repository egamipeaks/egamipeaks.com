@extends('layouts.app')

@section('title', 'Confirm subscription')

@section('content')
    <div class="max-w-2xl mx-auto px-6 py-16 text-center">
        @if ($alreadyVerified)
            <h1 class="text-3xl font-bold uppercase tracking-widest mb-4">You're already subscribed</h1>
            <p class="text-[#6b6b6b]">
                Your subscription is already confirmed. We'll let you know when there's a new release.
            </p>
            <a href="{{ route('home') }}" class="inline-block mt-8 border-2 border-[#1a1a1a] px-6 py-2 uppercase tracking-widest text-sm font-bold hover:bg-[#1a1a1a] hover:text-[#f5f3f0] transition-colors">
                Back to home
            </a>
        @else
            <h1 class="text-3xl font-bold uppercase tracking-widest mb-4">Confirm your subscription</h1>
            <p class="text-[#6b6b6b] mb-8">
                Click the button below to confirm your email address.
            </p>
            <form method="POST" action="{{ route('subscribe.verify.confirm', ['token' => $token]) }}">
                @csrf
                <button type="submit" class="cursor-pointer border-2 border-ink bg-ink text-white px-6 py-3 uppercase tracking-widest text-sm font-bold transition-colors hover:bg-accent hover:border-accent hover:text-ink">
                    Confirm subscription
                </button>
            </form>
        @endif
    </div>
@endsection
