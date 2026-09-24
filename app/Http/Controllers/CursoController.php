<?php

namespace App\Http\Controllers;

use App\Http\Requests\CursoRequest;
use App\Models\Curso;
use Illuminate\Database\QueryException;

class CursoController extends Controller
{
    public function index()
    {
        $cursos = Curso::withCount('lecciones')->orderBy('titulo')->paginate(10);

        return view('cursos.index', compact('cursos'));
    }

    public function create()
    {
        return view('cursos.create', ['curso' => new Curso]);
    }

    public function store(CursoRequest $request)
    {
        Curso::create($request->validated());

        return redirect()->route('cursos.index')->with('status', 'Curso creado.');
    }

    public function edit(Curso $curso)
    {
        return view('cursos.edit', compact('curso'));
    }

    public function update(CursoRequest $request, Curso $curso)
    {
        $curso->update($request->validated());

        return redirect()->route('cursos.index')->with('status', 'Curso actualizado.');
    }

    /**
     * Página de confirmación: avisa de que se borrarán también sus lecciones.
     */
    public function confirmarEliminacion(Curso $curso)
    {
        $curso->loadCount('lecciones');

        return view('cursos.eliminar', compact('curso'));
    }

    public function destroy(Curso $curso)
    {
        try {
            // La BD borra en cascada sus lecciones.
            $curso->delete();
        } catch (QueryException $e) {
            // 23000: una FK con RESTRICT lo impide (historial de empleados asociado).
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            return redirect()->route('cursos.index')
                ->with('error', "No se puede eliminar «{$curso->titulo}»: hay datos de empleados asociados a este curso.");
        }

        return redirect()->route('cursos.index')->with('status', 'Curso eliminado junto con sus lecciones.');
    }
}
