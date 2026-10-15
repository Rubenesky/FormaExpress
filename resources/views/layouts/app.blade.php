<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 desde CDN: sin paso de build de JS/CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- Identidad de FormaExpress: solo se sobreescriben variables y clases de
         Bootstrap. Grises, rojo de error y verde de éxito siguen siendo los suyos. -->
    <style>
        :root {
            /* Primario: verde azulado oscuro (sustituye al azul de Bootstrap). */
            --fx-primario: #0f5e66;
            --fx-primario-rgb: 15, 94, 102;
            --fx-primario-oscuro: #0b4a51;
            --fx-primario-claro: #e3eff0;
            /* Acento coral, con moderación. De relleno lleva texto oscuro;
               como texto sobre blanco se usa la variante oscura. */
            --fx-acento: #f26b5b;
            --fx-acento-rgb: 242, 107, 91;
            --fx-acento-hover: #f4857a;
            --fx-acento-oscuro: #b8432f;

            --bs-primary: var(--fx-primario);
            --bs-primary-rgb: var(--fx-primario-rgb);
            --bs-link-color: var(--fx-primario);
            --bs-link-color-rgb: var(--fx-primario-rgb);
            --bs-link-hover-color: var(--fx-primario-oscuro);
            --bs-link-hover-color-rgb: 11, 74, 81;
            /* Variante clara del primario (alert-primary): Bootstrap la trae en azul fijo. */
            --bs-primary-bg-subtle: var(--fx-primario-claro);
            --bs-primary-text-emphasis: #073a40;
            --bs-primary-border-subtle: #a9cbcf;
        }

        .btn-primary {
            --bs-btn-bg: var(--fx-primario);
            --bs-btn-border-color: var(--fx-primario);
            --bs-btn-hover-bg: var(--fx-primario-oscuro);
            --bs-btn-hover-border-color: var(--fx-primario-oscuro);
            --bs-btn-active-bg: var(--fx-primario-oscuro);
            --bs-btn-active-border-color: var(--fx-primario-oscuro);
            --bs-btn-disabled-bg: var(--fx-primario);
            --bs-btn-disabled-border-color: var(--fx-primario);
            --bs-btn-focus-shadow-rgb: var(--fx-primario-rgb);
        }

        .btn-outline-primary {
            --bs-btn-color: var(--fx-primario);
            --bs-btn-border-color: var(--fx-primario);
            --bs-btn-hover-bg: var(--fx-primario);
            --bs-btn-hover-border-color: var(--fx-primario);
            --bs-btn-active-bg: var(--fx-primario-oscuro);
            --bs-btn-active-border-color: var(--fx-primario-oscuro);
            --bs-btn-disabled-color: var(--fx-primario);
            --bs-btn-disabled-border-color: var(--fx-primario);
            --bs-btn-focus-shadow-rgb: var(--fx-primario-rgb);
        }

        /* Acción principal del empleado (completar lección, enviar evaluación). */
        .btn-acento {
            --bs-btn-color: #212529;
            --bs-btn-bg: var(--fx-acento);
            --bs-btn-border-color: var(--fx-acento);
            --bs-btn-hover-color: #212529;
            --bs-btn-hover-bg: var(--fx-acento-hover);
            --bs-btn-hover-border-color: var(--fx-acento-hover);
            --bs-btn-active-color: #212529;
            --bs-btn-active-bg: var(--fx-acento-hover);
            --bs-btn-active-border-color: var(--fx-acento-hover);
            --bs-btn-focus-shadow-rgb: var(--fx-acento-rgb);
            font-weight: 600;
        }

        .form-control:focus,
        .form-select:focus,
        .form-check-input:focus {
            border-color: var(--fx-primario);
            box-shadow: 0 0 0 .25rem rgba(var(--fx-primario-rgb), .25);
        }

        .form-check-input:checked {
            background-color: var(--fx-primario);
            border-color: var(--fx-primario);
        }

        .pagination {
            --bs-pagination-active-bg: var(--fx-primario);
            --bs-pagination-active-border-color: var(--fx-primario);
            --bs-pagination-focus-box-shadow: 0 0 0 .25rem rgba(var(--fx-primario-rgb), .25);
        }

        .dropdown-menu {
            --bs-dropdown-link-active-bg: var(--fx-primario);
        }

        /* Barra de navegación: blanca, con el primario en el borde y en el enlace activo. */
        .fx-navbar {
            border-bottom: 3px solid var(--fx-primario);
            --bs-navbar-hover-color: var(--fx-primario);
            --bs-navbar-active-color: var(--fx-primario);
        }

        .fx-navbar .nav-link.active {
            font-weight: 600;
        }

        /* Wordmark: "Forma" en el primario y "Express" en el acento (variante legible sobre blanco). */
        .fx-marca {
            font-weight: 700;
            letter-spacing: -.01em;
        }

        .fx-marca-forma {
            color: var(--fx-primario);
        }

        .fx-marca-express {
            color: var(--fx-acento-oscuro);
        }

        /* Barras de progreso en el primario (Bootstrap las pinta en su azul). */
        .progress {
            --bs-progress-bar-bg: var(--fx-primario);
        }

        /* Tarjetas de "Mis cursos" y "Mis logros". */
        .fx-tarjeta {
            border-top: 4px solid var(--fx-primario);
        }

        .fx-tarjeta-conseguida {
            border-top-color: var(--fx-acento);
        }

        /* Aviso con el acento (insignias nuevas): coral claro y texto oscuro,
           mismo patrón que alert-primary. Los enlaces conservan el primario. */
        .alert-acento {
            --bs-alert-color: #5c2016;
            --bs-alert-bg: #fde4e0;
            --bs-alert-border-color: #f6b3a9;
            --bs-alert-link-color: #5c2016;
        }

        /* Coral de relleno: siempre con texto oscuro. */
        .fx-badge-acento {
            background-color: var(--fx-acento);
            color: #212529;
        }

        /* Iconos sobre fondo blanco: el coral en su variante oscura. */
        .fx-icono-primario {
            color: var(--fx-primario);
        }

        .fx-icono-acento {
            color: var(--fx-acento-oscuro);
        }

        .fx-icono {
            width: 1em;
            height: 1em;
            vertical-align: -.125em;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fx-navbar">
            <div class="container">
                <a class="navbar-brand fx-marca" href="{{ url('/') }}"><span class="fx-marca-forma">Forma</span><span class="fx-marca-express">Express</span></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @can('estudiar')
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('mis-cursos.*', 'mis-lecciones.*')) active @endif" href="{{ route('mis-cursos.index') }}">Mis cursos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('mis-logros.*')) active @endif" href="{{ route('mis-logros.index') }}">Mis logros</a>
                            </li>
                        @endcan
                        @can('consultar')
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('consulta.*')) active @endif" href="{{ route('consulta.index') }}">Consulta</a>
                            </li>
                        @endcan
                        @can('administrar')
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('cursos.*')) active @endif" href="{{ route('cursos.index') }}">Cursos</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link @if (request()->routeIs('registro-consultas.*')) active @endif" href="{{ route('registro-consultas.index') }}">Registro de consultas</a>
                            </li>
                        @endcan
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @if (session('status') || session('error'))
                <div class="container">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
