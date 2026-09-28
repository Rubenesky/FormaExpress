<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionTest extends TestCase
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
        return "/cursos/{$this->curso->id}/asignaciones{$sufijo}";
    }

    public function test_el_selector_solo_ofrece_empleados_sin_el_curso(): void
    {
        $asignada = User::factory()->create(['name' => 'Asignada']);
        $libre = User::factory()->create(['name' => 'Libre']);
        User::factory()->create(['name' => 'Encargado', 'rol' => 'encargado']);
        $this->curso->empleadosAsignados()->attach($asignada->id, ['fecha_asignacion' => today()]);

        $this->actingAs($this->admin)->get($this->url())
            ->assertOk()
            ->assertViewHas('disponibles', fn ($d) => $d->pluck('id')->all() === [$libre->id])
            ->assertViewHas('asignados', fn ($a) => $a->pluck('id')->all() === [$asignada->id]);
    }

    public function test_el_administrador_asigna_un_curso(): void
    {
        $empleado = User::factory()->create();

        $this->actingAs($this->admin)
            ->post($this->url(), ['empleado_id' => $empleado->id])
            ->assertRedirect($this->url())
            ->assertSessionHas('status', 'Curso asignado.');

        $this->assertDatabaseHas('asignaciones_curso', [
            'empleado_id' => $empleado->id,
            'curso_id' => $this->curso->id,
            'fecha_asignacion' => today()->toDateString(),
        ]);
    }

    public function test_no_se_asigna_dos_veces_el_mismo_curso(): void
    {
        $empleado = User::factory()->create();
        $this->curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()]);

        $this->actingAs($this->admin)
            ->post($this->url(), ['empleado_id' => $empleado->id])
            ->assertSessionHasErrors(['empleado_id' => 'Este empleado ya tiene el curso asignado.']);

        $this->assertDatabaseCount('asignaciones_curso', 1);
    }

    public function test_solo_se_asigna_a_empleados(): void
    {
        $encargado = User::factory()->create(['rol' => 'encargado']);

        foreach ([$encargado->id, $this->admin->id, 99999] as $id) {
            $this->actingAs($this->admin)
                ->post($this->url(), ['empleado_id' => $id])
                ->assertSessionHasErrors(['empleado_id' => 'El empleado seleccionado no es válido.']);
        }

        $this->actingAs($this->admin)
            ->post($this->url(), ['empleado_id' => ''])
            ->assertSessionHasErrors(['empleado_id' => 'El campo empleado es obligatorio.']);

        $this->assertDatabaseCount('asignaciones_curso', 0);
    }

    public function test_el_administrador_desasigna_un_curso_sin_progreso(): void
    {
        $empleado = User::factory()->create();
        $this->curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()]);

        $this->actingAs($this->admin)->get($this->url("/{$empleado->id}/eliminar"))
            ->assertOk()
            ->assertSee('Sí, desasignar');

        $this->actingAs($this->admin)
            ->delete($this->url("/{$empleado->id}"))
            ->assertRedirect($this->url())
            ->assertSessionHas('status', 'Asignación eliminada.');

        $this->assertDatabaseCount('asignaciones_curso', 0);
    }

    public function test_no_se_desasigna_si_el_empleado_tiene_progreso(): void
    {
        $empleado = User::factory()->create();
        $leccion = $this->curso->lecciones()->create(['titulo' => 'L1', 'contenido' => 'x', 'orden' => 1]);
        $this->curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()]);
        $empleado->completarLeccion($leccion);

        $this->actingAs($this->admin)->get($this->url("/{$empleado->id}/eliminar"))
            ->assertOk()
            ->assertSee('No se puede desasignar')
            ->assertDontSee('Sí, desasignar');

        // Aunque se llame directamente a la ruta DELETE.
        $this->actingAs($this->admin)
            ->delete($this->url("/{$empleado->id}"))
            ->assertRedirect($this->url())
            ->assertSessionHas('error', "No se puede desasignar a {$empleado->name}: ya tiene progreso en este curso.");

        $this->assertDatabaseHas('asignaciones_curso', ['empleado_id' => $empleado->id, 'curso_id' => $this->curso->id]);
    }

    public function test_el_progreso_en_otro_curso_no_bloquea(): void
    {
        $empleado = User::factory()->create();
        $otro = Curso::create(['titulo' => 'Otro']);
        $empleado->completarLeccion($otro->lecciones()->create(['titulo' => 'L', 'contenido' => 'x', 'orden' => 1]));
        $this->curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()]);

        $this->actingAs($this->admin)->delete($this->url("/{$empleado->id}"))->assertSessionHas('status');

        $this->assertDatabaseCount('asignaciones_curso', 0);
    }

    public function test_desasignar_a_quien_no_lo_tiene_da_404(): void
    {
        $empleado = User::factory()->create();

        $this->actingAs($this->admin)->get($this->url("/{$empleado->id}/eliminar"))->assertNotFound();
        $this->actingAs($this->admin)->delete($this->url("/{$empleado->id}"))->assertNotFound();
    }

    public function test_empleado_y_encargado_no_acceden_a_las_asignaciones(): void
    {
        $empleado = User::factory()->create();
        $this->curso->empleadosAsignados()->attach($empleado->id, ['fecha_asignacion' => today()]);

        foreach ([$empleado, User::factory()->create(['rol' => 'encargado'])] as $usuario) {
            $this->actingAs($usuario)->get($this->url())->assertForbidden();
            $this->actingAs($usuario)->post($this->url(), ['empleado_id' => $empleado->id])->assertForbidden();
            $this->actingAs($usuario)->get($this->url("/{$empleado->id}/eliminar"))->assertForbidden();
            $this->actingAs($usuario)->delete($this->url("/{$empleado->id}"))->assertForbidden();
        }

        $this->assertDatabaseCount('asignaciones_curso', 1);
    }
}
