<?php

use App\Http\Controllers\CursoController;
use App\Http\Controllers\LeccionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

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
});
