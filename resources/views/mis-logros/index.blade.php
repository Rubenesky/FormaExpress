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
                            <td>
                                <svg class="fx-icono fx-icono-primario me-1" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" focusable="false"><circle cx="8" cy="6" r="4.5"/><path d="M5.2 9.6 4 15.5l4-1.8 4 1.8-1.2-5.9a5.6 5.6 0 0 1-5.6 0z"/></svg>
                                {{ $certificado->curso->titulo }}
                            </td>
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

    <h2 class="h5 mb-3">Insignias</h2>
    <div class="row g-4">
        @foreach ($catalogo as $insignia)
            @php($conseguida = $conseguidas->get($insignia->id))
            <div class="col-sm-6 col-lg-3">
                <div class="card h-100 {{ $conseguida ? 'shadow-sm fx-tarjeta fx-tarjeta-conseguida' : 'text-muted' }}">
                    <div class="card-body p-4">
                        <h3 class="h6 card-title">
                            @if ($conseguida)
                                <span class="fx-icono-acento" aria-hidden="true">&#9733;</span>
                            @endif
                            {{ $insignia->nombre }}
                        </h3>
                        @if ($conseguida)
                            <p class="mb-2"><span class="badge fx-badge-acento">Conseguida</span></p>
                        @endif
                        <p class="card-text small mb-2">{{ $insignia->descripcion }}</p>
                        @if ($conseguida)
                            <p class="card-text small text-muted mb-0">{{ $conseguida->pivot->created_at->format('d/m/Y') }}</p>
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
