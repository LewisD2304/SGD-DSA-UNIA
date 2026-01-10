<div>
    <div class="login-page">
        <div class="login-left">
            <div class="waves-container">
                <svg class="wave-1" viewBox="0 0 400 800" preserveAspectRatio="none">
                    <path d="M400,0 L400,800 L200,800 Q100,700 150,600 Q200,500 100,400 Q0,300 100,200 Q200,100 150,0 Z" fill="rgba(0,0,0,0.1)"></path>
                </svg>
                <svg class="wave-2" viewBox="0 0 400 800" preserveAspectRatio="none">
                    <path d="M400,0 L400,800 L250,800 Q150,700 200,600 Q250,500 150,400 Q50,300 150,200 Q250,100 200,0 Z" fill="rgba(0,0,0,0.15)"></path>
                </svg>
            </div>

            <div class="dots-pattern">
                <span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span>
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="login-left-content">
                <div class="badge-top">
                    <i class="ki-outline ki-document fs-4 text-white"></i>
                    UNIA - Servicios Académicos
                </div>

                <div class="mt-4">
                    <h1 class="login-title">Sistema de <br>Gestión <br>Documentaria</h1>
                    <p class="login-subtitle">Plataforma integral para la administración y seguimiento de documentos académicos</p>
                </div>

                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="ki-outline ki-file-added fs-2 text-white"></i>
                        </div>
                        <div>
                            <div class="feature-text">Registro de Documentos</div>
                            <div class="feature-desc">Ingreso y clasificación de trámites</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="ki-outline ki-folder fs-2 text-white"></i>
                        </div>
                        <div>
                            <div class="feature-text">Gestión de Expedientes</div>
                            <div class="feature-desc">Organización y archivo digital</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="ki-outline ki-magnifier fs-2 text-white"></i>
                        </div>
                        <div>
                            <div class="feature-text">Seguimiento de Trámites</div>
                            <div class="feature-desc">Consulta el estado en tiempo real</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="ki-outline ki-shield-tick fs-2 text-white"></i>
                        </div>
                        <div>
                            <div class="feature-text">Seguridad Documental</div>
                            <div class="feature-desc">Protección y respaldo de información</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="corner-decoration top-right"></div>
            <div class="corner-decoration bottom-left"></div>

            <div class="login-card">
                <div class="login-card-header">
                    <div class="login-logo-wrap">
                        <img src="{{ asset('media/logo-unia.webp') }}" alt="UNIA" class="login-logo">
                    </div>
                    <h3 class="login-heading">Sistema de Gestión Documentaria</h3>
                    <p class="login-subheading">Dirección de Servicios Académicos</p>
                    <div class="decor-divider">
                        <span class="decor-line"></span>
                        <span class="decor-dot"></span>
                        <span class="decor-line"></span>
                    </div>
                    <p class="login-lead">Ingresa tus credenciales para acceder</p>
                </div>

                <div class="login-body">
                    <form method="POST" wire:submit.prevent="iniciarSesion" autocomplete="off">
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="usuario"><span class="dot"></span> Usuario / Correo electrónico</label>
                            <div class="input-wrap">
                                <div class="input-icon"><i class="ki-outline ki-user fs-2"></i></div>
                                <input type="text" id="usuario" class="form-control-custom @error('usuario') is-invalid @enderror" wire:model.defer="usuario" placeholder="usuario@unia.edu.pe">
                            </div>
                            @error('usuario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password"><span class="dot"></span> Contraseña</label>
                            <div class="input-wrap">
                                <div class="input-icon"><i class="ki-outline ki-security-user fs-2"></i></div>
                                <input type="password" id="password" class="form-control-custom @error('password') is-invalid @enderror" wire:model.defer="password" placeholder="••••••••">
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i id="eye-icon" class="ki-outline ki-eye fs-3"></i>
                                </button>
                            </div>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>


                        <div class="form-group mt-4">
                            <button type="submit" class="btn-submit" wire:loading.attr="disabled" wire:target="iniciarSesion">
                                <span wire:loading.remove wire:target="iniciarSesion">
                                    <i class="ki-outline ki-entrance-right fs-2"></i> Ingresar al Sistema
                                </span>
                                <span wire:loading wire:target="iniciarSesion">
                                    <span class="spinner-border spinner-border-sm" role="status"></span> Ingresando...
                                </span>
                            </button>
                        </div>

                        @if(session()->has('message'))
                            <div class="alert-inline mt-2 text-center text-danger font-weight-bold">
                                {{ session('message') }}
                            </div>
                        @endif
                    </form>
                </div>
                <div class="login-footer-text">© 2026 Dirección Servicios Académicos</div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --unia-green: #008735;
            --unia-green-dark: #006828;
            --unia-bg-light: #f3f6f9;
            --text-dark: #181C32;
            --text-gray: #7E8299;
        }

        /* 1. RESET DE BODY Y HTML PARA EVITAR ESPACIOS NEGROS */
        html, body, #kt_body, #kt_app_root {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background-color: var(--unia-bg-light); /* Fondo seguro por si se hace scroll */
        }

        body { font-family: 'Inter', sans-serif; }

        /* 2. CONTENEDOR PRINCIPAL FLEXIBLE */
        .login-page {
            display: flex;
            min-height: 100vh; /* Mínimo 100vh para permitir crecimiento */
            width: 100%;
            background-color: var(--unia-bg-light);
        }

        /* === SECCIÓN IZQUIERDA (VERDE) === */
        /* Flex grow asegura que ocupe todo el alto disponible */
        .login-left {
            width: 55%;
            background-color: var(--unia-green);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 80px;
            color: #fff;
            min-height: 100vh; /* Asegura que el verde llegue hasta abajo */
        }

        .waves-container {
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 70%;
            height: 100%;
            pointer-events: none;
        }
        .wave-1, .wave-2 {
            position: absolute;
            bottom: 0; right: 0;
            height: 100%; width: 100%;
        }
        .wave-2 { opacity: 0.6; transform: translateX(20px); }

        .dots-pattern {
            position: absolute;
            top: 50%; right: 15%;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            opacity: 0.4;
        }
        .dots-pattern span {
            width: 5px; height: 5px;
            background: #fff;
            border-radius: 50%;
        }

        .login-left-content {
            position: relative;
            z-index: 2;
            max-width: 550px;
        }

        .badge-top {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            backdrop-filter: blur(5px);
        }

        .login-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 15px;
            font-style: italic;
            color: #ffffff !important;
        }
        .login-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 40px;
            line-height: 1.5;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 18px;
            border-radius: 12px;
            backdrop-filter: blur(4px);
            transition: background 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .feature-icon {
            width: 40px; height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }
        .feature-text { font-weight: 700; font-size: 1rem; }
        .feature-desc { font-size: 0.85rem; opacity: 0.8; }

        /* === SECCIÓN DERECHA (BLANCA) === */
        .login-right {
            width: 45%;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 100vh; /* Asegura altura mínima */
        }

        .corner-decoration {
            position: absolute;
            width: 100px; height: 100px;
            border: 2px dashed rgba(0, 135, 53, 0.2);
            border-radius: 50%;
        }
        .top-right { top: -50px; right: -50px; }
        .bottom-left { bottom: -50px; left: -50px; }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.08);
            padding: 0;
            z-index: 10;
            position: relative;
            overflow: hidden;
            margin: 20px; /* Margen para evitar que toque los bordes en pantallas pequeñas */
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 8px;
            background: var(--unia-green);
        }

        .login-card-header {
            text-align: center;
            padding: 40px 30px 10px;
        }
        .login-logo {
            height: 70px;
            margin-bottom: 15px;
        }
        .login-heading { font-size: 1.2rem; font-weight: 700; color: var(--text-dark); margin: 0; }
        .login-subheading { font-size: 1rem; color: var(--text-gray); margin-bottom: 10px; }

        .decor-divider { display: flex; align-items: center; justify-content: center; gap: 10px; margin: 15px 0; }
        .decor-line { height: 1px; width: 40px; background: #e0e0e0; }
        .decor-dot { width: 6px; height: 6px; background: var(--unia-green); transform: rotate(45deg); }
        .login-lead { font-size: 0.9rem; color: var(--text-gray); margin-bottom: 20px; }

        .login-body { padding: 10px 40px 40px; }

        .form-group { margin-bottom: 20px; }
        .form-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.9rem; font-weight: 600; color: var(--text-dark);
            margin-bottom: 8px;
        }
        .dot { width: 6px; height: 6px; background-color: var(--unia-green); border-radius: 50%; }

        .input-wrap {
            display: flex;
            background-color: #F5F8FA;
            border: 1px solid #E4E6EF;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .input-wrap:focus-within {
            border-color: var(--unia-green);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(0, 135, 53, 0.1);
        }
        .input-icon {
            width: 50px;
            display: flex; align-items: center; justify-content: center;
            color: var(--unia-green);
            background: rgba(0, 135, 53, 0.05);
            border-right: 1px solid #E4E6EF;
        }
        .form-control-custom {
            border: none;
            background: transparent;
            padding: 12px 15px;
            width: 100%;
            outline: none;
            color: var(--text-dark);
            font-weight: 500;
        }
        .toggle-password {
            background: none; border: none; cursor: pointer; color: var(--text-gray);
            padding: 0 15px;
        }

        .form-footer { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 25px; }
        .checkbox-container { display: flex; align-items: center; gap: 8px; color: var(--text-gray); cursor: pointer; }
        .link { color: var(--unia-green); font-weight: 600; text-decoration: none; }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--unia-green);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: background 0.3s;
        }
        .btn-submit:hover { background-color: var(--unia-green-dark); }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

        .invalid-feedback { color: #f1416c; font-size: 0.85rem; margin-top: 5px; display: block; }
        .login-footer-text { text-align: center; color: #B5B5C3; font-size: 0.8rem; padding-bottom: 20px; }

        /* Responsivo */
        @media (max-width: 992px) {
            .login-left { display: none; }
            .login-right { width: 100%; }
            .login-card { box-shadow: none; max-width: 100%; padding: 20px; }
        }
    </style>

    <script>
        function togglePassword() {
            var input = document.getElementById("password");
            var icon = document.getElementById("eye-icon");
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("ki-eye");
                icon.classList.add("ki-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("ki-eye-slash");
                icon.classList.add("ki-eye");
            }
        }
    </script>
</div>
