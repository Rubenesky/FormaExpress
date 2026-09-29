<?php

namespace Tests\Feature;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CertificadoTest extends TestCase
{
    use RefreshDatabase;

    private User $empleado;

    private Curso $curso;

    /** @var array{0: int, 1: int, 2: int} [pregunta, opción correcta, opción incorrecta] */
    private array $pregunta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empleado = User::factory()->create(['name' => 'Ana Pérez']);
        [$this->curso, $this->pregunta] = $this->cursoConEvaluacion('Apertura de caja', $this->empleado);
    }

    /**
     * Curso con 1 lección (ya completada por $empleado) y evaluación de 1 pregunta.
     *
     * @return array{0: Curso, 1: array{0: int, 1: int, 2: int}}
     */
    private function cursoConEvaluacion(string $titulo, User $empleado): array
    {
        $curso = Curso::create(['titulo' => $titulo]);
        $leccion = $curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()->toDateString()]);
        $empleado->completarLeccion($leccion);

        $pregunta = $curso->evaluacion()->create(['titulo' => 'Test', 'nota_minima' => 8])
            ->preguntas()->create(['enunciado' => 'Pregunta']);
        $bien = $pregunta->opciones()->create(['texto' => 'Bien', 'es_correcta' => true]);
        $mal = $pregunta->opciones()->create(['texto' => 'Mal', 'es_correcta' => false]);

        return [$curso, [$pregunta->id, $bien->id, $mal->id]];
    }

    private function responder(bool $acertar, ?Curso $curso = null, ?array $pregunta = null, ?User $empleado = null)
    {
        $curso ??= $this->curso;
        [$preguntaId, $bien, $mal] = $pregunta ?? $this->pregunta;

        return $this->actingAs($empleado ?? $this->empleado)
            ->post("/mis-cursos/{$curso->id}/evaluacion", ['respuestas' => [$preguntaId => $acertar ? $bien : $mal]]);
    }

    private function certificado(): Certificado
    {
        return Certificado::where('empleado_id', $this->empleado->id)->where('curso_id', $this->curso->id)->firstOrFail();
    }

    // --- Emisión ---

    public function test_aprobar_emite_el_certificado_con_la_fecha_de_hoy(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 29));

        $this->followingRedirects()->responder(true)
            ->assertSee('Has obtenido el certificado del curso.')
            ->assertSee('Descargar certificado');

        $this->assertSame('2026-09-29', $this->certificado()->fecha_emision->toDateString());
    }

    public function test_suspender_no_emite_certificado(): void
    {
        $this->followingRedirects()->responder(false)->assertDontSee('Has obtenido el certificado');

        $this->assertDatabaseCount('certificados', 0);
    }

    public function test_solo_se_emite_con_el_primer_aprobado(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 29));
        $this->responder(true);

        $this->travelTo(now()->setDate(2026, 10, 5));
        $this->followingRedirects()->responder(true)->assertDontSee('Has obtenido el certificado');

        $this->assertDatabaseCount('certificados', 1);
        $this->assertSame('2026-09-29', $this->certificado()->fecha_emision->toDateString());
    }

    public function test_un_curso_sin_evaluacion_no_certifica(): void
    {
        $sinEvaluacion = Curso::create(['titulo' => 'Sin evaluación']);
        $leccion = $sinEvaluacion->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $sinEvaluacion->empleadosAsignados()->attach($this->empleado->id, ['fecha_asignacion' => today()->toDateString()]);

        $this->actingAs($this->empleado)->post("/mis-lecciones/{$leccion->id}/completar");

        $this->assertDatabaseMissing('certificados', ['curso_id' => $sinEvaluacion->id]);
    }

    public function test_el_certificado_se_ve_en_el_curso_y_en_mis_cursos(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 29));
        $this->responder(true);

        $this->actingAs($this->empleado)->get("/mis-cursos/{$this->curso->id}")->assertSee('Curso certificado el 29/09/2026.');
        $this->actingAs($this->empleado)->get('/mis-cursos')->assertSee('Certificado</span>', false);
    }

    // --- PDF ---

    public function test_el_empleado_descarga_su_certificado_en_pdf(): void
    {
        $this->responder(true);

        $respuesta = $this->actingAs($this->empleado)->get("/certificados/{$this->certificado()->id}/pdf")->assertOk();

        $this->assertSame('application/pdf', $respuesta->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=certificado-apertura-de-caja.pdf', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_el_pdf_contiene_nombre_curso_fecha_y_codigo(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 29));
        $this->responder(true);
        $certificado = $this->certificado()->load(['empleado', 'curso']);

        $html = view('certificados.pdf', compact('certificado'))->render();

        $this->assertStringContainsString('Ana Pérez', $html);
        $this->assertStringContainsString('«Apertura de caja»', $html);
        $this->assertStringContainsString('29/09/2026', $html);
        $this->assertStringContainsString($certificado->codigo(), $html);
    }

    public function test_quien_puede_descargar_un_certificado(): void
    {
        $this->responder(true);
        $url = "/certificados/{$this->certificado()->id}/pdf";

        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(['rol' => 'encargado']))->get($url)->assertForbidden();
        $this->actingAs(User::factory()->administrador()->create())->get($url)->assertOk();

        $this->app['auth']->forgetGuards();
        $this->get($url)->assertRedirect('/login');
    }

    // --- Mis logros y vista del administrador ---

    public function test_mis_logros_muestra_solo_los_certificados_propios(): void
    {
        $this->responder(true);

        $otra = User::factory()->create();
        [$otroCurso, $otraPregunta] = $this->cursoConEvaluacion('Almacén', $otra);
        $this->responder(true, $otroCurso, $otraPregunta, $otra);

        $this->actingAs($this->empleado)->get('/mis-logros')
            ->assertOk()
            ->assertSee('Apertura de caja')
            ->assertDontSee('Almacén');
    }

    public function test_mis_logros_solo_para_empleados(): void
    {
        $this->actingAs(User::factory()->administrador()->create())->get('/mis-logros')->assertForbidden();
        $this->actingAs(User::factory()->create(['rol' => 'encargado']))->get('/mis-logros')->assertForbidden();
    }

    public function test_el_administrador_ve_el_certificado_en_las_asignaciones(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 29));
        $this->responder(true);

        $this->actingAs(User::factory()->administrador()->create())
            ->get("/cursos/{$this->curso->id}/asignaciones")
            ->assertSee('29/09/2026')
            ->assertSee("/certificados/{$this->certificado()->id}/pdf", false);
    }

    public function test_no_se_borra_un_curso_con_certificados(): void
    {
        $this->responder(true);
        DB::table('resultados_evaluacion')->delete();
        DB::table('asignaciones_curso')->delete();
        DB::table('progreso_lecciones')->delete();

        $this->actingAs(User::factory()->administrador()->create())
            ->delete("/cursos/{$this->curso->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('cursos', ['id' => $this->curso->id]);
        $this->assertDatabaseCount('certificados', 1);
    }
}
