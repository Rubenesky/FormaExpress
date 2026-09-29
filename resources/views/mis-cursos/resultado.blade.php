@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('mis-cursos.index') }}">Mis cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('mis-cursos.show', $curso) }}">{{ $curso->titulo }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Resultado</li>
        </ol>
    </nav>

    <h1 class="h3 mb-3">{{ $evaluacion->titulo }}</h1>

    <div class="card mb-4 {{ $resultado->aprobado ? 'border-success' : 'border-danger' }}">
        <div class="card-body">
            <p class="display-6 mb-1">{{ number_format((float) $resultado->nota, 2, ',', '') }} <span class="fs-5 text-muted">/ 10</span></p>
            @if ($resultado->aprobado)
                <p class="h5 text-success mb-2">Aprobado</p>
            @else
                <p class="h5 text-danger mb-2">Suspendido</p>
            @endif
            <p class="text-muted mb-0">
                @if ($reciente && $aciertos !== null)
                    {{ $aciertos }} de {{ $totalPreguntas }} {{ $totalPreguntas === 1 ? 'acierto' : 'aciertos' }} ·
                @endif
                Intento {{ $intentos->count() }} · {{ $resultado->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>

    @if ($certificadoNuevo)
        <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
            <span><strong>¡Enhorabuena!</strong> Has obtenido el certificado del curso.</span>
            <a href="{{ route('certificados.pdf', $certificadoNuevo) }}" class="btn btn-sm btn-success">Descargar certificado</a>
        </div>
    @endif

    @if ($insigniasNuevas->isNotEmpty())
        <div class="alert alert-info" role="alert">
            {{ $insigniasNuevas->count() === 1 ? 'Nueva insignia:' : 'Nuevas insignias:' }}
            <strong>{{ $insigniasNuevas->implode(', ') }}</strong>.
            <a href="{{ route('mis-logros.index') }}">Ver mis logros</a>
        </div>
    @endif

    @if ($reciente)
        @if ($falladas->isEmpty())
            <div class="alert alert-success">Has acertado todas las preguntas.</div>
        @else
            <h2 class="h5">Preguntas falladas</h2>
            <p class="text-muted small">Repasa estas preguntas antes de volver a intentarlo.</p>
            <ul class="list-group mb-4">
                @foreach ($falladas as $pregunta)
                    <li class="list-group-item" style="white-space: pre-line">{{ $pregunta->enunciado }}</li>
                @endforeach
            </ul>
        @endif
    @endif

    <div class="d-flex flex-wrap gap-2 mb-4">
        @can('hacer-evaluacion', $curso)
            <a href="{{ route('mis-cursos.evaluacion.formulario', $curso) }}" class="btn btn-outline-primary">
                {{ $resultado->aprobado ? 'Repetir la evaluación' : 'Volver a intentarlo' }}
            </a>
        @endcan
        <a href="{{ route('mis-cursos.show', $curso) }}" class="btn btn-link">Volver al curso</a>
    </div>

    @if ($intentos->count() > 1)
        <h2 class="h6">Tus intentos</h2>
        <table class="table table-sm w-auto">
            <thead>
                <tr><th>Fecha</th><th class="text-end">Nota</th><th>Resultado</th></tr>
            </thead>
            <tbody>
                @foreach ($intentos as $intento)
                    <tr>
                        <td>{{ $intento->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">{{ number_format((float) $intento->nota, 2, ',', '') }}</td>
                        <td>{{ $intento->aprobado ? 'Aprobado' : 'Suspendido' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
