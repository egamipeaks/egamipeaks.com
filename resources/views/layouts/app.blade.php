<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title') @yield('title') — {{ config('app.name') }} @else {{ config('app.name') }} @endif</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex flex-col">
    <nav class="border-b border-gray-800 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight hover:text-white transition-colors">
                {{ config('app.name') }}
            </a>
            <a href="{{ route('releases.index') }}" class="text-sm text-gray-400 hover:text-white transition-colors">
                Releases
            </a>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-gray-800 px-6 py-8 mt-16">
        <div class="max-w-6xl mx-auto text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </footer>
</body>
</html>
