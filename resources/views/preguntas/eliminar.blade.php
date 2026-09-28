@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Eliminar pregunta</div>
                <div class="card-body">
                    <p>Vas a eliminar esta pregunta de <strong>{{ $evaluacion->titulo }}</strong>:</p>
                    <blockquote class="border-start ps-3" style="white-space: pre-line">{{ $pregunta->enunciado }}</blockquote>

                    <div class="alert alert-warning" role="alert">
                        Se borrarán también sus opciones. Esta acción no se puede deshacer.
                        @if ($evaluacion->resultados_count > 0)
                            <br>Ya hay intentos de empleados: sus notas se calcularon con esta pregunta.
                        @endif
                    </div>

                    <form method="POST" action="{{ route('cursos.evaluacion.preguntas.destroy', [$curso, $pregunta]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Sí, eliminar la pregunta</button>
                        <a href="{{ route('cursos.evaluacion.show', $curso) }}" class="btn btn-link">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
