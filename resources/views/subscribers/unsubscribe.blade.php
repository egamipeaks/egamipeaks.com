@extends('layouts.app')

@section('title', 'Unsubscribe')

@section('content')
    <div class="max-w-2xl mx-auto px-6 py-16 text-center">
        <h1 class="text-3xl font-bold uppercase tracking-widest mb-4">Unsubscribe</h1>
        <p class="text-[#6b6b6b] mb-8">
            Click the button below to remove <strong>{{ $email }}</strong> from the list.
        </p>
        <form method="POST" action="{{ route('subscribe.unsubscribe.confirm', ['token' => $token]) }}">
            @csrf
            <button type="submit" class="cursor-pointer border-2 border-ink bg-ink text-white px-6 py-3 uppercase tracking-widest text-sm font-bold transition-colors hover:bg-red-700 hover:border-red-700 hover:text-white">
                Unsubscribe me
            </button>
        </form>
    </div>
@endsection
