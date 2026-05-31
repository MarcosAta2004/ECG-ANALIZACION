@php
    $user = session('user');
    $isAdmin = ($user['role'] ?? '') === 'Administrador';
@endphp

<div x-data="{ open: false, maintOpen: {{ request()->routeIs(['ritmos-cardiacos.*', 'grupos-cardiacos.*', 'niveles-gravedad.*', 'clasificaciones-arritmia.*', 'prefijos-paciente.*']) ? 'true' : 'false' }} }">

    {{-- Botón hamburguesa (móvil) --}}
    <button @click="open = !open"
        class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-card border border-border hover:bg-secondary transition-colors">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Overlay móvil --}}
    <div x-show="open" @click="open = false" class="lg:hidden fixed inset-0 z-40"
        style="background:hsl(var(--background)/0.8);backdrop-filter:blur(4px);"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- Sidebar --}}
    <aside class="sidebar" :class="open ? '' : 'sidebar-hidden'">

        {{-- Logo --}}
        <div class="p-6 border-b border-sidebar-border">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary animate-heartbeat" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                    </svg>
                    <div class="absolute inset-0 rounded-full"
                        style="background:hsl(var(--primary)/0.2);filter:blur(12px);"></div>
                </div>
                <div>
                    <h1 class="text-xl font-bold gradient-text">ECG Analyzer</h1>
                    <p class="text-xs text-muted-foreground">Análisis Inteligente</p>
                </div>
            </div>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            
            <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest px-3 mb-2 mt-4">Principal</div>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" @click="open = false"
                class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- Pacientes --}}
            <a href="{{ route('pacientes.index') }}" @click="open = false"
                class="nav-link {{ request()->routeIs('pacientes.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 100-8 4 4 0 000 8zm8 0a4 4 0 100-8 4 4 0 000 8z" />
                </svg>
                <span>Pacientes</span>
            </a>

            {{-- Estudios --}}
            <a href="{{ route('imagenes.index') }}" @click="open = false"
                class="nav-link {{ request()->routeIs('imagenes.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Estudios (Análisis)</span>
            </a>

            {{-- Historial --}}
            <a href="{{ route('estudios.index') }}" @click="open = false"
                class="nav-link {{ request()->routeIs('estudios.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Historial Clínico</span>
            </a>

            {{-- Reportes --}}
            <a href="{{ route('reportes.index') }}" @click="open = false"
                class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6M9 13h6m-6-4h3m5 12H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5h5" />
                </svg>
                <span>Reportes PDF</span>
            </a>

            @if ($isAdmin)
                <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest px-3 mb-2 mt-6">Administración</div>
                
                {{-- Usuarios --}}
                <a href="{{ route('usuarios.index') }}" @click="open = false"
                    class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Usuarios</span>
                </a>

                {{-- Mantenimientos Dropdown --}}
                <div class="space-y-1">
                    <button @click="maintOpen = !maintOpen" 
                        class="nav-link w-full justify-between {{ request()->routeIs(['ritmos-cardiacos.*', 'grupos-cardiacos.*', 'niveles-gravedad.*', 'clasificaciones-arritmia.*', 'prefijos-paciente.*']) ? 'bg-secondary/50' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Mantenimientos</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="maintOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div x-show="maintOpen" x-transition class="pl-11 space-y-1">
                        <a href="{{ route('ritmos-cardiacos.index') }}" class="block py-1.5 text-xs {{ request()->routeIs('ritmos-cardiacos.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground transition-colors' }}">Ritmos Cardíacos</a>
                        <a href="{{ route('grupos-cardiacos.index') }}" class="block py-1.5 text-xs {{ request()->routeIs('grupos-cardiacos.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground transition-colors' }}">Grupos Cardíacos</a>
                        <a href="{{ route('niveles-gravedad.index') }}" class="block py-1.5 text-xs {{ request()->routeIs('niveles-gravedad.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground transition-colors' }}">Niveles Gravedad</a>
                        <a href="{{ route('clasificaciones-arritmia.index') }}" class="block py-1.5 text-xs {{ request()->routeIs('clasificaciones-arritmia.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground transition-colors' }}">Clasificaciones</a>
                        <a href="{{ route('prefijos-paciente.index') }}" class="block py-1.5 text-xs {{ request()->routeIs('prefijos-paciente.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground transition-colors' }}">Prefijos</a>
                    </div>
                </div>

                {{-- Auditoria --}}
                <a href="{{ route('auditoria.index') }}" @click="open = false"
                    class="nav-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-4.5V12c0 4.5-3.1 8.4-8 9.5-4.9-1.1-8-5-8-9.5V5.5L12 3l8 2.5z" />
                    </svg>
                    <span>Auditoria</span>
                </a>
            @endif
        </nav>
    </aside>
</div>