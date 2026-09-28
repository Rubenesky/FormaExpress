<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * es_correcta queda oculto al serializar: nunca debe llegar al navegador
 * del empleado (la corrección se hace en el servidor).
 */
#[Fillable(['texto', 'es_correcta'])]
#[Hidden(['es_correcta'])]
class Opcion extends Model
{
    // Laravel deduciría "opcions".
    protected $table = 'opciones';

    protected function casts(): array
    {
        return [
            'es_correcta' => 'boolean',
        ];
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class);
    }
}
