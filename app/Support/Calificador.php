<?php

namespace App\Support;

/**
 * Cálculos puros de nota y progreso: sin Eloquent, sin base de datos.
 */
class Calificador
{
    /**
     * Nota de una evaluación: aciertos ÷ total × 10, sin penalización por
     * fallo, con 2 decimales. 0 si la evaluación no tiene preguntas.
     */
    public static function nota(int $aciertos, int $total): float
    {
        return $total > 0 ? round($aciertos / $total * 10, 2) : 0.0;
    }

    /**
     * ¿La nota alcanza la nota mínima? Se compara en céntimos enteros para
     * que el límite exacto no dependa de decimales flotantes.
     */
    public static function aprueba(float $nota, float $notaMinima): bool
    {
        return (int) round($nota * 100) >= (int) round($notaMinima * 100);
    }

    /**
     * % de lecciones completadas de un curso (0-100, entero). 0 si el curso
     * no tiene lecciones.
     */
    public static function progreso(int $completadas, int $total): int
    {
        return $total > 0 ? (int) round($completadas / $total * 100) : 0;
    }
}
