@csrf

<div class="mb-3">
    <label for="titulo" class="form-label">Título</label>
    <input id="titulo" type="text" name="titulo" maxlength="255" required autofocus
           class="form-control @error('titulo') is-invalid @enderror"
           value="{{ old('titulo', $evaluacion->titulo) }}">
    @error('titulo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="nota_minima" class="form-label">Nota mínima para aprobar (0 a 10)</label>
    <input id="nota_minima" type="number" name="nota_minima" min="0" max="10" step="0.01" required
           class="form-control @error('nota_minima') is-invalid @enderror" style="max-width: 10rem"
           value="{{ old('nota_minima', $evaluacion->nota_minima) }}">
    @error('nota_minima')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Nota = aciertos / preguntas × 10. Aprueba quien saca esta nota o más.</div>
</div>

<button type="submit" class="btn btn-primary">Guardar</button>
<a href="{{ route('cursos.evaluacion.show', $curso) }}" class="btn btn-link">Cancelar</a>
