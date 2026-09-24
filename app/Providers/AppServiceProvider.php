<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gestión de cursos y lecciones: solo administradores.
        // Se usa en rutas (middleware can:administrar) y en vistas (@can).
        Gate::define('administrar', fn (User $user) => $user->rol === 'administrador');

        Paginator::useBootstrapFive();
    }
}
