@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cursos.evaluacion.show', $curso) }}">{{ $evaluacion->titulo }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar pregunta</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">Editar pregunta</div>
        <div class="card-body">
            <form method="POST" action="{{ route('cursos.evaluacion.preguntas.update', [$curso, $pregunta]) }}">
                @method('PUT')
                @include('preguntas._form')
            </form>
        </div>
    </div>
</div>
@endsection
