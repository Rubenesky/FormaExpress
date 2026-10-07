<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'empleados';

    /**
     * Cursos asignados a este empleado (tabla asignaciones_curso).
     */
    public function cursosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'asignaciones_curso', 'empleado_id', 'curso_id')
            ->withPivot('fecha_asignacion')
            ->withTimestamps();
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class, 'empleado_id');
    }

    /**
     * Insignias conseguidas (tabla empleado_insignia); la fecha está en pivot->created_at.
     */
    public function insignias(): BelongsToMany
    {
        return $this->belongsToMany(Insignia::class, 'empleado_insignia', 'empleado_id', 'insignia_id')
            ->withTimestamps();
    }

    public function tieneAsignado(Curso $curso): bool
    {
        return $this->cursosAsignados()->whereKey($curso->id)->exists();
    }

    /**
     * Lecciones del curso que el empleado aún no ha completado.
     */
    public function leccionesPendientesEn(Curso $curso): int
    {
        return $curso->lecciones()
            ->whereDoesntHave('completadaPor', fn ($q) => $q->whereKey($this->id))
            ->count();
    }

    /**
     * Lecciones que el empleado ha completado (tabla progreso_lecciones).
     */
    public function leccionesCompletadas(): BelongsToMany
    {
        return $this->belongsToMany(Leccion::class, 'progreso_lecciones', 'empleado_id', 'leccion_id')
            ->withPivot('fecha_finalizacion')
            ->withTimestamps();
    }

    /**
     * Marca la lección como completada. Idempotente: si ya lo estaba, el
     * índice único (empleado_id, leccion_id) hace que no se inserte nada y
     * se conserva la fecha de la primera vez.
     */
    public function completarLeccion(Leccion $leccion): void
    {
        $ahora = now();

        DB::table('progreso_lecciones')->insertOrIgnore([
            'empleado_id' => $this->id,
            'leccion_id' => $leccion->id,
            'fecha_finalizacion' => $ahora,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
