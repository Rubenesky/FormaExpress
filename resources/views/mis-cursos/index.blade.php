@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-3">Mis cursos</h1>

    @if ($cursos->isEmpty())
        <div class="alert alert-info">Todavía no tienes cursos asignados.</div>
    @else
        <div class="row g-4">
            @foreach ($cursos as $curso)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm fx-tarjeta">
                        <div class="card-body d-flex flex-column p-4">
                            <h2 class="h5 card-title mb-2">{{ $curso->titulo }}</h2>
                            @if ($certificados->contains($curso->id))
                                <p class="mb-2">
                                    <span class="badge text-bg-primary">
                                        <svg class="fx-icono" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" focusable="false"><circle cx="8" cy="6" r="4.5"/><path d="M5.2 9.6 4 15.5l4-1.8 4 1.8-1.2-5.9a5.6 5.6 0 0 1-5.6 0z"/></svg>
                                        Certificado</span>
                                </p>
                            @endif
                            @if ($curso->descripcion)
                                <p class="card-text text-muted mb-4">{{ Str::limit($curso->descripcion, 120) }}</p>
                            @endif

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between small mb-2">
                                    <span>{{ $curso->completadas_count }} de {{ $curso->lecciones_count }} lecciones</span>
                                    <span class="fw-semibold">{{ $curso->porcentajeProgreso() }}%</span>
                                </div>
                                <div class="progress mb-4" role="progressbar" aria-label="Progreso del curso"
                                     aria-valuenow="{{ $curso->porcentajeProgreso() }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $curso->porcentajeProgreso() }}%"></div>
                                </div>
                                <a href="{{ route('mis-cursos.show', $curso) }}" class="btn btn-outline-primary btn-sm">Ver curso</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
