<?php

namespace App\Http\Controllers;

use App\Http\Requests\EvaluacionRequest;
use App\Models\Curso;
use App\Models\Evaluacion;
use Illuminate\Database\QueryException;

/**
 * Evaluación de un curso (como mucho una por curso). Solo administradores.
 */
class EvaluacionController extends Controller
{
    public function show(Curso $curso)
    {
        $evaluacion = $curso->evaluacion()
            ->withCount('resultados')
            ->with('preguntas.opciones')
            ->first();

        return view('evaluaciones.show', compact('curso', 'evaluacion'));
    }

    public function create(Curso $curso)
    {
        if ($curso->evaluacion()->exists()) {
            return redirect()->route('cursos.evaluacion.show', $curso);
        }

        $evaluacion = new Evaluacion(['titulo' => "Evaluación: {$curso->titulo}", 'nota_minima' => '8.00']);

        return view('evaluaciones.create', compact('curso', 'evaluacion'));
    }

    public function store(EvaluacionRequest $request, Curso $curso)
    {
        try {
            $curso->evaluacion()->create($request->validated());
        } catch (QueryException $e) {
            // 23000: el índice único impide una segunda evaluación en el curso.
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            return redirect()->route('cursos.evaluacion.show', $curso)
                ->with('error', 'Este curso ya tiene una evaluación.');
        }

        return redirect()->route('cursos.evaluacion.show', $curso)->with('status', 'Evaluación creada.');
    }

    public function edit(Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->firstOrFail();

        return view('evaluaciones.edit', compact('curso', 'evaluacion'));
    }

    public function update(EvaluacionRequest $request, Curso $curso)
    {
        $curso->evaluacion()->firstOrFail()->update($request->validated());

        return redirect()->route('cursos.evaluacion.show', $curso)->with('status', 'Evaluación actualizada.');
    }

    /**
     * Página de confirmación: avisa de las preguntas que se borrarán.
     */
    public function confirmarEliminacion(Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->withCount(['preguntas', 'resultados'])->firstOrFail();

        return view('evaluaciones.eliminar', compact('curso', 'evaluacion'));
    }

    public function destroy(Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->firstOrFail();

        try {
            // La BD borra en cascada sus preguntas y opciones.
            $evaluacion->delete();
        } catch (QueryException $e) {
            // 23000: FK RESTRICT de resultados_evaluacion (hay intentos de empleados).
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            return redirect()->route('cursos.evaluacion.show', $curso)
                ->with('error', 'No se puede eliminar la evaluación: hay resultados de empleados.');
        }

        return redirect()->route('cursos.evaluacion.show', $curso)->with('status', 'Evaluación eliminada junto con sus preguntas.');
    }
}
