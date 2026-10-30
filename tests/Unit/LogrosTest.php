<?php

namespace Tests\Unit;

use App\Models\Insignia;
use App\Support\Logros;
use PHPUnit\Framework\TestCase;

class LogrosTest extends TestCase
{
    public function test_primer_intento_aprobado_concede_la_insignia_a_la_primera(): void
    {
        $hitos = Logros::hitos(aprobado: true, nota: 7.0, esPrimerIntento: true, certificadosTotales: 1);

        $this->assertContains(Insignia::PRIMER_INTENTO, $hitos);
    }

    public function test_no_concede_a_la_primera_si_hubo_intentos_previos(): void
    {
        // Aprueba, pero no es el primer intento: no debe concederse "a la primera".
        $hitos = Logros::hitos(aprobado: true, nota: 7.0, esPrimerIntento: false, certificadosTotales: 1);

        $this->assertNotContains(Insignia::PRIMER_INTENTO, $hitos);
    }

    public function test_nota_maxima_solo_se_concede_con_un_diez_exacto(): void
    {
        $hitos = Logros::hitos(aprobado: true, nota: 9.99, esPrimerIntento: false, certificadosTotales: 0);

        $this->assertNotContains(Insignia::NOTA_MAXIMA, $hitos);
    }

    public function test_nota_maxima_se_concede_con_un_diez(): void
    {
        $hitos = Logros::hitos(aprobado: true, nota: 10.0, esPrimerIntento: false, certificadosTotales: 1);

        $this->assertContains(Insignia::NOTA_MAXIMA, $hitos);
    }

    public function test_primer_certificado_se_concede_con_un_certificado(): void
    {
        $hitos = Logros::hitos(aprobado: true, nota: 7.0, esPrimerIntento: false, certificadosTotales: 1);

        $this->assertContains(Insignia::PRIMER_CERTIFICADO, $hitos);
        $this->assertNotContains(Insignia::CINCO_CERTIFICADOS, $hitos);
    }

    public function test_cinco_certificados_se_concede_a_partir_del_quinto(): void
    {
        $hitos = Logros::hitos(aprobado: true, nota: 7.0, esPrimerIntento: false, certificadosTotales: 5);

        $this->assertContains(Insignia::PRIMER_CERTIFICADO, $hitos);
        $this->assertContains(Insignia::CINCO_CERTIFICADOS, $hitos);
    }

    public function test_cuatro_certificados_no_concede_la_insignia_de_cinco(): void
    {
        $hitos = Logros::hitos(aprobado: true, nota: 7.0, esPrimerIntento: false, certificadosTotales: 4);

        $this->assertNotContains(Insignia::CINCO_CERTIFICADOS, $hitos);
    }

    public function test_sin_certificados_no_concede_ninguna_insignia_de_certificados(): void
    {
        $hitos = Logros::hitos(aprobado: false, nota: 5.0, esPrimerIntento: false, certificadosTotales: 0);

        $this->assertNotContains(Insignia::PRIMER_CERTIFICADO, $hitos);
        $this->assertNotContains(Insignia::CINCO_CERTIFICADOS, $hitos);
    }

    public function test_suspender_sin_certificados_previos_no_concede_ninguna_insignia(): void
    {
        $hitos = Logros::hitos(aprobado: false, nota: 5.0, esPrimerIntento: false, certificadosTotales: 0);

        $this->assertSame([], $hitos);
    }
}
