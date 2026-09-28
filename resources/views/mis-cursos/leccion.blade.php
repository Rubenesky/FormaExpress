@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('mis-cursos.index') }}">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('mis-cursos.show', $curso) }}">{{ $curso->titulo }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $leccion->titulo }}</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <h1 class="h4 mb-0">{{ $leccion->titulo }}</h1>
        </div>
        {{-- pre-line conserva los saltos de línea sin salir del escapado {{ }} --}}
        <div class="card-body" style="white-space: pre-line">{{ $leccion->contenido }}</div>
        <div class="card-footer d-flex flex-wrap gap-2 align-items-center">
            @if ($completada)
                <span class="badge text-bg-success">
                    Completada el {{ \Illuminate\Support\Carbon::parse($completada->pivot->fecha_finalizacion)->format('d/m/Y H:i') }}
                </span>
            @else
                <form method="POST" action="{{ route('mis-lecciones.completar', $leccion) }}">
                    @csrf
                    <button type="submit" class="btn btn-success">Marcar como completada</button>
                </form>
            @endif

            <div class="ms-auto">
                <a href="{{ route('mis-cursos.show', $curso) }}" class="btn btn-link">Volver al curso</a>
                @if ($siguiente)
                    <a href="{{ route('mis-lecciones.show', $siguiente) }}" class="btn btn-outline-primary">Siguiente lección</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
