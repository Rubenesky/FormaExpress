<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AyudaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_administrador_ve_el_modal_de_ayuda_en_cursos(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->get('/cursos')
            ->assertOk()
            ->assertSee('Ayuda sobre esta pantalla', false)
            ->assertSee('id="ayuda-cursos"', false)
            ->assertSee('Ayuda: Cursos')
            ->assertSee('Esta pantalla lista todos los cursos del centro de formación', false);
    }

    public function test_el_empleado_ve_el_modal_de_ayuda_en_mis_cursos(): void
    {
        $empleado = User::factory()->create();

        $this->actingAs($empleado)->get('/mis-cursos')
            ->assertOk()
            ->assertSee('id="ayuda-mis-cursos"', false)
            ->assertSee('Ayuda: Mis cursos')
            ->assertSee('Aquí ves los cursos que te han asignado', false);
    }

    public function test_el_encargado_ve_el_modal_de_ayuda_en_consulta(): void
    {
        $encargado = User::factory()->create(['rol' => 'encargado']);

        $this->actingAs($encargado)->get('/consulta')
            ->assertOk()
            ->assertSee('id="ayuda-consulta"', false)
            ->assertSee('Ayuda: Consulta')
            ->assertSee('Esta pantalla busca empleados por su nombre', false);
    }

    public function test_el_modal_de_ayuda_esta_en_las_once_pantallas(): void
    {
        $admin = User::factory()->administrador()->create();
        $empleado = User::factory()->create();
        $encargado = User::factory()->create(['rol' => 'encargado']);

        $curso = Curso::create(['titulo' => 'Apertura de caja']);
        $leccion = $curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'Texto', 'orden' => 1]);
        $curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => '2026-09-01']);
        $empleado->completarLeccion($leccion);
        $evaluacion = $curso->evaluacion()->create(['titulo' => 'Evaluación', 'nota_minima' => 8]);
        $pregunta = $evaluacion->preguntas()->create(['enunciado' => '¿1+1?']);
        $pregunta->opciones()->create(['texto' => '2', 'es_correcta' => true]);
        $pregunta->opciones()->create(['texto' => '3', 'es_correcta' => false]);

        $pantallas = [
            [$admin, "/cursos/{$curso->id}/lecciones", 'ayuda-lecciones'],
            [$admin, "/cursos/{$curso->id}/asignaciones", 'ayuda-asignaciones'],
            [$admin, "/cursos/{$curso->id}/evaluacion", 'ayuda-evaluacion-admin'],
            [$admin, '/registro-consultas', 'ayuda-registro-consultas'],
            [$empleado, "/mis-lecciones/{$leccion->id}", 'ayuda-leccion'],
            [$empleado, "/mis-cursos/{$curso->id}/evaluacion", 'ayuda-evaluacion-empleado'],
            [$empleado, '/mis-logros', 'ayuda-mis-logros'],
            [$encargado, "/consulta/empleados/{$empleado->id}", 'ayuda-ficha'],
        ];

        foreach ($pantallas as [$usuario, $url, $idModal]) {
            $this->actingAs($usuario)->get($url)
                ->assertOk()
                ->assertSee('id="'.$idModal.'"', false);
        }
    }
}
