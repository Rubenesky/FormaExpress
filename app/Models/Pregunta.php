<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pregunta de opción única: mínimo 2 opciones y exactamente 1 correcta
 * (lo valida PreguntaRequest).
 */
#[Fillable(['enunciado'])]
class Pregunta extends Model
{
    /** Huecos de opción que muestra el formulario del administrador. */
    public const MAX_OPCIONES = 6;

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(Opcion::class)->orderBy('id');
    }
}
