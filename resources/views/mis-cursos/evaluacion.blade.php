@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('mis-cursos.index') }}">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('mis-cursos.show', $curso) }}">{{ $curso->titulo }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Evaluación</li>
        </ol>
    </nav>

    <h1 class="h3 d-flex align-items-center gap-2">
        {{ $evaluacion->titulo }}
        @include('partials.ayuda', ['id' => 'ayuda-evaluacion-empleado', 'titulo' => 'Evaluación empleado', 'parrafos' => [
            'Esta es la evaluación del curso: responde una opción en cada pregunta y pulsa «Enviar respuestas». Si dejas alguna sin responder, ese envío no cuenta como intento.',
            'La nota es el porcentaje de aciertos sobre 10; los fallos no restan. Apruebas si llegas a la nota mínima indicada arriba.',
            'Puedes repetir la evaluación tantas veces como quieras: se guardan todos los intentos. Al aprobar por primera vez consigues el certificado del curso automáticamente.',
        ]])
    </h1>
    <p class="text-muted">
        {{ $evaluacion->preguntas->count() }} {{ $evaluacion->preguntas->count() === 1 ? 'pregunta' : 'preguntas' }}
        · Aprobado con {{ $evaluacion->nota_minima }} sobre 10 · Responde todas las preguntas.
    </p>

    @error('respuestas')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    {{-- Solo se envía el id de la opción elegida; la corrección se hace en el servidor. --}}
    <form method="POST" action="{{ route('mis-cursos.evaluacion.enviar', $curso) }}">
        @csrf

        @foreach ($evaluacion->preguntas as $pregunta)
            <fieldset class="card mb-3 @error('respuestas.'.$pregunta->id) border-danger @enderror">
                <div class="card-body">
                    <legend class="fs-6 fw-semibold" style="white-space: pre-line">{{ $loop->iteration }}. {{ $pregunta->enunciado }}</legend>

                    @foreach ($pregunta->opciones as $opcion)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" required
                                   name="respuestas[{{ $pregunta->id }}]" value="{{ $opcion->id }}"
                                   id="opcion_{{ $opcion->id }}"
                                   @checked((string) old('respuestas.'.$pregunta->id) === (string) $opcion->id)>
                            <label class="form-check-label" for="opcion_{{ $opcion->id }}">{{ $opcion->texto }}</label>
                        </div>
                    @endforeach

                    @error('respuestas.'.$pregunta->id)
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </fieldset>
        @endforeach

        <button type="submit" class="btn btn-acento">Enviar respuestas</button>
        <a href="{{ route('mis-cursos.show', $curso) }}" class="btn btn-link">Cancelar</a>
    </form>
</div>
@endsection
