<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeccionRequest;
use App\Models\Curso;
use App\Models\Leccion;
use Illuminate\Database\QueryException;

class LeccionController extends Controller
{
    public function index(Curso $curso)
    {
        $lecciones = $curso->lecciones()->paginate(10);

        return view('lecciones.index', compact('curso', 'lecciones'));
    }

    public function create(Curso $curso)
    {
        // Propone el siguiente número de orden.
        $leccion = new Leccion(['orden' => ($curso->lecciones()->max('orden') ?? 0) + 1]);

        return view('lecciones.create', compact('curso', 'leccion'));
    }

    public function store(LeccionRequest $request, Curso $curso)
    {
        $curso->lecciones()->create($request->validated());

        return redirect()->route('cursos.lecciones.index', $curso)->with('status', 'Lección creada.');
    }

    public function edit(Curso $curso, Leccion $leccion)
    {
        $this->comprobarQuePertenece($curso, $leccion);

        return view('lecciones.edit', compact('curso', 'leccion'));
    }

    public function update(LeccionRequest $request, Curso $curso, Leccion $leccion)
    {
        $this->comprobarQuePertenece($curso, $leccion);

        $leccion->update($request->validated());

        return redirect()->route('cursos.lecciones.index', $curso)->with('status', 'Lección actualizada.');
    }

    public function confirmarEliminacion(Curso $curso, Leccion $leccion)
    {
        $this->comprobarQuePertenece($curso, $leccion);

        return view('lecciones.eliminar', compact('curso', 'leccion'));
    }

    public function destroy(Curso $curso, Leccion $leccion)
    {
        $this->comprobarQuePertenece($curso, $leccion);

        try {
            $leccion->delete();
        } catch (QueryException $e) {
            // 23000: una FK con RESTRICT lo impide (progreso de empleados en esta lección).
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            return redirect()->route('cursos.lecciones.index', $curso)
                ->with('error', "No se puede eliminar «{$leccion->titulo}»: hay empleados con progreso registrado en esta lección.");
        }

        return redirect()->route('cursos.lecciones.index', $curso)->with('status', 'Lección eliminada.');
    }

    /**
     * Evita editar una lección a través de la URL de otro curso.
     */
    private function comprobarQuePertenece(Curso $curso, Leccion $leccion): void
    {
        abort_unless((int) $leccion->curso_id === (int) $curso->id, 404);
    }
}
