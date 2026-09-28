<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['titulo', 'contenido', 'orden'])]
class Leccion extends Model
{
    // Laravel deduciría "leccions".
    protected $table = 'lecciones';

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    /**
     * Empleados que han completado la lección (tabla progreso_lecciones).
     */
    public function completadaPor(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'progreso_lecciones', 'leccion_id', 'empleado_id')
            ->withPivot('fecha_finalizacion')
            ->withTimestamps();
    }
}
