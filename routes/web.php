<?php

use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EvaluacionController;
use App\Http\Controllers\HacerEvaluacionController;
use App\Http\Controllers\LeccionController;
use App\Http\Controllers\MisCursosController;
use App\Http\Controllers\MisLogrosController;
use App\Http\Controllers\PreguntaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Formación del empleado: solo sus cursos asignados (Gates en AppServiceProvider).
Route::middleware('auth')->group(function () {
    Route::get('mis-cursos', [MisCursosController::class, 'index'])
        ->middleware('can:estudiar')->name('mis-cursos.index');
    Route::get('mis-cursos/{curso}', [MisCursosController::class, 'show'])
        ->middleware('can:ver-curso,curso')->name('mis-cursos.show');
    Route::get('mis-lecciones/{leccion}', [MisCursosController::class, 'leccion'])
        ->middleware('can:ver-leccion,leccion')->name('mis-lecciones.show');
    Route::post('mis-lecciones/{leccion}/completar', [MisCursosController::class, 'completar'])
        ->middleware('can:ver-leccion,leccion')->name('mis-lecciones.completar');

    // Evaluación del curso: solo con todas las lecciones completadas (Gate hacer-evaluacion).
    Route::get('mis-cursos/{curso}/evaluacion', [HacerEvaluacionController::class, 'formulario'])
        ->middleware('can:hacer-evaluacion,curso')->name('mis-cursos.evaluacion.formulario');
    Route::post('mis-cursos/{curso}/evaluacion', [HacerEvaluacionController::class, 'enviar'])
        ->middleware('can:hacer-evaluacion,curso')->name('mis-cursos.evaluacion.enviar');
    Route::get('mis-cursos/{curso}/evaluacion/resultado', [HacerEvaluacionController::class, 'resultado'])
        ->middleware('can:ver-curso,curso')->name('mis-cursos.evaluacion.resultado');

    // Certificados e insignias.
    Route::get('mis-logros', [MisLogrosController::class, 'index'])
        ->middleware('can:estudiar')->name('mis-logros.index');
    Route::get('certificados/{certificado}/pdf', [CertificadoController::class, 'pdf'])
        ->middleware('can:ver-certificado,certificado')->name('certificados.pdf');
});

// Gestión de cursos y lecciones: solo administradores (Gate "administrar").
Route::middleware(['auth', 'can:administrar'])->group(function () {
    Route::get('cursos/{curso}/eliminar', [CursoController::class, 'confirmarEliminacion'])
        ->name('cursos.eliminar');
    Route::resource('cursos', CursoController::class)->except('show');

    Route::get('cursos/{curso}/lecciones/{leccion}/eliminar', [LeccionController::class, 'confirmarEliminacion'])
        ->name('cursos.lecciones.eliminar');
    Route::resource('cursos.lecciones', LeccionController::class)
        ->except('show')
        ->parameters(['lecciones' => 'leccion']);

    // Asignación de cursos a empleados.
    Route::get('cursos/{curso}/asignaciones', [AsignacionController::class, 'index'])
        ->name('cursos.asignaciones.index');
    Route::post('cursos/{curso}/asignaciones', [AsignacionController::class, 'store'])
        ->name('cursos.asignaciones.store');
    Route::get('cursos/{curso}/asignaciones/{empleado}/eliminar', [AsignacionController::class, 'confirmarEliminacion'])
        ->name('cursos.asignaciones.eliminar');
    Route::delete('cursos/{curso}/asignaciones/{empleado}', [AsignacionController::class, 'destroy'])
        ->name('cursos.asignaciones.destroy');

    // Evaluación del curso (una por curso: sin id en la URL).
    Route::prefix('cursos/{curso}/evaluacion')->name('cursos.evaluacion.')->group(function () {
        Route::get('/', [EvaluacionController::class, 'show'])->name('show');
        Route::get('create', [EvaluacionController::class, 'create'])->name('create');
        Route::post('/', [EvaluacionController::class, 'store'])->name('store');
        Route::get('edit', [EvaluacionController::class, 'edit'])->name('edit');
        Route::put('/', [EvaluacionController::class, 'update'])->name('update');
        Route::get('eliminar', [EvaluacionController::class, 'confirmarEliminacion'])->name('eliminar');
        Route::delete('/', [EvaluacionController::class, 'destroy'])->name('destroy');

        // Preguntas de la evaluación (las opciones van dentro del formulario de la pregunta).
        Route::get('preguntas/create', [PreguntaController::class, 'create'])->name('preguntas.create');
        Route::post('preguntas', [PreguntaController::class, 'store'])->name('preguntas.store');
        Route::get('preguntas/{pregunta}/edit', [PreguntaController::class, 'edit'])->name('preguntas.edit');
        Route::put('preguntas/{pregunta}', [PreguntaController::class, 'update'])->name('preguntas.update');
        Route::get('preguntas/{pregunta}/eliminar', [PreguntaController::class, 'confirmarEliminacion'])->name('preguntas.eliminar');
        Route::delete('preguntas/{pregunta}', [PreguntaController::class, 'destroy'])->name('preguntas.destroy');
    });
});
