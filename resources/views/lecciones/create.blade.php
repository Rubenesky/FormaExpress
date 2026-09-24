@extends('layouts.app')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('cursos.index') }}">Cursos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cursos.lecciones.index', $curso) }}">{{ $curso->titulo }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Nueva lección</li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">Nueva lección</div>
        <div class="card-body">
            <form method="POST" action="{{ route('cursos.lecciones.store', $curso) }}">
                @include('lecciones._form')
            </form>
        </div>
    </div>
</div>
@endsection
