@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-3">Mis cursos</h1>

    @if ($cursos->isEmpty())
        <div class="alert alert-info">Todavía no tienes cursos asignados.</div>
    @else
        <div class="row g-3">
            @foreach ($cursos as $curso)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h2 class="h5 card-title">{{ $curso->titulo }}</h2>
                            @if ($curso->descripcion)
                                <p class="card-text text-muted">{{ Str::limit($curso->descripcion, 120) }}</p>
                            @endif

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>{{ $curso->completadas_count }} de {{ $curso->lecciones_count }} lecciones</span>
                                    <span>{{ $curso->porcentajeProgreso() }}%</span>
                                </div>
                                <div class="progress mb-3" role="progressbar" aria-label="Progreso del curso"
                                     aria-valuenow="{{ $curso->porcentajeProgreso() }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar @if ($curso->porcentajeProgreso() === 100) bg-success @endif"
                                         style="width: {{ $curso->porcentajeProgreso() }}%"></div>
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
