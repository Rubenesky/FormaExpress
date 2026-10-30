@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $curso->titulo }}</li>
        </ol>
    </nav>

    @php($ayudaEvaluacionAdmin = [
        'id' => 'ayuda-evaluacion-admin',
        'titulo' => 'Evaluación admin',
        'parrafos' => [
            'Esta pantalla gestiona la evaluación tipo test del curso: cada curso tiene como mucho una.',
            'Cada pregunta admite hasta 6 opciones; hay que marcar cuál es la correcta. Aquí, a diferencia de la vista del empleado, sí se ve la respuesta correcta de cada pregunta.',
            'Puedes cambiar la nota mínima para aprobar y ver cuántos intentos han hecho ya los empleados.',
            'Editar o eliminar preguntas que ya tienen intentos cambia el significado de esas notas; la aplicación lo avisa, pero no lo impide.',
        ],
    ])

    @if (! $evaluacion)
        <h1 class="h3 mb-3 d-flex align-items-center gap-2">
            Evaluación de «{{ $curso->titulo }}»
            @include('partials.ayuda', $ayudaEvaluacionAdmin)
        </h1>
        <div class="alert alert-info">Este curso todavía no tiene evaluación.</div>
        <a href="{{ route('cursos.evaluacion.create', $curso) }}" class="btn btn-primary">Crear evaluación</a>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <h1 class="h3 mb-1 d-flex align-items-center gap-2">
                    {{ $evaluacion->titulo }}
                    @include('partials.ayuda', $ayudaEvaluacionAdmin)
                </h1>
                <p class="text-muted mb-0">
                    Nota mínima para aprobar: <strong>{{ $evaluacion->nota_minima }}</strong> sobre 10
                    · {{ $evaluacion->preguntas->count() }} {{ $evaluacion->preguntas->count() === 1 ? 'pregunta' : 'preguntas' }}
                    · {{ $evaluacion->resultados_count }} {{ $evaluacion->resultados_count === 1 ? 'intento' : 'intentos' }} de empleados
                </p>
            </div>
            <div class="text-nowrap">
                <a href="{{ route('cursos.evaluacion.edit', $curso) }}" class="btn btn-outline-primary">Editar</a>
                <a href="{{ route('cursos.evaluacion.eliminar', $curso) }}" class="btn btn-outline-danger">Eliminar</a>
            </div>
        </div>

        @if ($evaluacion->resultados_count > 0)
            <div class="alert alert-warning" role="alert">
                Ya hay intentos de empleados. Si cambias las preguntas, las notas anteriores
                dejarán de corresponder al test actual.
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">Preguntas</h2>
            <a href="{{ route('cursos.evaluacion.preguntas.create', $curso) }}" class="btn btn-primary btn-sm">Nueva pregunta</a>
        </div>

        @if ($evaluacion->preguntas->isEmpty())
            <div class="alert alert-info">La evaluación todavía no tiene preguntas. Los empleados no podrán hacerla hasta que tenga al menos una.</div>
        @else
            @foreach ($evaluacion->preguntas as $pregunta)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-2">
                            <p class="fw-semibold mb-2" style="white-space: pre-line">{{ $loop->iteration }}. {{ $pregunta->enunciado }}</p>
                            <div class="text-nowrap">
                                <a href="{{ route('cursos.evaluacion.preguntas.edit', [$curso, $pregunta]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <a href="{{ route('cursos.evaluacion.preguntas.eliminar', [$curso, $pregunta]) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0 ms-3">
                            @foreach ($pregunta->opciones as $opcion)
                                <li>
                                    @if ($opcion->es_correcta)
                                        <span class="badge text-bg-success">Correcta</span> <strong>{{ $opcion->texto }}</strong>
                                    @else
                                        <span class="text-muted">○</span> {{ $opcion->texto }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        @endif
    @endif
</div>
@endsection
