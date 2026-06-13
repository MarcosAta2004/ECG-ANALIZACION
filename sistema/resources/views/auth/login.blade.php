<x-guest-layout>
    <style>
        .login-left {
            background-color: #ffffff;
            z-index: 10;
            position: relative;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.05);
        }

        .login-right {
            position: relative;
            background-color: #0f2027;
            /* Fallback */
        }

        .login-right img {
            object-fit: cover;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1;
            opacity: 0.6;
            /* Dejar que el fondo se mezcle */
            mix-blend-mode: luminosity;
        }

        .login-right .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(26, 54, 93, 0.95) 0%, rgba(15, 76, 117, 0.85) 100%);
            z-index: 2;
        }

        .login-right-content {
            position: relative;
            z-index: 3;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            padding: 4rem;
        }

        .form-control-modern {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .form-control-modern:focus {
            border-color: #3b8aff;
            box-shadow: 0 0 0 0.25rem rgba(59, 138, 255, 0.25);
            background-color: #ffffff;
        }

        .btn-modern {
            background: linear-gradient(135deg, #1A365D 0%, #3B8AFF 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 138, 255, 0.4);
        }

        .text-clinical {
            color: #1A365D;
        }

        .heartbeat-icon {
            animation: heartbeat 2s infinite;
        }

        @keyframes heartbeat {
            0% {
                transform: scale(1);
            }

            10% {
                transform: scale(1.1);
            }

            20% {
                transform: scale(1);
            }

            30% {
                transform: scale(1.1);
            }

            40% {
                transform: scale(1);
            }

            100% {
                transform: scale(1);
            }
        }

        .floating-shape {
            position: absolute;
            opacity: 0.05;
            z-index: -1;
        }
    </style>

    <section class="login-content p-0 m-0">
        <div class="row m-0 vh-100 w-100">

            {{-- Panel Izquierdo (Formulario) --}}
            <div class="col-12 col-md-5 col-lg-4 d-flex align-items-center justify-content-center login-left">

                {{-- Shapes decorativos de fondo en la izquierda --}}
                <svg class="floating-shape" style="top: -50px; left: -50px;" width="200" height="200"
                    viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="100" fill="#3B8AFF" />
                </svg>

                <div class="w-100 px-4 px-md-5" style="max-width: 450px;">

                    {{-- Logo ArrhythmiaAI --}}
                    <div class="text-center mb-5">
                        <div class="d-inline-block text-clinical heartbeat-icon mb-3">
                            <svg width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z">
                                </path>
                                <polyline points="3 12 7 12 10 5 14 19 17 12 21 12" stroke="#dc3545"></polyline>
                            </svg>
                        </div>
                        <h2 class="fw-bold text-clinical mb-1" style="letter-spacing: -0.5px;">ArrhythmiaAI</h2>
                        <p class="text-muted small">Sistema de Análisis Electrocardiográfico</p>
                    </div>

                    <h4 class="mb-2 fw-semibold text-dark">Bienvenido de nuevo</h4>
                    <p class="text-secondary mb-4" style="font-size: 0.9rem;">Por favor, ingresa tus credenciales de
                        acceso institucional.</p>

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show text-start mb-4" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Cerrar"></button>
                        </div>
                    @endif

                    <x-auth-validation-errors class="mb-4 text-center" :errors="$errors" />

                    {{-- Formulario --}}
                    <form method="POST" action="{{ route('login.post') }}" id="login-form">
                        @csrf

                        <div class="form-group mb-4">
                            <label for="login" class="form-label fw-medium text-dark small mb-1">Usuario /
                                Email</label>
                            <input id="login" type="text" name="login"
                                value="{{ env('IS_DEMO') ? 'admin' : old('login') }}"
                                class="form-control form-control-modern" placeholder="Ej. dr.perez@hospital.com"
                                required autofocus>
                        </div>

                        <div class="form-group mb-4">
                            <label for="password" class="form-label fw-medium text-dark small mb-1">Contraseña</label>
                            <input id="password" class="form-control form-control-modern" type="password"
                                name="password" placeholder="••••••••" value="{{ env('IS_DEMO') ? 'password' : '' }}"
                                required autocomplete="current-password">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="customCheck1" name="remember"
                                    style="cursor:pointer;">
                                <label class="form-check-label text-secondary small" for="customCheck1"
                                    style="cursor:pointer;">Recordarme</label>
                            </div>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit"
                                class="btn btn-primary btn-modern text-white shadow-sm d-flex justify-content-center align-items-center">
                                <span class="btn-text">Ingresar al Sistema</span>
                                <span class="btn-spinner spinner-border spinner-border-sm d-none ms-2" role="status"
                                    aria-hidden="true"></span>
                                <svg class="ms-2" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </button>
                        </div>
                    </form>

                    <div class="mt-5 text-center text-muted" style="font-size: 0.8rem;">
                        &copy; {{ date('Y') }} ArrhythmiaAI.<br>Todos los derechos reservados.
                    </div>
                </div>
            </div>

            {{-- Panel Derecho (Imagen e Información) --}}
            <div class="col-12 col-md-7 col-lg-8 d-none d-md-block p-0 login-right">
                <div class="overlay"></div>
                <img src="{{ asset('images/auth/electrocardiograma.jpg') }}" alt="Electrocardiograma">

                <div class="login-right-content">
                    <div style="max-width: 600px;">
                        <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-3 fw-bold"
                            style="letter-spacing: 1px;">TECNOLOGÍA AVANZADA</span>
                        <h1 class="display-4 fw-bold text-white mb-4" style="line-height: 1.2;">
                            Precisión diagnóstica impulsada por Inteligencia Artificial.
                        </h1>
                        <p class="text-white-50 fs-5 mb-0" style="line-height: 1.6;">
                            ArrhythmiaAI asiste a los profesionales de la salud en la detección temprana y clasificación
                            precisa de anomalías cardíacas a través de análisis profundo de ECG.
                        </p>

                        <div class="mt-5 d-flex gap-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="text-white">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-white mb-0 fw-bold">Análisis Rápido</h5>
                                    <span class="text-white-50 small">Resultados en segundos</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="text-white">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-white mb-0 fw-bold">Alta Seguridad</h5>
                                    <span class="text-white-50 small">Datos clínicos protegidos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <script>
        document.getElementById('login-form')?.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            const spinner = this.querySelector('.btn-spinner');
            const label = this.querySelector('.btn-text');

            if (!button || !spinner || !label) {
                return;
            }

            button.disabled = true;
            spinner.classList.remove('d-none');
            label.textContent = 'Ingresando...';
        });
    </script>
</x-guest-layout>
