<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro RGPD de consultas: quién (encargado o administrador) abrió la ficha
 * de qué empleado y cuándo. Se escribe en ConsultaController::ficha() y solo se
 * inserta: la app no tiene rutas para editarlo ni borrarlo.
 */
#[Fillable(['consultor_id', 'consultado_id', 'consultado_en'])]
class RegistroConsulta extends Model
{
    protected $table = 'registro_consultas';

    // Solo hay consultado_en (el registro no se edita, no tiene created_at/updated_at).
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'consultado_en' => 'datetime',
        ];
    }

    public function consultor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultor_id');
    }

    public function consultado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultado_id');
    }
}
