@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-3 d-flex align-items-center gap-2">
        Consulta de formación
        @include('partials.ayuda', ['id' => 'ayuda-consulta', 'titulo' => 'Consulta', 'parrafos' => [
            'Esta pantalla busca empleados por su nombre (al menos 2 letras) para comprobar su formación.',
            'Desde los resultados, el botón de cada fila abre la ficha del empleado: sus cursos, su progreso, sus certificados y sus insignias, sin que puedas editar nada.',
            'Abrir una ficha queda registrado por protección de datos (RGPD); la búsqueda en sí no se registra porque no muestra datos de formación.',
        ]])
    </h1>
    <p class="text-muted">Busca a un empleado por su nombre para ver sus cursos, su progreso, sus certificados y sus insignias.</p>

    {{-- Búsqueda por GET: no modifica nada. --}}
    <form method="GET" action="{{ route('consulta.index') }}" class="row g-2 mb-4" role="search">
        <div class="col-md-8">
            <label for="q" class="visually-hidden">Nombre del empleado</label>
            <input id="q" type="search" name="q" class="form-control" maxlength="100"
                   placeholder="Nombre del empleado" value="{{ $busqueda }}" autofocus>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    @if ($empleados === null)
        @if ($busqueda !== '')
            <div class="alert alert-info">Escribe al menos {{ \App\Http\Controllers\ConsultaController::MINIMO_BUSQUEDA }} caracteres.</div>
        @endif
    @elseif ($empleados->isEmpty())
        <div class="alert alert-info">No hay empleados cuyo nombre contenga «{{ $busqueda }}».</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Correo electrónico</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($empleados as $empleado)
                        <tr>
                            <td>{{ $empleado->name }}</td>
                            <td>{{ $empleado->email }}</td>
                            <td class="text-end">
                                <a href="{{ route('consulta.ficha', $empleado) }}" class="btn btn-sm btn-outline-secondary">Ver ficha</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $empleados->links() }}
    @endif
</div>
@endsection
