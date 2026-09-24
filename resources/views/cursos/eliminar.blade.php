@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Eliminar curso</div>
                <div class="card-body">
                    <p>Vas a eliminar el curso <strong>{{ $curso->titulo }}</strong>.</p>

                    <div class="alert alert-warning" role="alert">
                        @if ($curso->lecciones_count === 1)
                            <strong>Se borrará también su única lección.</strong>
                        @elseif ($curso->lecciones_count > 1)
                            <strong>Se borrarán también sus {{ $curso->lecciones_count }} lecciones.</strong>
                        @else
                            Este curso no tiene lecciones.
                        @endif
                        Esta acción no se puede deshacer.
                    </div>

                    <form method="POST" action="{{ route('cursos.destroy', $curso) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Sí, eliminar el curso</button>
                        <a href="{{ route('cursos.index') }}" class="btn btn-link">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
