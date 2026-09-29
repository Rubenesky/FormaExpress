@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('consulta.index') }}">Consulta</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $empleado->name }}</li>
        </ol>
    </nav>

    {{-- Ficha de solo lectura: ni enlaces de edición ni descarga del PDF. --}}
    <h1 class="h3 mb-1">{{ $empleado->name }}</h1>
    <p class="text-muted">{{ $empleado->email }}</p>

    <h2 class="h5 mt-4">Cursos asignados</h2>
    @if ($cursos->isEmpty())
        <div class="alert alert-info">No tiene cursos asignados.</div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th style="min-width: 12rem">Progreso</th>
                        <th>Certificado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cursos as $curso)
                        <tr>
                            <td>{{ $curso->titulo }}</td>
                            <td>
                                <div class="d-flex justify-content-between small">
                                    <span>{{ $curso->completadas_count }} de {{ $curso->lecciones_count }} lecciones</span>
                                    <span>{{ $curso->porcentajeProgreso() }}%</span>
                                </div>
                                <div class="progress" role="progressbar" aria-label="Progreso de {{ $curso->titulo }}"
                                     aria-valuenow="{{ $curso->porcentajeProgreso() }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar @if ($curso->porcentajeProgreso() === 100) bg-success @endif"
                                         style="width: {{ $curso->porcentajeProgreso() }}%"></div>
                                </div>
                            </td>
                            <td>
                                @if ($certificado = $certificados->get($curso->id))
                                    <span class="badge text-bg-success">Certificado</span>
                                    {{ $certificado->fecha_emision->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h2 class="h5 mt-4">Insignias</h2>
    @if ($insignias->isEmpty())
        <p class="text-muted">Todavía no tiene insignias.</p>
    @else
        <ul class="list-group">
            @foreach ($insignias as $insignia)
                <li class="list-group-item d-flex justify-content-between">
                    <span>{{ $insignia->nombre() }}</span>
                    <span class="text-muted small">{{ $insignia->created_at->format('d/m/Y') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
