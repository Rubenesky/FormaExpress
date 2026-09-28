<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsignacionRequest;
use App\Models\Curso;
use App\Models\User;

class AsignacionController extends Controller
{
    public function index(Curso $curso)
    {
        $asignados = $curso->empleadosAsignados()->orderBy('name')->paginate(20);

        // Para el selector: empleados que aún no tienen el curso.
        $disponibles = User::where('rol', 'empleado')
            ->whereDoesntHave('cursosAsignados', fn ($q) => $q->whereKey($curso->id))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('asignaciones.index', compact('curso', 'asignados', 'disponibles'));
    }

    public function store(AsignacionRequest $request, Curso $curso)
    {
        $curso->empleadosAsignados()->attach($request->validated('empleado_id'), ['fecha_asignacion' => today()->toDateString()]);

        return redirect()->route('cursos.asignaciones.index', $curso)->with('status', 'Curso asignado.');
    }

    public function confirmarEliminacion(Curso $curso, User $empleado)
    {
        $this->comprobarQueEstaAsignado($curso, $empleado);

        $tieneProgreso = $curso->tieneProgresoDe($empleado);

        return view('asignaciones.eliminar', compact('curso', 'empleado', 'tieneProgreso'));
    }

    public function destroy(Curso $curso, User $empleado)
    {
        $this->comprobarQueEstaAsignado($curso, $empleado);

        // Ninguna FK lo impide: se comprueba aquí (ver Curso::tieneProgresoDe).
        if ($curso->tieneProgresoDe($empleado)) {
            return redirect()->route('cursos.asignaciones.index', $curso)
                ->with('error', "No se puede desasignar a {$empleado->name}: ya tiene progreso en este curso.");
        }

        $curso->empleadosAsignados()->detach($empleado->id);

        return redirect()->route('cursos.asignaciones.index', $curso)->with('status', 'Asignación eliminada.');
    }

    private function comprobarQueEstaAsignado(Curso $curso, User $empleado): void
    {
        abort_unless($curso->empleadosAsignados()->whereKey($empleado->id)->exists(), 404);
    }
}
