@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $curso->titulo }}</li>
        </ol>
    </nav>

    <h1 class="h3 mb-3 d-flex align-items-center gap-2">
        Empleados asignados a «{{ $curso->titulo }}»
        @include('partials.ayuda', ['id' => 'ayuda-asignaciones', 'titulo' => 'Asignaciones', 'parrafos' => [
            'Esta pantalla asigna el curso a empleados y muestra quién lo tiene ya asignado.',
            'El desplegable solo ofrece empleados que todavía no tienen el curso; al asignarlo se guarda la fecha.',
            'La lista también indica si cada empleado ya tiene el certificado del curso, con un enlace para descargarlo.',
            'Puedes desasignar un curso, pero no si el empleado ya ha completado alguna lección.',
        ]])
    </h1>

    <div class="card mb-4">
        <div class="card-header">Asignar el curso a un empleado</div>
        <div class="card-body">
            @if ($disponibles->isEmpty())
                {{-- Sin selector no hay dónde pintar el error: se muestra aquí. --}}
                @error('empleado_id')
                    <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror
                <p class="mb-0 text-muted">Todos los empleados tienen ya este curso asignado.</p>
            @else
                <form method="POST" action="{{ route('cursos.asignaciones.store', $curso) }}" class="row g-2 align-items-start">
                    @csrf
                    <div class="col-md-8">
                        <label for="empleado_id" class="visually-hidden">Empleado</label>
                        <select id="empleado_id" name="empleado_id" required
                                class="form-select @error('empleado_id') is-invalid @enderror">
                            <option value="">Elige un empleado…</option>
                            @foreach ($disponibles as $empleado)
                                <option value="{{ $empleado->id }}" @selected(old('empleado_id') == $empleado->id)>
                                    {{ $empleado->name }} ({{ $empleado->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('empleado_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Asignar</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if ($asignados->isEmpty())
        <div class="alert alert-info">Este curso todavía no está asignado a ningún empleado.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Correo electrónico</th>
                        <th>Asignado el</th>
                        <th>Certificado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($asignados as $empleado)
                        <tr>
                            <td>{{ $empleado->name }}</td>
                            <td>{{ $empleado->email }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($empleado->pivot->fecha_asignacion)->format('d/m/Y') }}</td>
                            <td>
                                @if ($certificado = $certificados->get($empleado->id))
                                    {{ $certificado->fecha_emision->format('d/m/Y') }}
                                    · <a href="{{ route('certificados.pdf', $certificado) }}">PDF</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('cursos.asignaciones.eliminar', [$curso, $empleado]) }}" class="btn btn-sm btn-outline-danger">Desasignar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $asignados->links() }}
    @endif
</div>
@endsection
