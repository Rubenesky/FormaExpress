@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $curso->titulo }}</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Lecciones de «{{ $curso->titulo }}»</h1>
        <a href="{{ route('cursos.lecciones.create', $curso) }}" class="btn btn-primary">Nueva lección</a>
    </div>

    @if ($lecciones->isEmpty())
        <div class="alert alert-info">Este curso todavía no tiene lecciones.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">Orden</th>
                        <th>Título</th>
                        <th>Última modificación</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lecciones as $leccion)
                        <tr>
                            <td class="text-center">{{ $leccion->orden }}</td>
                            <td>{{ $leccion->titulo }}</td>
                            <td class="text-muted">{{ $leccion->updated_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('cursos.lecciones.edit', [$curso, $leccion]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <a href="{{ route('cursos.lecciones.eliminar', [$curso, $leccion]) }}" class="btn btn-sm btn-outline-danger">Eliminar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $lecciones->links() }}
    @endif
</div>
@endsection
