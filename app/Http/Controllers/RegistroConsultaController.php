<?php

namespace App\Http\Controllers;

use App\Models\RegistroConsulta;

/**
 * Registro RGPD de consultas, solo lectura y solo para administradores
 * (Gate administrar). No hay rutas para editar ni borrar filas.
 */
class RegistroConsultaController extends Controller
{
    public function index()
    {
        $registros = RegistroConsulta::with(['consultor', 'consultado'])
            ->orderByDesc('consultado_en')
            ->orderByDesc('id')
            ->paginate(25);

        return view('registro-consultas.index', compact('registros'));
    }
}
