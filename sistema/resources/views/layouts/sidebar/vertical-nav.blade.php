<ul class="navbar-nav iq-main-menu" id="sidebar">

    {{-- ========================================== --}}
    {{-- 1. SECCIÓN PRINCIPAL: INICIO --}}
    {{-- ========================================== --}}
    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Inicio</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" aria-current="page" href="{{ route('dashboard') }}">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            </i>
            <span class="item-name">Dashboard</span>
        </a>
    </li>

    <li><hr class="hr-horizontal"></li>

    {{-- ========================================== --}}
    {{-- 2. SECCIÓN: GESTIÓN MÉDICA --}}
    {{-- ========================================== --}}
    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Gestión Médica</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" aria-current="page" href="{{ route('pacientes.index') }}">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </i>
            <span class="item-name">Pacientes</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" aria-current="page" href="{{ route('estudios.index') }}">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </i>
            <span class="item-name">Estudios</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" aria-current="page" href="{{ route('upload') }}">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                </svg>
            </i>
            <span class="item-name">Subir ECG</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" aria-current="page" href="{{ route('imagenes.index') }}">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </i>
            <span class="item-name">Imágenes</span>
        </a>
    </li>

    <li><hr class="hr-horizontal"></li>

    {{-- ========================================== --}}
    {{-- 3. SECCIÓN: RESULTADOS E IA --}}
    {{-- ========================================== --}}
    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Resultados e IA</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" aria-current="page" href="{{ route('diagnosticos.index') }}">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                </svg>
            </i>
            <span class="item-name">Diagnósticos</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" aria-current="page" href="{{ route('predicciones.index') }}">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </i>
            <span class="item-name">Predicciones</span>
        </a>
    </li>

    <li><hr class="hr-horizontal"></li>

    {{-- ========================================== --}}
    {{-- 4. SECCIÓN: CONFIGURACIÓN --}}
    {{-- ========================================== --}}
    <li class="nav-item static-item">
        <a class="nav-link static-item disabled" href="#" tabindex="-1">
            <span class="default-icon">Configuración</span>
            <span class="mini-icon">-</span>
        </a>
    </li>

    {{-- Menú Desplegable: MANTENIMIENTO --}}
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#mantenimiento-collapse" role="button" aria-expanded="false" aria-controls="mantenimiento-collapse">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.054-2.066.015a3.5 3.5 0 0 1-3.21-1.58A3.5 3.5 0 0 1 4.5 10.5a3.5 3.5 0 0 1 6.541-1.745c.074.68.08 1.383.018 2.073m-1.72 1.708a2.25 2.25 0 0 0 3.181 3.181l4.243-4.243a2.25 2.25 0 0 0-3.182-3.182l-4.242 4.244Z" />
                </svg>
            </i>
            <span class="item-name">Mantenimiento</span>
            <i class="right-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </i>
        </a>

        <ul class="sub-nav collapse" id="mantenimiento-collapse" data-bs-parent="#sidebar">
            
            <li class="nav-item">
                <a class="nav-link" href="{{ route('prefijos-paciente.index') }}">
                    <i class="icon">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    </i>
                    <span class="item-name">Prefijos Paciente</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('ritmos-cardiacos.index') }}">
                    <i class="icon">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    </i>
                    <span class="item-name">Ritmos Cardíacos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('niveles-gravedad.index') }}">
                    <i class="icon">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    </i>
                    <span class="item-name">Niveles de Gravedad</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('grupos-cardiacos.index') }}">
                    <i class="icon">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    </i>
                    <span class="item-name">Grupos Cardíacos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('clasificaciones-arritmia.index') }}">
                    <i class="icon">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    </i>
                    <span class="item-name">Clasificaciones</span>
                </a>
            </li>
        </ul>
    </li>

    {{-- Menú Desplegable: SEGURIDAD --}}
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#seguridad-collapse" role="button" aria-expanded="false" aria-controls="seguridad-collapse">
            <i class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </i>
            <span class="item-name">Seguridad</span>
            <i class="right-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </i>
        </a>

        <ul class="sub-nav collapse" id="seguridad-collapse" data-bs-parent="#sidebar">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('usuarios.index') }}">
                    <i class="icon">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    </i>
                    <span class="item-name">Usuarios</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('roles.index') }}">
                    <i class="icon">
                        <svg width="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                    </i>
                    <span class="item-name">Roles</span>
                </a>
            </li>
        </ul>
    </li>

</ul>
