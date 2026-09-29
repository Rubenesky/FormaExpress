@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="h3 mb-1">Registro de consultas</h1>
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
