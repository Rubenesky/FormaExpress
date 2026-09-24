@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cursos.lecciones.index', $curso) }}">{{ $curso->titulo }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar lección</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">Editar lección</div>
        <div class="card-body">
            <form method="POST" action="{{ route('cursos.lecciones.update', [$curso, $leccion]) }}">
                @method('PUT')
                @include('lecciones._form')
            </form>
        </div>
    </div>
</div>
@endsection
