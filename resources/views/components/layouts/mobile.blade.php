@props(['title' => config('app.name', 'Tracking Finance')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name', 'Tracking Finance') }}</title>

    {{-- Sinkronkan preferensi tema sebelum render pertama agar tidak berkedip (anti-FOUC) --}}
    <script>
        (function () {
            const saved = localStorage.getItem('tf_theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    {{-- Font Space Grotesk self-hosted otomatis oleh Laravel Vite --}}
    @fonts

    {{-- Google Material Symbols Outlined (mendukung state FILL 0 & 1 sesuai Stitch) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- Memuat CSS (Tailwind v4.3) dan JS (Alpine.js) melalui Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas-base text-text-primary dark:bg-dark-canvas dark:text-dark-text-primary antialiased min-h-screen selection:bg-accent-lime selection:text-forest-deep dark:selection:bg-dark-lime dark:selection:text-dark-canvas">
    {{-- Kerangka Mobile-First Container (rapi di layar smartphone maupun browser desktop) --}}
    <div class="mx-auto min-h-screen w-full max-w-md bg-canvas-base dark:bg-dark-canvas md:border-x md:border-border-subtle md:dark:border-dark-border">
        <main class="pb-24">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
