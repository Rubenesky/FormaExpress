<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

/**
 * El empleado hace la evaluación de un curso y ve su resultado.
 * Acceso: Gate hacer-evaluacion (formulario y envío) y ver-curso (resultado).
 * La corrección se hace en el servidor: es_correcta nunca llega al navegador.
 */
class HacerEvaluacionController extends Controller
{
    public function formulario(Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->with('preguntas.opciones')->firstOrFail();

        return view('mis-cursos.evaluacion', compact('curso', 'evaluacion'));
    }

    public function enviar(Request $request, Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->with('preguntas')->firstOrFail();

        // Todas las preguntas son obligatorias; un envío incompleto no cuenta como intento.
        $reglas = ['respuestas' => ['required', 'array']];
        foreach ($evaluacion->preguntas as $pregunta) {
            $reglas["respuestas.{$pregunta->id}"] = ['required', 'integer'];
        }

        $validados = $request->validate($reglas, [
            'respuestas.required' => 'Responde todas las preguntas.',
            'respuestas.*.required' => 'Responde esta pregunta.',
            'respuestas.*.integer' => 'Respuesta no válida.',
        ]);

        $correccion = $evaluacion->corregir($validados['respuestas']);

        $evaluacion->resultados()->create([
            'empleado_id' => $request->user()->id,
            'nota' => $correccion['nota'],
            'aprobado' => $evaluacion->aprueba($correccion['nota']),
        ]);

        return redirect()->route('mis-cursos.evaluacion.resultado', $curso)
            ->with('falladas', $correccion['falladas'])
            ->with('aciertos', $correccion['aciertos']);
    }

    public function resultado(Request $request, Curso $curso)
    {
        $evaluacion = $curso->evaluacion()->firstOrFail();

        $intentos = $evaluacion->resultados()
            ->where('empleado_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        abort_if($intentos->isEmpty(), 404);

        // Solo justo después de enviar: qué preguntas falló (sin decir cuál era la correcta).
        $falladas = $evaluacion->preguntas()->whereKey(session('falladas', []))->get();

        return view('mis-cursos.resultado', [
            'curso' => $curso,
            'evaluacion' => $evaluacion,
            'resultado' => $intentos->first(),
            'intentos' => $intentos,
            'reciente' => session()->has('falladas'),
            'aciertos' => session('aciertos'),
            'falladas' => $falladas,
            'totalPreguntas' => $evaluacion->preguntas()->count(),
        ]);
    }
}
