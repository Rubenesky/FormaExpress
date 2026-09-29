<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/**
 * Descarga del certificado en PDF (DomPDF). Acceso: Gate ver-certificado.
 */
class CertificadoController extends Controller
{
    public function pdf(Certificado $certificado)
    {
        $certificado->load(['empleado', 'curso']);

        return Pdf::loadView('certificados.pdf', compact('certificado'))
            ->setPaper('a4', 'landscape')
            // Incrusta solo los caracteres usados de la fuente (si no, el PDF pesa ~900 KB).
            ->setOption('enable_font_subsetting', true)
            ->download('certificado-'.Str::slug($certificado->curso->titulo).'.pdf');
    }
}
