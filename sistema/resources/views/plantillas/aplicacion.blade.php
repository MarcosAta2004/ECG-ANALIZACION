<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>ECG Analyzer - @yield('title', 'Panel')</title>
    @vite(['resources/css/aplicacion.css', 'resources/js/aplicacion.js'])
</head>
<body class="bg-background min-h-screen" x-data="{ showClinicalDisclaimer: false }">

    @include('parciales.barra-lateral')

    {{-- Lado Derecho: Navbar + Contenido + Footer --}}
    <div class="flex flex-col bg-muted/30">
        
        {{-- Navbar --}}
        @include('parciales.navbar')

        {{-- Espacio para el CRUD --}}
        <main class="flex-1 px-4 lg:px-8 py-8">
            <div class="mb-8">
                @yield('page-header')
            </div>

            @if (session('error'))
                <div class="alert-error mb-6">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error mb-6 animate-fade-in-up">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        {{-- Footer integrado en el cuerpo --}}
        <footer class="py-6 px-8 border-t border-border/40">
            <div class="text-center">
                <p class="text-[11px] text-muted-foreground font-medium tracking-wide">
                    Copyright {{ date('Y') }} © ArrhythmiaAI - Sistema de Análisis ECG 
                    <span class="mx-2 text-border">|</span>
                    <button @click="showClinicalDisclaimer = true" class="hover:text-primary transition-colors">Aviso Legal</button>
                </p>
            </div>
        </footer>

    </div>

    {{-- Modales globales --}}
    @include('parciales.modal-clinico')

    {{-- 
        GUIA PARA EL DESARROLLADOR (CRUD)
        -------------------------------
        Para crear nuevos CRUDs que se vean igual al resto del sistema:
        
        1. Estructura de la Vista:
           @extends('plantillas.aplicacion')
           @section('content')
              <div class="space-y-6"> ... </div>
           @endsection

        2. componentes utiles:
           - Tarjetas: <div class="card"> ... </div>
           - Botones: <button class="btn-primary">Texto</button>
           - Inputs: <input class="input-field">
           - Tablas: <table class="table-ecg"> ... </table>
           - Badges: <span class="badge badge-success">Activo</span>

        3. Animaciones:
           Usa la clase 'animate-fade-in-up' en tus contenedores principales.
    --}}

</body>
</html>
