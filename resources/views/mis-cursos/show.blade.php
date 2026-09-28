@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('mis-cursos.index') }}">Mis cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $curso->titulo }}</li>
        </ol>
    </nav>

    <h1 class="h3">{{ $curso->titulo }}</h1>
    @if ($curso->descripcion)
        <p class="text-muted" style="white-space: pre-line">{{ $curso->descripcion }}</p>
    @endif

    <div class="d-flex justify-content-between small mb-1">
        <span>{{ $curso->completadas_count }} de {{ $curso->lecciones_count }} lecciones completadas</span>
        <span>{{ $curso->porcentajeProgreso() }}%</span>
    </div>
    <div class="progress mb-4" role="progressbar" aria-label="Progreso del curso"
         aria-valuenow="{{ $curso->porcentajeProgreso() }}" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar @if ($curso->porcentajeProgreso() === 100) bg-success @endif"
             style="width: {{ $curso->porcentajeProgreso() }}%"></div>
    </div>

    @if ($lecciones->isEmpty())
        <div class="alert alert-info">Este curso todavía no tiene lecciones.</div>
    @else
        <ol class="list-group list-group-numbered">
            @foreach ($lecciones as $leccion)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('mis-lecciones.show', $leccion) }}" class="ms-2 me-auto">{{ $leccion->titulo }}</a>
                    @if ($completadas->contains($leccion->id))
                        <span class="badge text-bg-success">Completada</span>
                    @else
                        <span class="badge text-bg-secondary">Pendiente</span>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif

    {{-- Evaluación: solo si existe y tiene preguntas. Se desbloquea al completar todas las lecciones. --}}
    @if ($evaluacion && $evaluacion->preguntas_count > 0)
        <div class="card mt-4">
            <div class="card-body">
                <h2 class="h5">Evaluación</h2>

                @if ($intentos->isNotEmpty())
                    <p class="mb-2">
                        Último intento: <strong>{{ number_format((float) $intentos->first()->nota, 2, ',', '') }}</strong> / 10 ·
                        {{ $intentos->first()->aprobado ? 'Aprobado' : 'Suspendido' }}
                        <span class="text-muted">({{ $intentos->count() }} {{ $intentos->count() === 1 ? 'intento' : 'intentos' }})</span>
                        · <a href="{{ route('mis-cursos.evaluacion.resultado', $curso) }}">Ver resultado</a>
                    </p>
                @endif

                @can('hacer-evaluacion', $curso)
                    <a href="{{ route('mis-cursos.evaluacion.formulario', $curso) }}" class="btn btn-primary">
                        {{ $intentos->isEmpty() ? 'Hacer la evaluación' : 'Volver a hacer la evaluación' }}
                    </a>
                @elseif ($lecciones->isEmpty())
                    <p class="text-muted mb-0">La evaluación estará disponible cuando el curso tenga lecciones.</p>
                @else
                    <p class="text-muted mb-0">Completa todas las lecciones para desbloquear la evaluación.</p>
                @endcan
            </div>
        </div>
    @endif
</div>
@endsection
