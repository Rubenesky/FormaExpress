<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Peticiones manipuladas que envían un array donde el formulario espera un
 * texto: la validación las rechaza y, al volver al formulario, la página debe
 * mostrarse con su error (nunca un 500 por old() devolviendo un array).
 */
class EntradaManipuladaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->administrador()->create();
        $this->curso = Curso::create(['titulo' => 'Caja']);
    }

    /**
     * Envía $datos desde $formulario y comprueba que al volver no hay 500.
     */
    private function assertVuelveSinError(?User $usuario, string $formulario, string $destino, array $datos, string $metodo = 'post'): void
    {
        $peticion = $usuario ? $this->actingAs($usuario) : $this;
        $peticion->from($formulario)->{$metodo}($destino, $datos)->assertRedirect($formulario);

        $vuelta = $usuario ? $this->actingAs($usuario)->get($formulario) : $this->get($formulario);
        $vuelta->assertOk();
    }

    public function test_formularios_del_administrador(): void
    {
        $leccion = $this->curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $evaluacion = $this->curso->evaluacion()->create(['titulo' => 'Test', 'nota_minima' => 8]);
        $pregunta = $evaluacion->preguntas()->create(['enunciado' => 'P1']);
        $pregunta->opciones()->create(['texto' => 'A', 'es_correcta' => true]);
        $pregunta->opciones()->create(['texto' => 'B', 'es_correcta' => false]);
        $sinEvaluacion = Curso::create(['titulo' => 'Sin evaluación']);
        $c = $this->curso->id;

        $this->assertVuelveSinError($this->admin, '/cursos/create', '/cursos', ['titulo' => ['x']]);
        $this->assertVuelveSinError($this->admin, "/cursos/{$c}/edit", "/cursos/{$c}", ['titulo' => 'T', 'descripcion' => ['x']], 'put');
        $this->assertVuelveSinError($this->admin, "/cursos/{$c}/lecciones/create", "/cursos/{$c}/lecciones", ['titulo' => ['x'], 'contenido' => ['y'], 'orden' => ['1']]);
        $this->assertVuelveSinError($this->admin, "/cursos/{$c}/lecciones/{$leccion->id}/edit", "/cursos/{$c}/lecciones/{$leccion->id}", ['titulo' => ['x'], 'contenido' => 'y', 'orden' => 1], 'put');
        $this->assertVuelveSinError($this->admin, "/cursos/{$c}/asignaciones", "/cursos/{$c}/asignaciones", ['empleado_id' => ['1']]);
        $this->assertVuelveSinError($this->admin, "/cursos/{$sinEvaluacion->id}/evaluacion/create", "/cursos/{$sinEvaluacion->id}/evaluacion", ['titulo' => ['x'], 'nota_minima' => ['5']]);
        $this->assertVuelveSinError($this->admin, "/cursos/{$c}/evaluacion/edit", "/cursos/{$c}/evaluacion", ['titulo' => ['x'], 'nota_minima' => ['5']], 'put');
        $this->assertVuelveSinError($this->admin, "/cursos/{$c}/evaluacion/preguntas/create", "/cursos/{$c}/evaluacion/preguntas", [
            'enunciado' => ['x'], 'opciones' => [['a'], 'b'], 'correcta' => [0, 1],
        ]);
        $this->assertVuelveSinError($this->admin, "/cursos/{$c}/evaluacion/preguntas/{$pregunta->id}/edit", "/cursos/{$c}/evaluacion/preguntas/{$pregunta->id}", [
            'enunciado' => ['x'], 'opciones' => [['a'], 'b'], 'correcta' => [0, 1],
        ], 'put');

        $this->assertDatabaseCount('cursos', 2);
        $this->assertDatabaseHas('cursos', ['id' => $c, 'titulo' => 'Caja']);
        $this->assertDatabaseCount('lecciones', 1);
        $this->assertDatabaseCount('evaluaciones', 1);
        // Solo la pregunta creada legítimamente arriba; ninguna de las dos
        // peticiones manipuladas (crear y editar) llegó a tocarla.
        $this->assertDatabaseCount('preguntas', 1);
        $this->assertDatabaseHas('preguntas', ['id' => $pregunta->id, 'enunciado' => 'P1']);
    }

    public function test_formularios_de_login_y_registro(): void
    {
        $this->assertVuelveSinError(null, '/login', '/login', ['email' => ['x'], 'password' => 'y']);
        $this->assertVuelveSinError(null, '/register', '/register', ['name' => ['x'], 'email' => ['y'], 'password' => 'secreto123', 'password_confirmation' => 'secreto123']);

        $this->assertDatabaseCount('empleados', 1);
    }

    public function test_formularios_de_recuperacion_de_contrasena(): void
    {
        $this->assertVuelveSinError(null, '/password/reset', '/password/email', ['email' => ['x']]);
        $this->assertVuelveSinError(null, '/password/reset/token-falso', '/password/reset', [
            'token' => 'token-falso', 'email' => ['x'], 'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ]);
        $this->assertVuelveSinError($this->admin, '/password/confirm', '/password/confirm', ['password' => ['x']]);
    }

    public function test_formulario_de_la_evaluacion_del_empleado(): void
    {
        $empleado = User::factory()->create();
        $leccion = $this->curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $this->curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()->toDateString()]);
        $empleado->completarLeccion($leccion);
        $pregunta = $this->curso->evaluacion()->create(['titulo' => 'Test', 'nota_minima' => 8])
            ->preguntas()->create(['enunciado' => 'Pregunta']);
        $opcion = $pregunta->opciones()->create(['texto' => 'Sí', 'es_correcta' => true]);
        $pregunta->opciones()->create(['texto' => 'No', 'es_correcta' => false]);
        $url = "/mis-cursos/{$this->curso->id}/evaluacion";

        $this->assertVuelveSinError($empleado, $url, $url, ['respuestas' => [$pregunta->id => [$opcion->id]]]);
        $this->assertVuelveSinError($empleado, $url, $url, ['respuestas' => 'texto']);

        $this->assertDatabaseCount('resultados_evaluacion', 0);
    }

    public function test_que_se_guarda_para_old_al_volver(): void
    {
        // Texto enviado como array: el formulario vuelve con el error en español y
        // conserva el resto de campos. (Se comprueba con un GET sin aserciones de
        // sesión en medio: los assertSession* del test caducan los datos flash.)
        $datos = ['titulo' => ['x'], 'descripcion' => 'Se conserva'];
        $this->actingAs($this->admin)->from('/cursos/create')->post('/cursos', $datos);
        $this->actingAs($this->admin)->get('/cursos/create')
            ->assertOk()
            ->assertSee('El campo título debe ser un texto.')
            ->assertSee('>Se conserva</textarea>', false);

        $this->actingAs($this->admin)->from('/cursos/create')->post('/cursos', $datos)
            ->assertSessionHasInput('descripcion', 'Se conserva')
            ->assertSessionMissing('_old_input.titulo');

        // Array legítimo (opciones): se guarda con sus textos, sin los elementos anidados.
        $this->curso->evaluacion()->create(['titulo' => 'Test', 'nota_minima' => 8]);
        $this->actingAs($this->admin)->from("/cursos/{$this->curso->id}/evaluacion/preguntas/create")
            ->post("/cursos/{$this->curso->id}/evaluacion/preguntas", ['enunciado' => 'P', 'opciones' => ['A', ['x'], 'C'], 'correcta' => 0])
            ->assertSessionHasInput('opciones', [0 => 'A', 2 => 'C']);

        // Las contraseñas no se guardan nunca (como en Laravel). /register es solo para invitados.
        $this->app['auth']->forgetGuards();
        $this->from('/register')
            ->post('/register', ['name' => ['x'], 'email' => 'a@b.test', 'password' => 'secreto123', 'password_confirmation' => 'secreto123'])
            ->assertSessionHasInput('email', 'a@b.test')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');
    }

    public function test_los_valores_normales_se_conservan_al_volver(): void
    {
        $this->actingAs($this->admin)->from('/cursos/create')
            ->post('/cursos', ['titulo' => '', 'descripcion' => 'Texto que no se pierde']);

        $this->actingAs($this->admin)->get('/cursos/create')
            ->assertOk()
            ->assertSee('Texto que no se pierde');
    }
}
