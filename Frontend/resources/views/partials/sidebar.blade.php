@php $user = session('user'); @endphp

<div x-data="{ open: false }">

    {{-- Botón hamburguesa (móvil) --}}
    <button @click="open = !open"
            class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-card border border-border hover:bg-secondary transition-colors">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Overlay móvil --}}
    <div x-show="open" @click="open = false"
         class="lg:hidden fixed inset-0 z-40"
         style="background:hsl(var(--background)/0.8);backdrop-filter:blur(4px);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    {{-- Sidebar --}}
    <aside class="sidebar"
           :class="open ? '' : 'sidebar-hidden'">

        {{-- Logo --}}
        <div class="p-6 border-b border-sidebar-border">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary animate-heartbeat"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945
                                 M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0
                                 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M2 12h2l2-7 3 14 3-10 2 3h4" />
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

        {{-- Usuario --}}
        <div class="px-6 py-4 border-b border-sidebar-border">
            <p class="text-sm text-muted-foreground">Sesión activa</p>
            <p class="text-sm font-medium truncate">{{ $user['email'] ?? '' }}</p>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 p-4 space-y-2">
            @php
                $navItems = [
                    ['title' => 'Dashboard',           'route' => 'dashboard', 'icon' => 'chart'],
                    ['title' => 'Resumen',             'route' => 'resumen',   'icon' => 'home'],
                    ['title' => 'Subir ECG',           'route' => 'upload',    'icon' => 'upload'],
                    ['title' => 'Historial / Reportes','route' => 'history',   'icon' => 'file-text'],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   @click="open = false"
                   class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">

                    @if($item['icon'] === 'home')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    @elseif($item['icon'] === 'upload')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    @elseif($item['icon'] === 'chart')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2
                                     a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14
                                     a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                                     a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @endif

                    <span>{{ $item['title'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Logout --}}
        <div class="p-4 border-t border-sidebar-border">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 w-full px-4 py-3 rounded-lg text-destructive hover:bg-destructive/10 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6
                                 a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="font-medium">Cerrar sesión</span>
                </button>
            </form>
        </div>

    </aside>
</div>
