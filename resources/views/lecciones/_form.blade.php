@csrf

<div class="row">
    <div class="col-md-9 mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input id="titulo" type="text" name="titulo" maxlength="255" required autofocus
               class="form-control @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $leccion->titulo) }}">
        @error('titulo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label for="orden" class="form-label">Orden</label>
        <input id="orden" type="number" name="orden" min="1" max="65535" required
               class="form-control @error('orden') is-invalid @enderror"
               value="{{ old('orden', $leccion->orden) }}">
        @error('orden')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="contenido" class="form-label">Contenido</label>
    <textarea id="contenido" name="contenido" rows="12" required
              class="form-control @error('contenido') is-invalid @enderror">{{ old('contenido', $leccion->contenido) }}</textarea>
    @error('contenido')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary">Guardar</button>
<a href="{{ route('cursos.lecciones.index', $curso) }}" class="btn btn-link">Cancelar</a>
