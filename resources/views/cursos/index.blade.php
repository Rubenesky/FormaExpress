@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 d-flex align-items-center gap-2">
            Cursos
            @include('partials.ayuda', ['id' => 'ayuda-cursos', 'titulo' => 'Cursos', 'parrafos' => [
                'Esta pantalla lista todos los cursos del centro de formación: título, descripción y número de lecciones.',
                'Con «Nuevo curso» creas uno; desde los botones de cada fila entras a sus lecciones, sus asignaciones o su evaluación, lo editas o lo eliminas.',
                'Solo puedes eliminar un curso si ningún empleado tiene datos de formación en él (asignación, progreso, intentos o certificados); si los tiene, la aplicación te lo avisa en vez de borrarlo.',
            ]])
        </h1>
        <a href="{{ route('cursos.create') }}" class="btn btn-primary">Nuevo curso</a>
    </div>

    @if ($cursos->isEmpty())
        <div class="alert alert-info">Todavía no hay cursos.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th class="text-center">Lecciones</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cursos as $curso)
                        <tr>
                            <td>{{ $curso->titulo }}</td>
                            <td class="text-muted">{{ Str::limit($curso->descripcion, 80) }}</td>
                            <td class="text-center">{{ $curso->lecciones_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('cursos.lecciones.index', $curso) }}" class="btn btn-sm btn-outline-secondary">Lecciones</a>
                                <a href="{{ route('cursos.asignaciones.index', $curso) }}" class="btn btn-sm btn-outline-secondary">Asignaciones</a>
                                <a href="{{ route('cursos.evaluacion.show', $curso) }}" class="btn btn-sm btn-outline-secondary">Evaluación</a>
                                <a href="{{ route('cursos.edit', $curso) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <a href="{{ route('cursos.eliminar', $curso) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $cursos->links() }}
    @endif
</div>
@endsection
