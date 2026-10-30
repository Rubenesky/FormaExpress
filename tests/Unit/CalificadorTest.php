<?php

namespace Tests\Unit;

use App\Support\Calificador;
use PHPUnit\Framework\TestCase;

class CalificadorTest extends TestCase
{
    // --- nota() ---

    public function test_nota_calcula_el_porcentaje_de_aciertos_sobre_diez(): void
    {
        $this->assertSame(8.0, Calificador::nota(4, 5));
    }

    public function test_nota_con_decimales_periodicos_redondea_a_dos_decimales(): void
    {
        // 1/3 * 10 = 3.333... -> 3.33
        $this->assertSame(3.33, Calificador::nota(1, 3));
    }

    public function test_nota_sin_preguntas_es_cero(): void
    {
        $this->assertSame(0.0, Calificador::nota(0, 0));
    }

    public function test_nota_con_todo_fallado_es_cero(): void
    {
        $this->assertSame(0.0, Calificador::nota(0, 5));
    }

    public function test_nota_con_todo_acertado_es_diez(): void
    {
        $this->assertSame(10.0, Calificador::nota(5, 5));
    }

    // --- aprueba() ---

    public function test_aprueba_con_nota_igual_a_la_minima(): void
    {
        $this->assertTrue(Calificador::aprueba(8.0, 8.0));
    }

    public function test_aprueba_falla_por_una_centesima(): void
    {
        $this->assertFalse(Calificador::aprueba(7.99, 8.0));
    }

    public function test_aprueba_evita_el_error_de_coma_flotante_en_el_limite(): void
    {
        // 8.1 - 0.1 no es exactamente 8.0 en coma flotante (da 7.999999999999999),
        // pero en céntimos enteros sí debe contar como aprobado.
        $this->assertTrue(Calificador::aprueba(8.1 - 0.1, 8.0));
    }

    // --- progreso() ---

    public function test_progreso_sin_lecciones_es_cero(): void
    {
        $this->assertSame(0, Calificador::progreso(0, 0));
    }

    public function test_progreso_redondea_decimales_periodicos(): void
    {
        // 1/3 * 100 = 33.33... -> 33
        $this->assertSame(33, Calificador::progreso(1, 3));
    }

    public function test_progreso_completo_es_cien(): void
    {
        $this->assertSame(100, Calificador::progreso(5, 5));
    }
}
