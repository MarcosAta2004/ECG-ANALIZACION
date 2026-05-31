@php
    $user = session('user');
@endphp

<header class="sticky top-0 z-30 flex h-16 w-full items-center justify-end border-b border-border bg-white px-4 lg:px-8">
    
    {{-- Lado Derecho: Solo Perfil --}}
    <div class="flex items-center gap-4">
        
        {{-- Perfil Dropdown --}}
        <div x-data="{ userOpen: false }" class="relative">
            <button @click="userOpen = !userOpen" @click.away="userOpen = false" 
                    class="flex items-center gap-3 p-1 rounded-lg hover:bg-secondary transition-all">
                
                <div class="hidden lg:block text-right pr-2">
                    <p class="text-xs font-bold leading-none text-foreground">{{ $user['nombre'] ?? 'Usuario' }}</p>
                    <p class="text-[10px] text-muted-foreground font-medium mt-1">{{ $user['role'] ?? 'Personal' }}</p>
                </div>

                <div class="h-9 w-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm shadow-sm overflow-hidden">
                    @if(isset($user['perfil']))
                        <img src="{{ asset($user['perfil']) }}" alt="Avatar" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($user['nombre'] ?? 'U', 0, 1)) }}
                    @endif
                </div>

                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground transition-transform duration-200" :class="userOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="userOpen" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 style="display: none;"
                 class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl bg-white border border-border shadow-xl ring-1 ring-black/5 focus:outline-none overflow-hidden z-50">
                <div class="p-2">
                    <a href="{{ route('perfil.index') }}" class="flex items-center gap-3 px-3 py-2 text-xs font-medium rounded-lg hover:bg-secondary transition-colors text-foreground">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Mi Perfil
                    </a>
                </div>
                <div class="border-t border-border bg-muted/20 px-2 py-2">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 px-3 py-2 text-xs font-bold text-destructive rounded-lg hover:bg-destructive/10 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
