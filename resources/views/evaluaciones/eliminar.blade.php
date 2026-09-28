@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Eliminar evaluación</div>
                <div class="card-body">
                    <p>Vas a eliminar la evaluación <strong>{{ $evaluacion->titulo }}</strong> del curso <strong>{{ $curso->titulo }}</strong>.</p>

                    @if ($evaluacion->resultados_count > 0)
                        <div class="alert alert-danger" role="alert">
                            No se puede eliminar: hay {{ $evaluacion->resultados_count }}
                            {{ $evaluacion->resultados_count === 1 ? 'intento' : 'intentos' }} de empleados registrados.
                        </div>
                        <a href="{{ route('cursos.evaluacion.show', $curso) }}" class="btn btn-secondary">Volver</a>
                    @else
                        <div class="alert alert-warning" role="alert">
                            @if ($evaluacion->preguntas_count === 1)
                                <strong>Se borrará también su única pregunta y sus opciones.</strong>
                            @elseif ($evaluacion->preguntas_count > 1)
                                <strong>Se borrarán también sus {{ $evaluacion->preguntas_count }} preguntas y sus opciones.</strong>
                            @else
                                La evaluación no tiene preguntas.
                            @endif
                            Esta acción no se puede deshacer.
                        </div>

                        <form method="POST" action="{{ route('cursos.evaluacion.destroy', $curso) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Sí, eliminar la evaluación</button>
                            <a href="{{ route('cursos.evaluacion.show', $curso) }}" class="btn btn-link">Cancelar</a>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
