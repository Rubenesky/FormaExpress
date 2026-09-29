@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-4">Mis logros</h1>

    <h2 class="h5">Certificados</h2>
    @if ($certificados->isEmpty())
        <div class="alert alert-info">
            Todavía no tienes certificados. Se consiguen al aprobar la evaluación de un curso.
        </div>
    @else
        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Fecha de emisión</th>
                        <th>Código</th>
                        <th class="text-end">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($certificados as $certificado)
                        <tr>
                            <td>{{ $certificado->curso->titulo }}</td>
                            <td>{{ $certificado->fecha_emision->format('d/m/Y') }}</td>
                            <td class="text-muted">{{ $certificado->codigo() }}</td>
                            <td class="text-end">
                                <a href="{{ route('certificados.pdf', $certificado) }}" class="btn btn-sm btn-outline-primary">Descargar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h2 class="h5">Insignias</h2>
    <div class="row g-3">
        @foreach ($catalogo as $hito => $insignia)
            @php($conseguida = $conseguidas->get($hito))
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 {{ $conseguida ? 'border-success' : 'text-muted' }}">
                    <div class="card-body">
                        <h3 class="h6 card-title">
                            {{ $insignia['nombre'] }}
                            @if ($conseguida)
                                <span class="badge text-bg-success">Conseguida</span>
                            @endif
                        </h3>
                        <p class="card-text small mb-1">{{ $insignia['descripcion'] }}</p>
                        @if ($conseguida)
                            <p class="card-text small text-muted mb-0">{{ $conseguida->created_at->format('d/m/Y') }}</p>
                        @else
                            <p class="card-text small mb-0">Pendiente</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
