<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Insignia conseguida por un empleado. "hito" guarda uno de los códigos del
 * catálogo fijo de abajo (el catálogo como tabla propia queda para la Tarea 2).
 * Única por (empleado, hito).
 */
class Insignia extends Model
{
    public const PRIMER_CERTIFICADO = 'primer-certificado';

    public const CINCO_CERTIFICADOS = 'cinco-certificados';

    public const NOTA_MAXIMA = 'nota-maxima';

    public const PRIMER_INTENTO = 'primer-intento';

    /** @var array<string, array{nombre: string, descripcion: string}> */
    public const CATALOGO = [
        // Descripciones como instrucción: sirven igual para insignias conseguidas y pendientes.
        self::PRIMER_CERTIFICADO => ['nombre' => 'Primer curso certificado', 'descripcion' => 'Obtén tu primer certificado.'],
        self::CINCO_CERTIFICADOS => ['nombre' => '5 cursos certificados', 'descripcion' => 'Obtén 5 certificados.'],
        self::NOTA_MAXIMA => ['nombre' => 'Nota máxima', 'descripcion' => 'Saca un 10 en una evaluación.'],
        self::PRIMER_INTENTO => ['nombre' => 'A la primera', 'descripcion' => 'Aprueba una evaluación en el primer intento.'],
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empleado_id');
    }

    public function nombre(): string
    {
        return self::CATALOGO[$this->hito]['nombre'] ?? $this->hito;
    }

    public function descripcion(): string
    {
        return self::CATALOGO[$this->hito]['descripcion'] ?? '';
    }
}
