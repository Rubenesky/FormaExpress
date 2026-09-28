@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Desasignar curso</div>
                <div class="card-body">
                    <p>
                        Vas a quitar el curso <strong>{{ $curso->titulo }}</strong>
                        a <strong>{{ $empleado->name }}</strong>.
                    </p>

                    @if ($tieneProgreso)
                        <div class="alert alert-danger" role="alert">
                            No se puede desasignar: este empleado ya tiene progreso en el curso.
                        </div>
                        <a href="{{ route('cursos.asignaciones.index', $curso) }}" class="btn btn-secondary">Volver</a>
                    @else
                        <div class="alert alert-warning" role="alert">El empleado dejará de ver este curso.</div>

                        <form method="POST" action="{{ route('cursos.asignaciones.destroy', [$curso, $empleado]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Sí, desasignar</button>
                            <a href="{{ route('cursos.asignaciones.index', $curso) }}" class="btn btn-link">Cancelar</a>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
