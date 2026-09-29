<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado {{ $certificado->codigo() }}</title>
    {{-- DomPDF no carga CSS externo: estilos en línea. DejaVu Sans (incluida en DomPDF) cubre tildes y eñes. --}}
    <style>
        @page { margin: 0; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; color: #1f2937; }
        .marco { position: absolute; top: 28px; right: 28px; bottom: 28px; left: 28px; border: 6px double #0d6efd; }
        .contenido { position: absolute; top: 150px; left: 90px; right: 90px; text-align: center; }
        .marca { font-size: 16px; letter-spacing: 4px; color: #0d6efd; text-transform: uppercase; }
        h1 { font-size: 40px; margin: 18px 0 30px; font-weight: normal; }
        .texto { font-size: 16px; margin: 6px 0; }
        .nombre { font-size: 30px; font-weight: bold; margin: 12px 0 18px; }
        .curso { font-size: 24px; margin: 12px 0 30px; }
        /* DomPDF no da ancho a una tabla posicionada: se posiciona el div y la tabla ocupa el 100%. */
        .pie { position: absolute; bottom: 70px; left: 90px; right: 90px; font-size: 12px; color: #6b7280; }
        .pie table { width: 100%; border-collapse: collapse; }
        .pie td { width: 50%; padding: 0; }
    </style>
</head>
<body>
    <div class="marco"></div>
    <div class="contenido">
        <div class="marca">FormaExpress</div>
        <h1>Certificado de formación</h1>
        <p class="texto">Se certifica que</p>
        <p class="nombre">{{ $certificado->empleado->name }}</p>
        <p class="texto">ha superado la evaluación del curso</p>
        <p class="curso">«{{ $certificado->curso->titulo }}»</p>
    </div>
    <div class="pie">
        <table>
            <tr>
                <td style="text-align: left">Fecha de emisión: {{ $certificado->fecha_emision->format('d/m/Y') }}</td>
                <td style="text-align: right">Código: {{ $certificado->codigo() }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
