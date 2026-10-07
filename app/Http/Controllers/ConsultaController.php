<?php

namespace App\Http\Controllers;

use App\Models\RegistroConsulta;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Consulta de solo lectura de la formación de los empleados (encargado y
 * administrador, Gate consultar). Nada editable: solo pantallas GET.
 *
 * La búsqueda solo muestra nombre y correo; los datos de formación se ven en
 * la ficha de cada empleado.
 */
class ConsultaController extends Controller
{
    /** Caracteres mínimos para buscar (sin búsqueda no se lista a nadie). */
    public const MINIMO_BUSQUEDA = 2;

    public function index(Request $request)
    {
        $busqueda = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $empleados = null;

        if (mb_strlen($busqueda) >= self::MINIMO_BUSQUEDA) {
            // Se escapan los comodines de LIKE para buscar el texto tal cual.
            $patron = '%'.addcslashes($busqueda, '%_\\').'%';

            $empleados = User::where('rol', 'empleado')
                ->where('name', 'like', $patron)
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString();
        }

        return view('consulta.index', compact('busqueda', 'empleados'));
    }

    public function ficha(Request $request, User $empleado)
    {
        // Solo se consultan empleados (no administradores ni encargados).
        abort_unless($empleado->rol === 'empleado', 404);

        // Registro RGPD: cada apertura de la ficha queda anotada, sin intervención del usuario.
        RegistroConsulta::create([
            'consultor_id' => $request->user()->id,
            'consultado_id' => $empleado->id,
            'consultado_en' => now(),
        ]);

        $cursos = $empleado->cursosAsignados()
            ->withCount([
                'lecciones',
                'lecciones as completadas_count' => fn ($q) => $q->whereHas('completadaPor', fn ($q) => $q->whereKey($empleado->id)),
            ])
            ->orderBy('titulo')
            ->get();

        $certificados = $empleado->certificados()->get()->keyBy('curso_id');
        $insignias = $empleado->insignias()->orderByPivot('created_at')->get();

        return view('consulta.ficha', compact('empleado', 'cursos', 'certificados', 'insignias'));
    }
}
