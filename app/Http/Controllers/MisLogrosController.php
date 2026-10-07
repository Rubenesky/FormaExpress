<?php

namespace App\Http\Controllers;

use App\Models\Insignia;
use Illuminate\Http\Request;

/**
 * "Mis logros" del empleado: sus certificados y sus insignias.
 * Acceso: Gate estudiar.
 */
class MisLogrosController extends Controller
{
    public function index(Request $request)
    {
        $empleado = $request->user();

        $certificados = $empleado->certificados()->with('curso')->orderByDesc('fecha_emision')->orderByDesc('id')->get();
        $conseguidas = $empleado->insignias()->get()->keyBy('id');

        return view('mis-logros.index', [
            'certificados' => $certificados,
            'conseguidas' => $conseguidas,
            'catalogo' => Insignia::orderBy('id')->get(),
        ]);
    }
}
