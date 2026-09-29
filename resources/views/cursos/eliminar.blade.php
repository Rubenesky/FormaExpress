@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Eliminar curso</div>
                <div class="card-body">
                    <p>Vas a eliminar el curso <strong>{{ $curso->titulo }}</strong>.</p>

                    {{-- Mismo patrón que evaluaciones/eliminar: si hay datos de empleados, se avisa antes y no se ofrece el borrado. --}}
                    @if (array_sum($bloqueos) > 0)
                        <div class="alert alert-danger" role="alert">
                            No se puede eliminar: el curso tiene datos de empleados asociados.
                            <ul class="mb-0 mt-2">
                                @if ($bloqueos['asignaciones'] > 0)
                                    <li>{{ $bloqueos['asignaciones'] }} {{ $bloqueos['asignaciones'] === 1 ? 'empleado asignado' : 'empleados asignados' }}</li>
                                @endif
                                @if ($bloqueos['progreso'] > 0)
                                    <li>{{ $bloqueos['progreso'] }} {{ $bloqueos['progreso'] === 1 ? 'lección completada' : 'lecciones completadas' }} por empleados</li>
                                @endif
                                @if ($bloqueos['resultados'] > 0)
                                    <li>{{ $bloqueos['resultados'] }} {{ $bloqueos['resultados'] === 1 ? 'intento' : 'intentos' }} de evaluación</li>
                                @endif
                                @if ($bloqueos['certificados'] > 0)
                                    <li>{{ $bloqueos['certificados'] }} {{ $bloqueos['certificados'] === 1 ? 'certificado emitido' : 'certificados emitidos' }}</li>
                                @endif
                            </ul>
                        </div>
                        <a href="{{ route('cursos.index') }}" class="btn btn-secondary">Volver</a>
                    @else
                        <div class="alert alert-warning" role="alert">
                            @if ($curso->lecciones_count === 1)
                                <strong>Se borrará también su única lección.</strong>
                            @elseif ($curso->lecciones_count > 1)
                                <strong>Se borrarán también sus {{ $curso->lecciones_count }} lecciones.</strong>
                            @else
                                Este curso no tiene lecciones.
                            @endif
                            @if ($curso->evaluacion)
                                <strong>
                                    También se borrará su evaluación
                                    @if ($curso->evaluacion->preguntas_count === 0)
                                        (sin preguntas).
                                    @elseif ($curso->evaluacion->preguntas_count === 1)
                                        y su única pregunta.
                                    @else
                                        y sus {{ $curso->evaluacion->preguntas_count }} preguntas.
                                    @endif
                                </strong>
                            @endif
                            Esta acción no se puede deshacer.
                        </div>

                        <form method="POST" action="{{ route('cursos.destroy', $curso) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Sí, eliminar el curso</button>
                            <a href="{{ route('cursos.index') }}" class="btn btn-link">Cancelar</a>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
