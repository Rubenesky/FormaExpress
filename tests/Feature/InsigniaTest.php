<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Insignia;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InsigniaTest extends TestCase
{
    use RefreshDatabase;

    private User $empleado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empleado = User::factory()->create();
    }

    /**
     * Curso asignado al empleado, con su lección completada y una evaluación de
     * $preguntas preguntas (nota mínima 5).
     *
     * @return array{0: Curso, 1: array<int, array{0: int, 1: int}>} [curso, pregunta_id => [correcta, incorrecta]]
     */
    private function curso(string $titulo, int $preguntas = 2): array
    {
        $curso = Curso::create(['titulo' => $titulo]);
        $leccion = $curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $curso->empleadosAsignados()->attach($this->empleado->id, ['fecha_asignacion' => today()->toDateString()]);
        $this->empleado->completarLeccion($leccion);

        $evaluacion = $curso->evaluacion()->create(['titulo' => 'Test', 'nota_minima' => 5]);
        $opciones = [];
        for ($i = 1; $i <= $preguntas; $i++) {
            $pregunta = $evaluacion->preguntas()->create(['enunciado' => "P{$i}"]);
            $opciones[$pregunta->id] = [
                $pregunta->opciones()->create(['texto' => 'Bien', 'es_correcta' => true])->id,
                $pregunta->opciones()->create(['texto' => 'Mal', 'es_correcta' => false])->id,
            ];
        }

        return [$curso, $opciones];
    }

    /** Responde acertando las $aciertos primeras preguntas. */
    private function responder(Curso $curso, array $opciones, int $aciertos)
    {
        $respuestas = [];
        $i = 0;
        foreach ($opciones as $preguntaId => [$bien, $mal]) {
            $respuestas[$preguntaId] = $i++ < $aciertos ? $bien : $mal;
        }

        return $this->actingAs($this->empleado)->post("/mis-cursos/{$curso->id}/evaluacion", ['respuestas' => $respuestas]);
    }

    /** @return list<string> */
    private function hitos(): array
    {
        return DB::table('insignias')->where('empleado_id', $this->empleado->id)->orderBy('hito')->pluck('hito')->all();
    }

    public function test_aprobar_al_primer_intento_da_primer_intento_y_primer_certificado(): void
    {
        [$curso, $opciones] = $this->curso('Caja');

        $this->followingRedirects()->responder($curso, $opciones, 1) // 5.00: aprueba, no es un 10
            ->assertSee('Nuevas insignias:')
            ->assertSee('Primer curso certificado')
            ->assertSee('A la primera');

        $this->assertSame([Insignia::PRIMER_CERTIFICADO, Insignia::PRIMER_INTENTO], $this->hitos());
    }

    public function test_aprobar_despues_de_suspender_no_da_primer_intento(): void
    {
        [$curso, $opciones] = $this->curso('Caja');

        $this->responder($curso, $opciones, 0); // suspenso
        $this->responder($curso, $opciones, 1); // aprobado

        $this->assertSame([Insignia::PRIMER_CERTIFICADO], $this->hitos());
    }

    public function test_un_10_da_nota_maxima_y_un_9_no(): void
    {
        [$curso, $opciones] = $this->curso('Caja', 10);
        $this->responder($curso, $opciones, 9);
        $this->assertNotContains(Insignia::NOTA_MAXIMA, $this->hitos());

        $this->responder($curso, $opciones, 10);
        $this->assertContains(Insignia::NOTA_MAXIMA, $this->hitos());
    }

    public function test_cinco_certificados_en_el_quinto_curso(): void
    {
        foreach (range(1, 4) as $n) {
            [$curso, $opciones] = $this->curso("Curso {$n}");
            $this->responder($curso, $opciones, 1);
        }
        $this->assertNotContains(Insignia::CINCO_CERTIFICADOS, $this->hitos());

        [$curso, $opciones] = $this->curso('Curso 5');
        $this->followingRedirects()->responder($curso, $opciones, 1)->assertSee('5 cursos certificados');

        $this->assertContains(Insignia::CINCO_CERTIFICADOS, $this->hitos());
    }

    public function test_repetir_un_hito_no_duplica_ni_vuelve_a_anunciarlo(): void
    {
        [$curso, $opciones] = $this->curso('Caja');
        $this->responder($curso, $opciones, 2);

        $this->followingRedirects()->responder($curso, $opciones, 2)
            ->assertDontSee('Nueva insignia')
            ->assertDontSee('Nuevas insignias');

        $this->assertSame(1, DB::table('insignias')->where('hito', Insignia::NOTA_MAXIMA)->count());
        $this->assertSame(3, DB::table('insignias')->count());
    }

    public function test_la_base_de_datos_impide_duplicar_una_insignia(): void
    {
        DB::table('insignias')->insert(['empleado_id' => $this->empleado->id, 'hito' => Insignia::NOTA_MAXIMA]);

        $this->expectException(QueryException::class);
        DB::table('insignias')->insert(['empleado_id' => $this->empleado->id, 'hito' => Insignia::NOTA_MAXIMA]);
    }

    public function test_mis_logros_muestra_conseguidas_y_pendientes(): void
    {
        [$curso, $opciones] = $this->curso('Caja');
        $this->responder($curso, $opciones, 2);

        $respuesta = $this->actingAs($this->empleado)->get('/mis-logros')->assertOk();

        foreach (Insignia::CATALOGO as $insignia) {
            $respuesta->assertSee($insignia['nombre']);
        }
        $this->assertSame(3, substr_count($respuesta->getContent(), '>Conseguida</span>'));
        $this->assertSame(1, substr_count($respuesta->getContent(), '>Pendiente</p>'));
    }
}
