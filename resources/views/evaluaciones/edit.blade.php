@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Editar la evaluación de «{{ $curso->titulo }}»</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cursos.evaluacion.update', $curso) }}">
                        @method('PUT')
                        @include('evaluaciones._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
