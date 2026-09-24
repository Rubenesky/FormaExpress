@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Editar curso</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cursos.update', $curso) }}">
                        @method('PUT')
                        @include('cursos._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
