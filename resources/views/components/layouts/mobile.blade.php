@props([
    'title' => config('app.name', 'Tracking Finance'),
    'activeNav' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name', 'Tracking Finance') }}</title>

    {{-- Sinkronkan preferensi tema sebelum render pertama agar tidak berkedip (anti-FOUC) --}}
    <script>
        (function() {
            const saved = localStorage.getItem('tf_theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    {{-- Google Fonts: Space Grotesk & Material Symbols Outlined (100% selaras Stitch) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- Memuat CSS (Tailwind v4.3) dan JS (Alpine.js) melalui Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas-base text-text-primary dark:bg-dark-canvas dark:text-dark-text-primary antialiased min-h-screen flex justify-center selection:bg-accent-lime selection:text-forest-deep dark:selection:bg-dark-lime dark:selection:text-dark-canvas font-sans text-body-md">
    {{-- Kerangka Mobile-First Container (390px target di mobile, terpusat rapi di desktop) --}}
    <div class="w-full max-w-[390px] min-h-screen bg-canvas-base dark:bg-dark-canvas flex flex-col relative pb-24 md:border-x md:border-border-subtle md:dark:border-dark-border">
        <main class="flex-1 flex flex-col">
            {{ $slot }}
        </main>

        @if ($activeNav)
            <x-bottom-nav :active="$activeNav" />
        @endif

        {{-- Global Delete Confirmation Modal (IMV-001) --}}
        <x-confirm-delete-modal />

        {{-- Global Toast Notification (IMV-002) --}}
        <x-toast />
    </div>
</body>

</html>
