@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Eliminar lección</div>
                <div class="card-body">
                    <p>
                        Vas a eliminar la lección <strong>{{ $leccion->titulo }}</strong>
                        del curso <strong>{{ $curso->titulo }}</strong>.
                    </p>

                    <div class="alert alert-warning" role="alert">Esta acción no se puede deshacer.</div>

                    <form method="POST" action="{{ route('cursos.lecciones.destroy', [$curso, $leccion]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Sí, eliminar la lección</button>
                        <a href="{{ route('cursos.lecciones.index', $curso) }}" class="btn btn-link">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
