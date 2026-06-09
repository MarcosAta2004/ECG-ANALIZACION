<x-guest-layout>
    <section class="login-content">
        <div class="row m-0 align-items-center bg-white vh-100">

            {{-- Panel Izquierdo --}}
            <div class="col-md-6">
                <div class="row justify-content-center">
                    {{-- Aquí aumentamos el ancho (de col-md-10 a col-md-11/lg-10/xl-9) --}}
                    <div class="col-md-12 col-lg-11 col-xl-10">
                        <div class="card card-transparent shadow-none d-flex justify-content-center mb-0 auth-card">
                            <div class="card-body">

                                {{-- Logo Centrado --}}
                                <div class="d-flex justify-content-center mb-4">
                                    <a href="{{ route('login') }}"
                                        class="navbar-brand d-flex align-items-center">
                                        <svg width="30" class="text-primary" viewBox="0 0 30 30" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect x="-0.757324" y="19.2427" width="28" height="4" rx="2"
                                                transform="rotate(-45 -0.757324 19.2427)" fill="currentColor" />
                                            <rect x="7.72803" y="27.728" width="28" height="4" rx="2"
                                                transform="rotate(-45 7.72803 27.728)" fill="currentColor" />
                                            <rect x="10.5366" y="16.3945" width="16" height="4" rx="2"
                                                transform="rotate(45 10.5366 16.3945)" fill="currentColor" />
                                            <rect x="10.5562" y="-0.556152" width="28" height="4" rx="2"
                                                transform="rotate(45 10.5562 -0.556152)" fill="currentColor" />
                                        </svg>
                                        <h4 class="logo-title ms-3 mb-0">{{ env('APP_NAME', 'Laravel') }}</h4>
                                    </a>
                                </div>

                                {{-- Textos Principales --}}
                                <h2 class="mb-2 text-center">Iniciar Sesión</h2>
                                <p class="text-center text-secondary mb-4">Ingresa tus credenciales para continuar.</p>

                                <x-auth-session-status class="mb-4 text-center" :status="session('status')" />
                                <x-auth-validation-errors class="mb-4 text-center" :errors="$errors" />

                                {{-- Formulario --}}
                                <form method="POST" action="{{ route('login.post') }}" data-toggle="validator">
                                    @csrf

                                    <div class="row">
                                        {{-- Input: Usuario/Email --}}
                                        <div class="col-lg-12">
                                            <div class="form-group mb-3">
                                                <label for="login" class="form-label text-secondary">Usuario</label>
                                                <input id="login" type="text" name="login"
                                                    value="{{ env('IS_DEMO') ? 'admin' : old('login') }}"
                                                    class="form-control" placeholder="Usuario" required autofocus>
                                            </div>
                                        </div>

                                        {{-- Input: Contraseña --}}
                                        <div class="col-lg-12">
                                            <div class="form-group mb-4">
                                                <label for="password"
                                                    class="form-label text-secondary">Contraseña</label>
                                                <input id="password" class="form-control" type="password"
                                                    name="password" placeholder="••••••••"
                                                    value="{{ env('IS_DEMO') ? 'password' : '' }}" required
                                                    autocomplete="current-password">
                                            </div>
                                        </div>

                                        {{-- Opciones: Recordarme y Recuperar Contraseña --}}
                                        <div class="col-lg-12">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div class="form-check mb-0">
                                                    <input type="checkbox" class="form-check-input" id="customCheck1"
                                                        name="remember">
                                                    <label class="form-check-label text-secondary"
                                                        for="customCheck1">Recordarme</label>
                                                </div>
                                                {{--<a href=""
                                                    class="text-primary fw-medium text-decoration-none">¿Olvidaste tu
                                                    contraseña?</a>*/--}}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Botón Submit --}}
                                    <div class="d-flex justify-content-center mb-4">
                                        <button type="submit"
                                            class="btn btn-primary px-5">{{ __('iniciarSesion') }}</button>
                                    </div>

                                    {{-- Redes Sociales
                                    <p class="text-center text-secondary mb-3">o inicia sesión con otras cuentas</p>
                                    <div class="d-flex justify-content-center mb-4">
                                        <ul class="list-group list-group-horizontal list-group-flush">
                                            <li class="list-group-item border-0 pb-0 bg-transparent">
                                                <a href="#"><img src="{{ asset('images/brands/fb.svg') }}" alt="fb"></a>
                                            </li>
                                            <li class="list-group-item border-0 pb-0 bg-transparent">
                                                <a href="#"><img src="{{ asset('images/brands/gm.svg') }}" alt="gm"></a>
                                            </li>
                                            <li class="list-group-item border-0 pb-0 bg-transparent">
                                                <a href="#"><img src="{{ asset('images/brands/im.svg') }}" alt="im"></a>
                                            </li>
                                            <li class="list-group-item border-0 pb-0 bg-transparent">
                                                <a href="#"><img src="{{ asset('images/brands/li.svg') }}" alt="li"></a>
                                            </li>
                                        </ul>
                                    </div> */}}

                                    {{-- Registro
                                    <p class="mt-3 text-center text-secondary small">
                                        ¿No tienes una cuenta? <a href="{{ route('auth.signup') }}"
                                            class="text-primary fw-medium text-decoration-none">Regístrate aquí.</a>
                                    </p>*/--}}
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fondo SVG Izquierdo --}}
                <div class="sign-bg">
                    <svg width="280" height="230" viewBox="0 0 431 398" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g opacity="0.05">
                            <rect x="-157.085" y="193.773" width="543" height="77.5714" rx="38.7857"
                                transform="rotate(-45 -157.085 193.773)" fill="#3B8AFF" />
                            <rect x="7.46875" y="358.327" width="543" height="77.5714" rx="38.7857"
                                transform="rotate(-45 7.46875 358.327)" fill="#3B8AFF" />
                            <rect x="61.9355" y="138.545" width="310.286" height="77.5714" rx="38.7857"
                                transform="rotate(45 61.9355 138.545)" fill="#3B8AFF" />
                            <rect x="62.3154" y="-190.173" width="543" height="77.5714" rx="38.7857"
                                transform="rotate(45 62.3154 -190.173)" fill="#3B8AFF" />
                        </g>
                    </svg>
                </div>
            </div>

            {{-- Panel Derecho (Imagen) --}}
            <div class="col-md-6 d-md-block d-none bg-primary p-0 mt-n1 vh-100 overflow-hidden">
                <img src="{{ asset('images/auth/01.png') }}" class="img-fluid gradient-main animated-scaleX w-100 h-100"
                    style="object-fit: cover;" alt="images">
            </div>

        </div>
    </section>
</x-guest-layout>