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
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
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
