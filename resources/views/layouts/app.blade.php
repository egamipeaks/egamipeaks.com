<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title') @yield('title') — {{ config('app.name') }} @else {{ config('app.name') }} @endif</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-mono:400,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f3f0] text-[#1a1a1a] font-sans min-h-screen flex flex-col">
    <nav class="border-b-2 border-[#1a1a1a] px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="group flex flex-col leading-tight">
                <span class="text-lg font-bold uppercase tracking-widest text-[#1a1a1a] group-hover:text-[#1da0c3] transition-colors">
                    {{ config('app.name') }}
                </span>
                <span class="text-xs text-[#6b6b6b] tracking-wide">
                    Music by Andrew Krzynowek
                </span>
            </a>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t-2 border-[#1a1a1a] px-6 py-8 mt-16">
        <div class="max-w-6xl mx-auto text-sm text-[#6b6b6b]">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </footer>
</body>
</html>
