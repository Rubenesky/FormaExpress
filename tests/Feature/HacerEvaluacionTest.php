<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Leccion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HacerEvaluacionTest extends TestCase
{
    use RefreshDatabase;

    private User $empleado;

    private Curso $curso;

    private Leccion $leccion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empleado = User::factory()->create();
        $this->curso = Curso::create(['titulo' => 'Apertura de caja']);
        $this->leccion = $this->curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $this->curso->empleadosAsignados()->attach($this->empleado->id, ['fecha_asignacion' => today()->toDateString()]);
    }

    private function url(string $sufijo = ''): string
    {
        return "/mis-cursos/{$this->curso->id}/evaluacion{$sufijo}";
    }

    /**
     * Crea la evaluación con $n preguntas de 3 opciones (la correcta es la 2.ª).
     *
     * @return array{0: Evaluacion, 1: array<int, int>, 2: array<int, int>} [evaluación, correctas, incorrectas] por pregunta_id
     */
    private function evaluacion(int $n, float $notaMinima = 8): array
    {
        $evaluacion = $this->curso->evaluacion()->create(['titulo' => 'Test de caja', 'nota_minima' => $notaMinima]);
        $correctas = [];
        $incorrectas = [];

        for ($i = 1; $i <= $n; $i++) {
            $pregunta = $evaluacion->preguntas()->create(['enunciado' => "Pregunta {$i}"]);
            $incorrectas[$pregunta->id] = $pregunta->opciones()->create(['texto' => "P{$i} mal A", 'es_correcta' => false])->id;
            $correctas[$pregunta->id] = $pregunta->opciones()->create(['texto' => "P{$i} bien", 'es_correcta' => true])->id;
            $pregunta->opciones()->create(['texto' => "P{$i} mal B", 'es_correcta' => false]);
        }

        return [$evaluacion, $correctas, $incorrectas];
    }

    /** Respuestas con $aciertos correctas (las primeras) y el resto incorrectas. */
    private function respuestas(array $correctas, array $incorrectas, int $aciertos): array
    {
        $respuestas = [];
        $i = 0;
        foreach ($correctas as $preguntaId => $opcionId) {
            $respuestas[$preguntaId] = $i++ < $aciertos ? $opcionId : $incorrectas[$preguntaId];
        }

        return $respuestas;
    }

    private function enviar(array $respuestas)
    {
        return $this->actingAs($this->empleado)->post($this->url(), ['respuestas' => $respuestas]);
    }

    private function completarLecciones(): void
    {
        $this->empleado->completarLeccion($this->leccion);
    }

    // --- Bloqueo ---

    public function test_con_lecciones_pendientes_la_evaluacion_esta_bloqueada(): void
    {
        [, $correctas, $incorrectas] = $this->evaluacion(2);

        $this->actingAs($this->empleado)->get("/mis-cursos/{$this->curso->id}")
            ->assertSee('Completa todas las lecciones para desbloquear la evaluación.')
            ->assertDontSee('Hacer la evaluación');

        $this->actingAs($this->empleado)->get($this->url())->assertForbidden();
        $this->enviar($this->respuestas($correctas, $incorrectas, 2))->assertForbidden();

        $this->assertDatabaseCount('resultados_evaluacion', 0);
    }

    public function test_al_completar_las_lecciones_se_desbloquea(): void
    {
        $this->evaluacion(2);
        $this->completarLecciones();

        $this->actingAs($this->empleado)->get("/mis-cursos/{$this->curso->id}")->assertSee('Hacer la evaluación');
        $this->actingAs($this->empleado)->get($this->url())->assertOk()->assertSee('Pregunta 1');
    }

    public function test_sin_evaluacion_o_sin_preguntas_no_se_ofrece_y_da_403(): void
    {
        $this->completarLecciones();

        $this->actingAs($this->empleado)->get("/mis-cursos/{$this->curso->id}")->assertDontSee('Evaluación</h2>', false);
        $this->actingAs($this->empleado)->get($this->url())->assertForbidden();

        $this->curso->evaluacion()->create(['titulo' => 'Vacía', 'nota_minima' => 8]);
        $this->actingAs($this->empleado)->get($this->url())->assertForbidden();
        $this->actingAs($this->empleado)->post($this->url(), ['respuestas' => []])->assertForbidden();
    }

    public function test_un_curso_sin_lecciones_no_ofrece_la_evaluacion(): void
    {
        $sinLecciones = Curso::create(['titulo' => 'Sin lecciones']);
        $sinLecciones->empleadosAsignados()->attach($this->empleado->id, ['fecha_asignacion' => today()->toDateString()]);
        $pregunta = $sinLecciones->evaluacion()->create(['titulo' => 'Test', 'nota_minima' => 8])
            ->preguntas()->create(['enunciado' => 'Pregunta']);
        $correcta = $pregunta->opciones()->create(['texto' => 'Sí', 'es_correcta' => true]);
        $pregunta->opciones()->create(['texto' => 'No', 'es_correcta' => false]);

        $this->actingAs($this->empleado)->get("/mis-cursos/{$sinLecciones->id}")
            ->assertSee('La evaluación estará disponible cuando el curso tenga lecciones.')
            ->assertDontSee('Hacer la evaluación');

        $this->actingAs($this->empleado)->get("/mis-cursos/{$sinLecciones->id}/evaluacion")->assertForbidden();
        $this->actingAs($this->empleado)
            ->post("/mis-cursos/{$sinLecciones->id}/evaluacion", ['respuestas' => [$pregunta->id => $correcta->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('resultados_evaluacion', 0);
    }

    public function test_sin_asignacion_o_con_otro_rol_da_403(): void
    {
        [, $correctas, $incorrectas] = $this->evaluacion(1);
        $respuestas = $this->respuestas($correctas, $incorrectas, 1);

        $noAsignado = User::factory()->create();
        $noAsignado->completarLeccion($this->leccion);

        foreach ([$noAsignado, User::factory()->create(['rol' => 'encargado']), User::factory()->administrador()->create()] as $usuario) {
            $this->actingAs($usuario)->get($this->url())->assertForbidden();
            $this->actingAs($usuario)->post($this->url(), ['respuestas' => $respuestas])->assertForbidden();
            $this->actingAs($usuario)->get($this->url('/resultado'))->assertForbidden();
        }

        $this->assertDatabaseCount('resultados_evaluacion', 0);
    }

    // --- Nota y aprobado ---

    public function test_la_nota_es_aciertos_entre_preguntas_por_10_con_2_decimales(): void
    {
        [, $correctas, $incorrectas] = $this->evaluacion(3);
        $this->completarLecciones();

        $this->enviar($this->respuestas($correctas, $incorrectas, 2))->assertRedirect($this->url('/resultado'));
        $this->enviar($this->respuestas($correctas, $incorrectas, 3));
        $this->enviar($this->respuestas($correctas, $incorrectas, 0));

        $notas = DB::table('resultados_evaluacion')->orderBy('id')->pluck('nota')->map(fn ($n) => (float) $n)->all();
        $this->assertSame([6.67, 10.0, 0.0], $notas);
    }

    public function test_el_aprobado_en_el_limite_exacto_de_la_nota_minima(): void
    {
        // 5 preguntas: 4 aciertos = 8.00 exacto, 3 aciertos = 6.00.
        [, $correctas, $incorrectas] = $this->evaluacion(5, 8.00);
        $this->completarLecciones();

        $this->enviar($this->respuestas($correctas, $incorrectas, 4));
        $this->enviar($this->respuestas($correctas, $incorrectas, 3));

        $this->assertSame([true, false], DB::table('resultados_evaluacion')->orderBy('id')->pluck('aprobado')->map(fn ($a) => (bool) $a)->all());
    }

    public function test_el_limite_con_decimales_periodicos(): void
    {
        // 2 de 3 = 6.666… -> 6.67. Con mínimo 6.67 aprueba; con 6.68 no.
        [$evaluacion, $correctas, $incorrectas] = $this->evaluacion(3, 6.67);
        $this->completarLecciones();

        $this->enviar($this->respuestas($correctas, $incorrectas, 2));
        $evaluacion->update(['nota_minima' => 6.68]);
        $this->enviar($this->respuestas($correctas, $incorrectas, 2));

        $this->assertSame([true, false], DB::table('resultados_evaluacion')->orderBy('id')->pluck('aprobado')->map(fn ($a) => (bool) $a)->all());
    }

    public function test_cambiar_la_nota_minima_no_altera_intentos_pasados(): void
    {
        [$evaluacion, $correctas, $incorrectas] = $this->evaluacion(5, 8);
        $this->completarLecciones();
        $this->enviar($this->respuestas($correctas, $incorrectas, 4));

        $evaluacion->update(['nota_minima' => 9]);

        $this->assertTrue((bool) DB::table('resultados_evaluacion')->value('aprobado'));
    }

    public function test_una_opcion_de_otra_pregunta_cuenta_como_fallo(): void
    {
        [, $correctas] = $this->evaluacion(2);
        $this->completarLecciones();
        [$primera, $segunda] = array_keys($correctas);

        // A la 1.ª se responde con la opción correcta de la 2.ª (y viceversa).
        $this->enviar([$primera => $correctas[$segunda], $segunda => $correctas[$primera]]);

        $this->assertSame(0.0, (float) DB::table('resultados_evaluacion')->value('nota'));
    }

    public function test_un_envio_incompleto_no_cuenta_como_intento(): void
    {
        [, $correctas] = $this->evaluacion(2);
        $this->completarLecciones();
        $primera = array_key_first($correctas);

        $this->actingAs($this->empleado)->from($this->url())
            ->post($this->url(), ['respuestas' => [$primera => $correctas[$primera]]])
            ->assertRedirect($this->url())
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('resultados_evaluacion', 0);
    }

    // --- Intentos y resultado ---

    public function test_intentos_ilimitados_cada_uno_es_una_fila(): void
    {
        [$evaluacion, $correctas, $incorrectas] = $this->evaluacion(2);
        $this->completarLecciones();

        foreach ([0, 1, 2, 2] as $aciertos) {
            $this->enviar($this->respuestas($correctas, $incorrectas, $aciertos))->assertRedirect($this->url('/resultado'));
        }

        $this->assertSame(4, DB::table('resultados_evaluacion')->where('empleado_id', $this->empleado->id)->where('evaluacion_id', $evaluacion->id)->count());

        $this->actingAs($this->empleado)->get($this->url('/resultado'))
            ->assertSee('Intento 4')
            ->assertSee('Tus intentos');
    }

    public function test_el_resultado_muestra_nota_estado_y_preguntas_falladas_sin_la_correcta(): void
    {
        [, $correctas, $incorrectas] = $this->evaluacion(3);
        $this->completarLecciones();

        $this->followingRedirects()
            ->actingAs($this->empleado)
            ->post($this->url(), ['respuestas' => $this->respuestas($correctas, $incorrectas, 1)])
            ->assertOk()
            ->assertSee('3,33')
            ->assertSee('Suspendido')
            ->assertSee('1 de 3 aciertos')
            ->assertSee('Preguntas falladas')
            ->assertSee('Pregunta 2')
            ->assertSee('Pregunta 3')
            ->assertDontSee('P2 bien')
            ->assertDontSee('P3 bien');
    }

    public function test_sin_intentos_el_resultado_da_404(): void
    {
        $this->evaluacion(1);

        $this->actingAs($this->empleado)->get($this->url('/resultado'))->assertNotFound();
    }

    // --- es_correcta nunca llega al navegador ---

    public function test_es_correcta_no_aparece_en_la_respuesta_al_empleado(): void
    {
        [, $correctas, $incorrectas] = $this->evaluacion(2);
        $this->completarLecciones();

        $formulario = $this->actingAs($this->empleado)->get($this->url())->assertOk();
        $formulario->assertDontSee('es_correcta')->assertDontSee('correcta"', false);

        // Las opciones correcta e incorrecta se pintan con el mismo marcado (solo cambian id y texto).
        $html = $formulario->getContent();
        foreach ($correctas as $preguntaId => $correcta) {
            // Se normalizan solo el id de la opción (value, id, for) y el texto de la etiqueta.
            $marcado = fn (int $id) => preg_replace(['/value="\d+"/', '/opcion_\d+/', '/>[^<]*<\/label>/'], ['value="ID"', 'opcion_ID', '>TEXTO</label>'],
                preg_match('/<div class="form-check">\s*<input[^>]*value="'.$id.'"[^>]*>\s*<label[^>]*>[^<]*<\/label>/', $html, $m) ? $m[0] : '');
            $this->assertNotSame('', $marcado($correcta));
            $this->assertSame($marcado($incorrectas[$preguntaId]), $marcado($correcta));
        }

        $this->followingRedirects()->actingAs($this->empleado)
            ->post($this->url(), ['respuestas' => $this->respuestas($correctas, $incorrectas, 1)])
            ->assertDontSee('es_correcta');
    }
}
