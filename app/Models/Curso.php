<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable(['titulo', 'descripcion'])]
class Curso extends Model
{
    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class)->orderBy('orden');
    }

    /**
     * Evaluación del curso (como mucho una: índice único en evaluaciones.curso_id).
     */
    public function evaluacion(): HasOne
    {
        return $this->hasOne(Evaluacion::class);
    }

    /**
     * Empleados que tienen el curso asignado (tabla asignaciones_curso).
     */
    public function empleadosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'asignaciones_curso', 'curso_id', 'empleado_id')
            ->withPivot('fecha_asignacion')
            ->withTimestamps();
    }

    /**
     * % de lecciones completadas (0-100, entero). Requiere haber cargado
     * lecciones_count y completadas_count (ver MisCursosController).
     */
    public function porcentajeProgreso(): int
    {
        if (! $this->lecciones_count) {
            return 0;
        }

        return (int) round($this->completadas_count / $this->lecciones_count * 100);
    }

    /**
     * ¿Ha completado el empleado alguna lección de este curso?
     *
     * Ninguna FK impide desasignar un curso con progreso, así que la regla
     * se comprueba aquí antes de borrar la asignación.
     */
    public function tieneProgresoDe(User $empleado): bool
    {
        return DB::table('progreso_lecciones')
            ->join('lecciones', 'lecciones.id', '=', 'progreso_lecciones.leccion_id')
            ->where('lecciones.curso_id', $this->id)
            ->where('progreso_lecciones.empleado_id', $empleado->id)
            ->exists();
    }
}
