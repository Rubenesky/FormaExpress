<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Catálogo de insignias (tabla insignias_catalogo). Las concedidas a cada
 * empleado están en empleado_insignia (User::insignias()).
 *
 * "codigo" es la clave estable con la que Logros las concede: las constantes
 * de abajo tienen que coincidir con las filas que crea la migración.
 */
class Insignia extends Model
{
    public const PRIMER_CERTIFICADO = 'primer-certificado';

    public const CINCO_CERTIFICADOS = 'cinco-certificados';

    public const NOTA_MAXIMA = 'nota-maxima';

    public const PRIMER_INTENTO = 'primer-intento';

    protected $table = 'insignias_catalogo';

    public $timestamps = false;

    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empleado_insignia', 'insignia_id', 'empleado_id')
            ->withTimestamps();
    }
}
