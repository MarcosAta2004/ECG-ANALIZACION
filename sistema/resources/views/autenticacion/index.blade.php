@extends('plantillas.invitado')

@section('title', 'Iniciar sesión')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    {{-- Fondo animado --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" style="opacity:0.2;">
        <div class="absolute inset-0 medical-grid"></div>
        <svg class="absolute w-full h-32" style="bottom:25%;"
             viewBox="0 0 1200 100" preserveAspectRatio="none">
            <path d="M0,50 L100,50 L120,50 L140,20 L160,80 L180,50 L200,50 L220,50 L240,50
                     L260,10 L280,90 L300,50 L320,50 L400,50 L420,50 L440,20 L460,80 L480,50
                     L500,50 L520,50 L540,50 L560,10 L580,90 L600,50 L620,50 L700,50 L720,50
                     L740,20 L760,80 L780,50 L800,50 L820,50 L840,50 L860,10 L880,90 L900,50
                     L920,50 L1000,50 L1020,50 L1040,20 L1060,80 L1080,50 L1100,50 L1120,50
                     L1140,50 L1160,10 L1180,90 L1200,50"
                  fill="none" stroke="hsl(var(--primary))" stroke-width="2"
                  class="ecg-line" />
        </svg>
        <div class="absolute top-1/2 left-1/2 w-[800px] h-[800px] rounded-full"
             style="transform:translate(-50%,-50%);
                    background:radial-gradient(ellipse at center,hsl(var(--primary)/0.08) 0%,transparent 70%);"></div>
    </div>

    {{-- Tarjeta de login --}}
    <div class="relative w-full max-w-md animate-fade-in-up">
        <div class="absolute -inset-1 rounded-2xl"
             style="background:linear-gradient(135deg,hsl(var(--primary)/0.2),hsl(var(--primary)/0.1),hsl(var(--primary)/0.2));
                    filter:blur(16px);opacity:0.6;"></div>

        <div class="relative glass rounded-2xl p-8 shadow-elevated">

            {{-- Header --}}
            <div class="text-center mb-8 animate-fade-in-delay-1">
                <div class="inline-flex items-center justify-center mb-4">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-16 w-16 text-primary animate-heartbeat"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                        </svg>
                        <div class="absolute inset-0 rounded-full"
                             style="background:hsl(var(--primary)/0.3);filter:blur(20px);"></div>
                    </div>
                </div>
                <h1 class="text-3xl font-bold gradient-text mb-2">ECG Analizacion</h1>
                <p class="text-muted-foreground">Sistema de Análisis de Electrocardiogramas</p>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('login.post') }}"
                  x-data="loginForm" @submit.prevent="handleSubmit">
                @csrf

                {{-- Usuario --}}
                <div class="mb-5 animate-fade-in-delay-2">
                    <label for="login" class="block text-sm font-medium text-foreground mb-2">
                        Usuario o Correo
                    </label>
                    <div class="input-with-icon">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="input-icon"
                             width="20" height="20"
                             aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <input id="login" name="login" type="text"
                               value="{{ old('login') }}"
                               placeholder="Ej: admin o doctor@hospital.com"
                               class="input-field"
                               :disabled="loading"
                               autocomplete="username" />
                    </div>
                    @error('login')
                        <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="mb-5 animate-fade-in-delay-3">
                    <label for="password" class="block text-sm font-medium text-foreground mb-2">
                        Contraseña
                    </label>
                    <div class="input-with-icon">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="input-icon"
                             width="20" height="20"
                             aria-hidden="true"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6
                                     a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input id="password" name="password" type="password"
                               placeholder="••••••••"
                               class="input-field"
                               :disabled="loading"
                               autocomplete="current-password" />
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Error general --}}
                @if(session('error'))
                    <div class="alert-error mb-5 animate-fade-in">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Botón --}}
                <button type="submit"
                        :disabled="loading"
                        class="btn-primary w-full glow-cyan animate-fade-in-delay-4">
                    <template x-if="loading">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Ingresando...
                        </span>
                    </template>
                    <template x-if="!loading">
                        <span>Ingresar</span>
                    </template>
                </button>

            </form>

            <p class="mt-6 text-center text-xs text-muted-foreground animate-fade-in-delay-4">
                Sistema de diagnóstico asistido por IA para profesionales de la salud
            </p>
        </div>
    </div>
</div>
@endsection
