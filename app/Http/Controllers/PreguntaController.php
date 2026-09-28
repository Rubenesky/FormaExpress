<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreguntaRequest;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Pregunta;
use Illuminate\Support\Facades\DB;

/**
 * Preguntas (y sus opciones) de la evaluación de un curso. Solo administradores.
 * Las opciones se editan dentro del formulario de la pregunta.
 */
class PreguntaController extends Controller
{
    public function create(Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->firstOrFail();
        $pregunta = new Pregunta;

        return view('preguntas.create', compact('curso', 'evaluacion', 'pregunta'));
    }

    public function store(PreguntaRequest $request, Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->firstOrFail();

        DB::transaction(function () use ($request, $evaluacion) {
            $pregunta = $evaluacion->preguntas()->create(['enunciado' => $request->validated('enunciado')]);
            $this->guardarOpciones($pregunta, $request);
        });

        return redirect()->route('cursos.evaluacion.show', $curso)->with('status', 'Pregunta creada.');
    }

    public function edit(Curso $curso, Pregunta $pregunta)
    {
        $evaluacion = $this->evaluacionDe($curso, $pregunta);

        return view('preguntas.edit', compact('curso', 'evaluacion', 'pregunta'));
    }

    public function update(PreguntaRequest $request, Curso $curso, Pregunta $pregunta)
    {
        $this->evaluacionDe($curso, $pregunta);

        DB::transaction(function () use ($request, $pregunta) {
            $pregunta->update(['enunciado' => $request->validated('enunciado')]);
            $this->guardarOpciones($pregunta, $request);
        });

        return redirect()->route('cursos.evaluacion.show', $curso)->with('status', 'Pregunta actualizada.');
    }

    public function confirmarEliminacion(Curso $curso, Pregunta $pregunta)
    {
        $evaluacion = $this->evaluacionDe($curso, $pregunta)->loadCount('resultados');

        return view('preguntas.eliminar', compact('curso', 'evaluacion', 'pregunta'));
    }

    public function destroy(Curso $curso, Pregunta $pregunta)
    {
        $this->evaluacionDe($curso, $pregunta);

        // La BD borra en cascada sus opciones.
        $pregunta->delete();

        return redirect()->route('cursos.evaluacion.show', $curso)->with('status', 'Pregunta eliminada.');
    }

    /**
     * Sustituye las opciones de la pregunta por las rellenas en el formulario.
     * Ningún resultado apunta a las opciones, así que reemplazarlas es seguro.
     */
    private function guardarOpciones(Pregunta $pregunta, PreguntaRequest $request): void
    {
        $correcta = (int) $request->validated('correcta');

        $pregunta->opciones()->delete();

        foreach ($request->opcionesRellenas() as $posicion => $texto) {
            $pregunta->opciones()->create(['texto' => $texto, 'es_correcta' => $posicion === $correcta]);
        }
    }

    /**
     * Evaluación del curso; 404 si no existe o si la pregunta es de otra evaluación.
     */
    private function evaluacionDe(Curso $curso, Pregunta $pregunta): Evaluacion
    {
        $evaluacion = $curso->evaluacion()->firstOrFail();

        abort_unless((int) $pregunta->evaluacion_id === (int) $evaluacion->id, 404);

        return $evaluacion;
    }
}
