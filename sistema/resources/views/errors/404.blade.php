@extends('plantillas.invitado')

@section('title', 'Página no encontrada')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <div class="fixed inset-0 pointer-events-none" style="opacity:0.15;">
        <div class="absolute inset-0 medical-grid"></div>
    </div>

    <div class="relative text-center animate-fade-in-up max-w-lg">
        <div class="mb-8">
            <div class="relative inline-flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-24 w-24 text-primary animate-heartbeat"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                </svg>
                <div class="absolute inset-0 rounded-full"
                     style="background:hsl(var(--primary)/0.15);filter:blur(24px);"></div>
            </div>
        </div>

        <h1 class="text-8xl font-bold gradient-text mb-4">404</h1>
        <h2 class="text-2xl font-semibold mb-3">Página no encontrada</h2>
        <p class="text-muted-foreground mb-8">
            La página que buscas no existe o fue movida.
        </p>

        <a href="{{ route('dashboard') }}"
           class="btn-primary inline-flex items-center gap-2 glow-cyan">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            Volver al Dashboard
        </a>
    </div>
</div>
@endsection
