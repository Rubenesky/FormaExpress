<?php

namespace App\Support;

use App\Models\Certificado;
use App\Models\Insignia;
use App\Models\ResultadoEvaluacion;
use Illuminate\Support\Facades\DB;

/**
 * Certificados e insignias automáticos (módulo 3). Se llama tras guardar cada
 * intento de evaluación, dentro de la misma transacción.
 *
 * Todo es idempotente: los índices únicos (empleado, curso) de certificados y
 * (empleado, insignia) de empleado_insignia hacen que insertOrIgnore no duplique
 * nada ni cambie la fecha de lo ya conseguido.
 */
class Logros
{
    /**
     * @return array{certificado: ?Certificado, insignias: list<string>} lo conseguido en este intento
     */
    public static function registrar(ResultadoEvaluacion $resultado): array
    {
        $empleadoId = $resultado->empleado_id;
        $cursoId = $resultado->evaluacion->curso_id;
        $ahora = now();

        // Certificado: con el primer aprobado de la evaluación del curso.
        $certificado = null;
        if ($resultado->aprobado) {
            $emitido = DB::table('certificados')->insertOrIgnore([
                'empleado_id' => $empleadoId,
                'curso_id' => $cursoId,
                'fecha_emision' => $ahora->toDateString(),
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            if ($emitido) {
                $certificado = Certificado::where('empleado_id', $empleadoId)->where('curso_id', $cursoId)->first();
            }
        }

        // Insignias: se comprueban todas en cada intento y solo se guardan las nuevas.
        $certificadosTotales = DB::table('certificados')->where('empleado_id', $empleadoId)->count();

        $hitos = self::hitos(
            aprobado: (bool) $resultado->aprobado,
            nota: (float) $resultado->nota,
            esPrimerIntento: $resultado->aprobado && self::esPrimerIntento($resultado),
            certificadosTotales: $certificadosTotales,
        );

        // Del código de cada hito a su fila del catálogo.
        $ids = Insignia::whereIn('codigo', $hitos)->pluck('id', 'codigo');

        $nuevas = [];
        foreach ($hitos as $hito) {
            if (! isset($ids[$hito])) {
                continue;
            }

            $concedida = DB::table('empleado_insignia')->insertOrIgnore([
                'empleado_id' => $empleadoId,
                'insignia_id' => $ids[$hito],
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            if ($concedida) {
                $nuevas[] = $hito;
            }
        }

        return ['certificado' => $certificado, 'insignias' => $nuevas];
    }

    /**
     * Qué insignias corresponden a un resultado, sin tocar la base de datos.
     * $certificadosTotales incluye el certificado de este intento, si se
     * acaba de emitir (se cuenta después de intentar emitirlo).
     *
     * @return list<string> códigos de Insignia (pueden repetirse hitos ya
     *                      conseguidos antes: registrar() filtra los nuevos)
     */
    public static function hitos(bool $aprobado, float $nota, bool $esPrimerIntento, int $certificadosTotales): array
    {
        $hitos = [];

        if ($aprobado && $esPrimerIntento) {
            $hitos[] = Insignia::PRIMER_INTENTO;
        }

        if ((int) round($nota * 100) === 1000) {
            $hitos[] = Insignia::NOTA_MAXIMA;
        }

        if ($certificadosTotales >= 1) {
            $hitos[] = Insignia::PRIMER_CERTIFICADO;
        }
        if ($certificadosTotales >= 5) {
            $hitos[] = Insignia::CINCO_CERTIFICADOS;
        }

        return $hitos;
    }

    /**
     * ¿Es el primer intento del empleado en esta evaluación (sin suspensos previos)?
     */
    private static function esPrimerIntento(ResultadoEvaluacion $resultado): bool
    {
        return ! ResultadoEvaluacion::where('evaluacion_id', $resultado->evaluacion_id)
            ->where('empleado_id', $resultado->empleado_id)
            ->where('id', '<', $resultado->id)
            ->exists();
    }
}
