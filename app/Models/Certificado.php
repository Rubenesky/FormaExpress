<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Certificado de un curso: se emite automáticamente con el primer aprobado de
 * su evaluación (ver App\Support\Logros). Un curso sin evaluación no certifica.
 * Único por (empleado, curso).
 */
class Certificado extends Model
{
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Código legible que aparece en el PDF (p. ej. FX-000042).
     */
    public function codigo(): string
    {
        return 'FX-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}
