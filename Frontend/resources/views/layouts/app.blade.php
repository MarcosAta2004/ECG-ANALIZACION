<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ECG Analyzer – @yield('title', 'Panel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background min-h-screen">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Contenido principal --}}
    <main class="lg:ml-64 min-h-screen">

        {{-- Header de página --}}
        <header class="sticky top-0 z-30 border-b border-border"
                style="background:hsl(var(--background)/0.8);backdrop-filter:blur(12px);">
            <div class="px-4 lg:px-8 py-4 lg:py-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="pt-12 lg:pt-0">
                        @yield('page-header')
                    </div>
                    <div class="flex items-center gap-2">
                        @yield('header-actions')
                    </div>
                </div>
            </div>
        </header>

        {{-- Contenido --}}
        <div class="px-4 lg:px-8 py-6 lg:py-8">
            @yield('content')
        </div>

    </main>

</body>
</html>
