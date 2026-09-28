<?php

namespace App\Providers;

use App\Models\Curso;
use App\Models\Leccion;
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

        // Formación del empleado. El rol "encargado" queda fuera por ahora.
        Gate::define('estudiar', fn (User $user) => $user->rol === 'empleado');
        Gate::define('ver-curso', fn (User $user, Curso $curso) => $user->rol === 'empleado' && $user->tieneAsignado($curso));
        Gate::define('ver-leccion', fn (User $user, Leccion $leccion) => $user->rol === 'empleado' && $user->tieneAsignado($leccion->curso));

        // Evaluación: curso asignado, con al menos una lección, con evaluación que tenga
        // preguntas, y todas las lecciones completadas.
        Gate::define('hacer-evaluacion', fn (User $user, Curso $curso) => $user->rol === 'empleado'
            && $user->tieneAsignado($curso)
            && $curso->lecciones()->exists()
            && $curso->evaluacion()->whereHas('preguntas')->exists()
            && $user->leccionesPendientesEn($curso) === 0);

        Paginator::useBootstrapFive();
    }
}
