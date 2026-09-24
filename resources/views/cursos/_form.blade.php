@csrf

<div class="mb-3">
    <label for="titulo" class="form-label">Título</label>
    <input id="titulo" type="text" name="titulo" maxlength="255" required autofocus
           class="form-control @error('titulo') is-invalid @enderror"
           value="{{ old('titulo', $curso->titulo) }}">
    @error('titulo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="descripcion" class="form-label">Descripción <span class="text-muted">(opcional)</span></label>
    <textarea id="descripcion" name="descripcion" rows="4" maxlength="5000"
              class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $curso->descripcion) }}</textarea>
    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary">Guardar</button>
<a href="{{ route('cursos.index') }}" class="btn btn-link">Cancelar</a>
