@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-1 d-flex align-items-center gap-2">
        Registro de consultas
        @include('partials.ayuda', ['id' => 'ayuda-registro-consultas', 'titulo' => 'Registro de consultas', 'parrafos' => [
            'Esta pantalla, solo para administradores, registra quién ha abierto la ficha de qué empleado desde Consulta, y cuándo.',
            'Es un registro exigido por protección de datos (RGPD): no se puede editar ni borrar desde la aplicación.',
            'Se muestra de 25 en 25, empezando por la consulta más reciente.',
        ]])
    </h1>
    <p class="text-muted">Quién ha consultado la ficha de formación de qué empleado y cuándo (RGPD). Este registro no se puede editar ni borrar.</p>

    @if ($registros->isEmpty())
        <div class="alert alert-info">Todavía no hay consultas registradas.</div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Consultado por</th>
                        <th>Rol</th>
                        <th>Empleado consultado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registros as $registro)
                        <tr>
                            <td class="text-nowrap">{{ $registro->consultado_en->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $registro->consultor->name }} <span class="text-muted small">({{ $registro->consultor->email }})</span></td>
                            <td>{{ $registro->consultor->rol === 'administrador' ? 'Administrador' : 'Encargado' }}</td>
                            <td>{{ $registro->consultado->name }} <span class="text-muted small">({{ $registro->consultado->email }})</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $registros->links() }}
    @endif
</div>
@endsection
