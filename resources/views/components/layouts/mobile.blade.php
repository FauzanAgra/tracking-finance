@props(['title' => config('app.name', 'Tracking Finance')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name', 'Tracking Finance') }}</title>

    {{-- Memuat CSS (Tailwind v4.3) dan JS (Alpine.js) melalui Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 antialiased min-h-screen">
    {{-- Kerangka Mobile-First Container (maksimal lebar layar smartphone di desktop agar tetap proporsional) --}}
    <div class="mx-auto min-h-screen w-full max-w-md bg-slate-900 shadow-2xl">
        <main class="pb-20">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
