<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Formación del empleado: sus cursos asignados, lecciones y progreso.
 * La autorización la hacen los Gates estudiar / ver-curso / ver-leccion en las rutas.
 */
class MisCursosController extends Controller
{
    public function index(Request $request)
    {
        $cursos = $this->conProgreso($request->user()->cursosAsignados(), $request->user())
            ->orderBy('titulo')
            ->get();

        return view('mis-cursos.index', compact('cursos'));
    }

    public function show(Request $request, Curso $curso)
    {
        $empleado = $request->user();

        $curso = $this->conProgreso(Curso::whereKey($curso->id), $empleado)->firstOrFail();
        $lecciones = $curso->lecciones()->orderBy('id')->get();
        $completadas = $this->idsCompletadas($empleado, $curso);

        // Evaluación (si tiene preguntas) e intentos de este empleado.
        $evaluacion = $curso->evaluacion()->withCount('preguntas')->first();
        $intentos = $evaluacion
            ? $evaluacion->resultados()->where('empleado_id', $empleado->id)->orderByDesc('id')->get()
            : collect();

        return view('mis-cursos.show', compact('curso', 'lecciones', 'completadas', 'evaluacion', 'intentos'));
    }

    public function leccion(Request $request, Leccion $leccion)
    {
        $curso = $leccion->curso;
        $completada = $request->user()->leccionesCompletadas()->whereKey($leccion->id)->first();

        // Lección siguiente en el mismo orden que el detalle del curso.
        $lista = $curso->lecciones()->orderBy('id')->get(['id', 'titulo']);
        $siguiente = $lista->get($lista->search(fn ($l) => $l->id === $leccion->id) + 1);

        return view('mis-cursos.leccion', compact('curso', 'leccion', 'completada', 'siguiente'));
    }

    public function completar(Request $request, Leccion $leccion)
    {
        $request->user()->completarLeccion($leccion);

        return redirect()->route('mis-lecciones.show', $leccion)->with('status', 'Lección completada.');
    }

    /**
     * Añade lecciones_count y completadas_count (del empleado) a la consulta de cursos.
     */
    private function conProgreso($cursos, User $empleado)
    {
        return $cursos->withCount([
            'lecciones',
            'lecciones as completadas_count' => fn ($q) => $q->whereHas('completadaPor', fn ($q) => $q->whereKey($empleado->id)),
        ]);
    }

    private function idsCompletadas(User $empleado, Curso $curso)
    {
        return $empleado->leccionesCompletadas()->where('lecciones.curso_id', $curso->id)->pluck('lecciones.id');
    }
}
