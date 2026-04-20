<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ECG Analyzer - @yield('title', 'Acceso')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background min-h-screen pb-16" x-data="{ showClinicalDisclaimer: false }">
    @yield('content')

    @include('partials.clinical-footer', ['withSidebar' => false])
</body>
</html>
