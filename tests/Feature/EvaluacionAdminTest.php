<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EvaluacionAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->administrador()->create();
        $this->curso = Curso::create(['titulo' => 'Apertura de caja']);
    }

    private function url(string $sufijo = ''): string
    {
        return "/cursos/{$this->curso->id}/evaluacion{$sufijo}";
    }

    private function evaluacion(array $datos = []): Evaluacion
    {
        return $this->curso->evaluacion()->create($datos + ['titulo' => 'Test', 'nota_minima' => 8]);
    }

    /** Datos de pregunta válidos: 3 opciones, la segunda es la correcta. */
    private function datosPregunta(array $cambios = []): array
    {
        return $cambios + [
            'enunciado' => '¿Qué se cuenta al abrir la caja?',
            'opciones' => ['Nada', 'El fondo de caja', 'Las ventas', '', '', ''],
            'correcta' => 1,
        ];
    }

    private function empleadoConResultado(Evaluacion $evaluacion): void
    {
        DB::table('resultados_evaluacion')->insert([
            'empleado_id' => User::factory()->create()->id,
            'evaluacion_id' => $evaluacion->id,
            'nota' => 5,
            'aprobado' => false,
        ]);
    }

    // --- Evaluación: crear, editar ---

    public function test_el_formulario_propone_titulo_y_nota_minima_8(): void
    {
        $this->actingAs($this->admin)->get($this->url('/create'))
            ->assertOk()
            ->assertSee('value="Evaluación: Apertura de caja"', false)
            ->assertSee('value="8.00"', false);
    }

    public function test_el_administrador_crea_la_evaluacion(): void
    {
        $this->actingAs($this->admin)
            ->post($this->url(), ['titulo' => 'Test de caja', 'nota_minima' => '7.5'])
            ->assertRedirect($this->url())
            ->assertSessionHas('status', 'Evaluación creada.');

        $this->assertDatabaseHas('evaluaciones', ['curso_id' => $this->curso->id, 'titulo' => 'Test de caja', 'nota_minima' => 7.5]);
    }

    public function test_la_nota_minima_se_valida(): void
    {
        $casos = [
            '11' => 'El campo nota mínima debe estar entre 0 y 10.',
            '8.555' => 'La nota mínima admite como máximo 2 decimales.',
            'abc' => 'El campo nota mínima debe ser un número.',
            '' => 'El campo nota mínima es obligatorio.',
        ];

        foreach ($casos as $valor => $mensaje) {
            $this->actingAs($this->admin)
                ->post($this->url(), ['titulo' => 'T', 'nota_minima' => $valor])
                ->assertSessionHasErrors(['nota_minima' => $mensaje]);
        }

        $this->assertDatabaseCount('evaluaciones', 0);
    }

    public function test_solo_hay_una_evaluacion_por_curso(): void
    {
        $this->evaluacion();

        $this->actingAs($this->admin)->get($this->url('/create'))->assertRedirect($this->url());

        $this->actingAs($this->admin)
            ->post($this->url(), ['titulo' => 'Segunda', 'nota_minima' => '5'])
            ->assertSessionHas('error', 'Este curso ya tiene una evaluación.');

        $this->assertSame(1, Evaluacion::where('curso_id', $this->curso->id)->count());
    }

    public function test_el_administrador_edita_la_nota_minima(): void
    {
        $this->evaluacion();

        $this->actingAs($this->admin)
            ->put($this->url(), ['titulo' => 'Nuevo título', 'nota_minima' => '9.25'])
            ->assertRedirect($this->url());

        $this->assertDatabaseHas('evaluaciones', ['curso_id' => $this->curso->id, 'titulo' => 'Nuevo título', 'nota_minima' => 9.25]);
    }

    public function test_editar_o_borrar_sin_evaluacion_da_404(): void
    {
        $this->actingAs($this->admin)->get($this->url('/edit'))->assertNotFound();
        $this->actingAs($this->admin)->get($this->url('/eliminar'))->assertNotFound();
        $this->actingAs($this->admin)->delete($this->url())->assertNotFound();
    }

    // --- Evaluación: eliminar ---

    public function test_eliminar_la_evaluacion_borra_sus_preguntas_y_opciones(): void
    {
        $evaluacion = $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta(['enunciado' => 'Otra']));

        $this->actingAs($this->admin)->get($this->url('/eliminar'))
            ->assertOk()
            ->assertSee('Se borrarán también sus 2 preguntas y sus opciones.');

        $this->actingAs($this->admin)->delete($this->url())
            ->assertRedirect($this->url())
            ->assertSessionHas('status', 'Evaluación eliminada junto con sus preguntas.');

        $this->assertDatabaseMissing('evaluaciones', ['id' => $evaluacion->id]);
        $this->assertDatabaseCount('preguntas', 0);
        $this->assertDatabaseCount('opciones', 0);
    }

    public function test_no_se_elimina_una_evaluacion_con_resultados(): void
    {
        $evaluacion = $this->evaluacion();
        $this->empleadoConResultado($evaluacion);

        $this->actingAs($this->admin)->get($this->url('/eliminar'))
            ->assertOk()
            ->assertSee('No se puede eliminar')
            ->assertDontSee('Sí, eliminar la evaluación');

        $this->actingAs($this->admin)->delete($this->url())
            ->assertRedirect($this->url())
            ->assertSessionHas('error', 'No se puede eliminar la evaluación: hay resultados de empleados.');

        $this->assertDatabaseHas('evaluaciones', ['id' => $evaluacion->id]);
    }

    // --- Preguntas y opciones ---

    public function test_el_administrador_crea_una_pregunta_con_sus_opciones(): void
    {
        $this->evaluacion();

        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta())
            ->assertRedirect($this->url())
            ->assertSessionHas('status', 'Pregunta creada.');

        $opciones = DB::table('opciones')->orderBy('id')->get();
        $this->assertSame(['Nada', 'El fondo de caja', 'Las ventas'], $opciones->pluck('texto')->all());
        $this->assertSame([0, 1, 0], $opciones->pluck('es_correcta')->map(fn ($v) => (int) $v)->all());
    }

    public function test_una_pregunta_necesita_al_menos_dos_opciones(): void
    {
        $this->evaluacion();

        $this->actingAs($this->admin)
            ->post($this->url('/preguntas'), $this->datosPregunta(['opciones' => ['Solo una', '', ''], 'correcta' => 0]))
            ->assertSessionHasErrors(['opciones' => 'La pregunta necesita al menos 2 opciones.']);

        $this->assertDatabaseCount('preguntas', 0);
    }

    public function test_una_pregunta_necesita_exactamente_una_correcta(): void
    {
        $this->evaluacion();

        // Ninguna marcada.
        $sinCorrecta = $this->datosPregunta();
        unset($sinCorrecta['correcta']);
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $sinCorrecta)
            ->assertSessionHasErrors(['correcta' => 'Marca cuál es la opción correcta.']);

        // La marcada es un hueco vacío.
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta(['correcta' => 4]))
            ->assertSessionHasErrors(['correcta' => 'La opción marcada como correcta está vacía.']);

        // Dos marcadas (petición manipulada: el radio no lo permite).
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta(['correcta' => [0, 1]]))
            ->assertSessionHasErrors('correcta');

        $this->assertDatabaseCount('preguntas', 0);
        $this->assertDatabaseCount('opciones', 0);
    }

    public function test_el_formulario_no_falla_al_volver_de_una_peticion_manipulada(): void
    {
        $this->evaluacion();

        $this->actingAs($this->admin)
            ->from($this->url('/preguntas/create'))
            ->post($this->url('/preguntas'), $this->datosPregunta(['correcta' => [0, 1]]));

        $this->actingAs($this->admin)->get($this->url('/preguntas/create'))->assertOk();
    }

    public function test_editar_una_pregunta_reemplaza_sus_opciones(): void
    {
        $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());
        $pregunta = DB::table('preguntas')->first();

        $this->actingAs($this->admin)->get($this->url("/preguntas/{$pregunta->id}/edit"))
            ->assertOk()
            ->assertSee('value="El fondo de caja"', false);

        $this->actingAs($this->admin)
            ->put($this->url("/preguntas/{$pregunta->id}"), [
                'enunciado' => 'Enunciado nuevo',
                'opciones' => ['', 'El fondo de caja', 'Las ventas', 'Otra más', '', ''],
                'correcta' => 3,
            ])
            ->assertRedirect($this->url());

        $opciones = DB::table('opciones')->where('pregunta_id', $pregunta->id)->orderBy('id')->get();
        $this->assertSame(['El fondo de caja', 'Las ventas', 'Otra más'], $opciones->pluck('texto')->all());
        $this->assertSame(1, $opciones->where('es_correcta', true)->count());
        $this->assertSame('Otra más', $opciones->firstWhere('es_correcta', true)->texto);
        $this->assertDatabaseHas('preguntas', ['id' => $pregunta->id, 'enunciado' => 'Enunciado nuevo']);
    }

    public function test_el_administrador_elimina_una_pregunta(): void
    {
        $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());
        $pregunta = DB::table('preguntas')->first();

        $this->actingAs($this->admin)->get($this->url("/preguntas/{$pregunta->id}/eliminar"))->assertOk();
        $this->actingAs($this->admin)->delete($this->url("/preguntas/{$pregunta->id}"))
            ->assertRedirect($this->url())
            ->assertSessionHas('status', 'Pregunta eliminada.');

        $this->assertDatabaseCount('preguntas', 0);
        $this->assertDatabaseCount('opciones', 0);
    }

    public function test_una_pregunta_no_se_maneja_desde_otro_curso(): void
    {
        $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());
        $pregunta = DB::table('preguntas')->first();

        $otro = Curso::create(['titulo' => 'Otro']);
        $otro->evaluacion()->create(['titulo' => 'Otra', 'nota_minima' => 5]);
        $base = "/cursos/{$otro->id}/evaluacion/preguntas/{$pregunta->id}";

        $this->actingAs($this->admin)->get("{$base}/edit")->assertNotFound();
        $this->actingAs($this->admin)->put($base, $this->datosPregunta(['enunciado' => 'Hackeada']))->assertNotFound();
        $this->actingAs($this->admin)->delete($base)->assertNotFound();

        $this->assertDatabaseHas('preguntas', ['id' => $pregunta->id, 'enunciado' => '¿Qué se cuenta al abrir la caja?']);
    }

    // --- Acceso ---

    public function test_empleado_y_encargado_reciben_403_en_la_administracion(): void
    {
        $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());
        $pregunta = DB::table('preguntas')->first();

        $empleado = User::factory()->create();
        $this->curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()->toDateString()]);

        foreach ([$empleado, User::factory()->create(['rol' => 'encargado'])] as $usuario) {
            $this->actingAs($usuario)->get($this->url())->assertForbidden();
            $this->actingAs($usuario)->get($this->url('/create'))->assertForbidden();
            $this->actingAs($usuario)->post($this->url(), ['titulo' => 'X', 'nota_minima' => 1])->assertForbidden();
            $this->actingAs($usuario)->get($this->url('/edit'))->assertForbidden();
            $this->actingAs($usuario)->put($this->url(), ['titulo' => 'X', 'nota_minima' => 1])->assertForbidden();
            $this->actingAs($usuario)->get($this->url('/eliminar'))->assertForbidden();
            $this->actingAs($usuario)->delete($this->url())->assertForbidden();
            $this->actingAs($usuario)->get($this->url('/preguntas/create'))->assertForbidden();
            $this->actingAs($usuario)->post($this->url('/preguntas'), $this->datosPregunta())->assertForbidden();
            $this->actingAs($usuario)->get($this->url("/preguntas/{$pregunta->id}/edit"))->assertForbidden();
            $this->actingAs($usuario)->put($this->url("/preguntas/{$pregunta->id}"), $this->datosPregunta())->assertForbidden();
            $this->actingAs($usuario)->delete($this->url("/preguntas/{$pregunta->id}"))->assertForbidden();
        }

        $this->assertDatabaseCount('evaluaciones', 1);
        $this->assertDatabaseCount('preguntas', 1);
        $this->assertDatabaseHas('evaluaciones', ['titulo' => 'Test', 'nota_minima' => 8]);
    }

    // --- B3: borrado del curso ---

    public function test_el_aviso_de_borrar_curso_menciona_la_evaluacion_y_sus_preguntas(): void
    {
        $this->curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta(['enunciado' => 'Otra']));
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta(['enunciado' => 'Y otra']));

        $this->actingAs($this->admin)->get("/cursos/{$this->curso->id}/eliminar")
            ->assertSee('Se borrará también su única lección.')
            ->assertSeeText('También se borrará su evaluación y sus 3 preguntas.');
    }

    public function test_borrar_un_curso_sin_resultados_borra_su_evaluacion(): void
    {
        $evaluacion = $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());

        $this->actingAs($this->admin)->delete("/cursos/{$this->curso->id}")->assertSessionHas('status');

        $this->assertDatabaseMissing('evaluaciones', ['id' => $evaluacion->id]);
        $this->assertDatabaseCount('preguntas', 0);
        $this->assertDatabaseCount('opciones', 0);
    }

    public function test_borrar_un_curso_con_resultados_falla_limpiamente(): void
    {
        $evaluacion = $this->evaluacion();
        $this->actingAs($this->admin)->post($this->url('/preguntas'), $this->datosPregunta());
        // Sin asignación: el único bloqueo es el resultado (cascada curso -> evaluación -> RESTRICT).
        $this->empleadoConResultado($evaluacion);

        $this->actingAs($this->admin)->delete("/cursos/{$this->curso->id}")
            ->assertRedirect('/cursos')
            ->assertSessionHas('error', 'No se puede eliminar «Apertura de caja»: hay datos de empleados asociados a este curso.');

        $this->assertDatabaseHas('cursos', ['id' => $this->curso->id]);
        $this->assertDatabaseHas('evaluaciones', ['id' => $evaluacion->id]);
        $this->assertDatabaseCount('preguntas', 1);
    }
}
