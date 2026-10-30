<?php

namespace App\Models;

use App\Support\Calificador;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Evaluación tipo test de un curso (una por curso, índice único en curso_id).
 */
#[Fillable(['titulo', 'nota_minima'])]
class Evaluacion extends Model
{
    // Laravel deduciría "evaluacions".
    protected $table = 'evaluaciones';

    protected function casts(): array
    {
        return [
            'nota_minima' => 'decimal:2',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class)->orderBy('id');
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(ResultadoEvaluacion::class);
    }

    /**
     * Corrige un intento en el servidor.
     *
     * @param  array<int|string, mixed>  $respuestas  pregunta_id => opcion_id elegida
     * @return array{aciertos: int, total: int, nota: float, falladas: list<int>}
     */
    public function corregir(array $respuestas): array
    {
        $preguntas = $this->preguntas()->with('opciones')->get();
        $aciertos = 0;
        $falladas = [];

        foreach ($preguntas as $pregunta) {
            $correcta = $pregunta->opciones->firstWhere('es_correcta', true);
            $elegida = $respuestas[$pregunta->id] ?? null;

            // Una opción de otra pregunta (petición manipulada) cuenta como fallo.
            if ($correcta && is_scalar($elegida) && (int) $elegida === (int) $correcta->id) {
                $aciertos++;
            } else {
                $falladas[] = $pregunta->id;
            }
        }

        $total = $preguntas->count();

        return [
            'aciertos' => $aciertos,
            'total' => $total,
            'nota' => Calificador::nota($aciertos, $total),
            'falladas' => $falladas,
        ];
    }

    /**
     * ¿La nota alcanza la nota mínima?
     */
    public function aprueba(float $nota): bool
    {
        return Calificador::aprueba($nota, (float) $this->nota_minima);
    }
}
