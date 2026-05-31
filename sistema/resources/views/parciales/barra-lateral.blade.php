@php
    $user = auth()->user();
    $roleName = session('user.role')
        ?? $user?->rolesa?->name
        ?? (($user && method_exists($user, 'getRoleNames')) ? $user->getRoleNames()->first() : '');
    $isAdmin = mb_strtolower((string) $roleName, 'UTF-8') === 'administrador';
@endphp

<div x-data="{ open: false }">

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

    <div x-show="open" @click="open = false"
         class="lg:hidden fixed inset-0 z-40 overlay-backdrop"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <aside class="sidebar"
           :class="open ? '' : 'sidebar-hidden'">

        <div class="p-6 border-b border-sidebar-border">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary animate-heartbeat"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M2 12h2l2-7 3 14 3-10 2 3h4l2-4 2 4h2" />
                    </svg>
                    <div class="absolute inset-0 rounded-full" style="background:hsl(var(--primary)/0.2);filter:blur(12px);"></div>
                </div>
                <div>
                    <h1 class="text-xl font-bold gradient-text">ECG Analizacion</h1>
                    <p class="text-xs text-muted-foreground">Analisis Inteligente</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-b border-sidebar-border">
            <p class="text-sm text-muted-foreground">Sesion activa</p>
            <p class="text-sm font-medium truncate">{{ session('user.email') ?? $user?->email ?? '' }}</p>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            @php
                $navItems = [
                    ['title' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'chart'],
                    ['title' => 'Resumen', 'route' => 'resumen', 'icon' => 'home'],
                    ['title' => 'Subir ECG', 'route' => 'upload', 'icon' => 'upload'],
                    ['title' => 'Historial', 'route' => 'history', 'icon' => 'file-text'],
                    ['title' => 'Reportes', 'route' => 'reports', 'icon' => 'report'],
                    ['title' => 'Pacientes', 'route' => 'pacientes.index', 'icon' => 'patients'],
                ];

                if ($isAdmin) {
                    $navItems[] = ['title' => 'Usuarios', 'route' => 'usuarios.index', 'icon' => 'users'];
                    $navItems[] = ['title' => 'Auditoria', 'route' => 'auditoria.index', 'icon' => 'audit'];
                }
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   @click="open = false"
                   class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">

                    @if($item['icon'] === 'home')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2h-2" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 3h6v4H9zM9 12h6M9 16h4" />
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
                    @elseif($item['icon'] === 'report')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17h6M9 13h6m-6-4h3m5 12H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 3v5h5" />
                        </svg>
                    @elseif($item['icon'] === 'patients')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 7h6M9 11h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2zm2 16h6" />
                        </svg>
                    @elseif($item['icon'] === 'users')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 100-8 4 4 0 000 8zm8 0a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                    @elseif($item['icon'] === 'audit')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5-4.5V12c0 4.5-3.1 8.4-8 9.5-4.9-1.1-8-5-8-9.5V5.5L12 3l8 2.5z" />
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

        <div class="p-4 border-t border-sidebar-border">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6
                                 a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="font-medium">Cerrar sesion</span>
                </button>
            </form>
        </div>

    </aside>
</div>
