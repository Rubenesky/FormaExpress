<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un intento de un empleado en una evaluación (intentos ilimitados).
 * "aprobado" se guarda con la nota mínima vigente en el momento del intento.
 */
#[Fillable(['empleado_id', 'nota', 'aprobado'])]
class ResultadoEvaluacion extends Model
{
    protected $table = 'resultados_evaluacion';

    protected function casts(): array
    {
        return [
            'nota' => 'decimal:2',
            'aprobado' => 'boolean',
        ];
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }
}
