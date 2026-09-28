@php
    // Huecos de opción: los del intento anterior (old) o los guardados, hasta MAX_OPCIONES.
    $guardadas = $pregunta->exists ? $pregunta->opciones : collect();
    $textos = old('opciones', $guardadas->pluck('texto')->pad(\App\Models\Pregunta::MAX_OPCIONES, '')->all());
    $indiceCorrecta = $guardadas->search(fn ($opcion) => $opcion->es_correcta);
    $correcta = old('correcta', $indiceCorrecta === false ? null : $indiceCorrecta);

    // Una petición manipulada (p. ej. correcta[]=0&correcta[]=1) no debe romper la vista.
    $textos = is_array($textos) ? array_map(fn ($t) => is_string($t) ? $t : '', $textos) : [];
    $correcta = is_scalar($correcta) ? (string) $correcta : null;
@endphp

@csrf

<div class="mb-3">
    <label for="enunciado" class="form-label">Enunciado</label>
    <textarea id="enunciado" name="enunciado" rows="3" maxlength="2000" required autofocus
              class="form-control @error('enunciado') is-invalid @enderror">{{ old('enunciado', $pregunta->enunciado) }}</textarea>
    @error('enunciado')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<fieldset class="mb-3">
    <legend class="form-label fs-6">Opciones <span class="text-muted">(mínimo 2; marca la correcta; deja vacías las que sobren)</span></legend>

    @error('opciones')
        <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
    @enderror
    @error('correcta')
        <div class="alert alert-danger py-2" role="alert">{{ $message }}</div>
    @enderror

    @for ($i = 0; $i < \App\Models\Pregunta::MAX_OPCIONES; $i++)
        <div class="input-group mb-2">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" name="correcta" value="{{ $i }}"
                       id="correcta_{{ $i }}" aria-label="Opción {{ $i + 1 }} es la correcta"
                       @checked($correcta === (string) $i)>
            </div>
            <label for="opcion_{{ $i }}" class="visually-hidden">Opción {{ $i + 1 }}</label>
            <input id="opcion_{{ $i }}" type="text" name="opciones[{{ $i }}]" maxlength="255"
                   class="form-control @error('opciones.'.$i) is-invalid @enderror"
                   placeholder="Opción {{ $i + 1 }}" value="{{ $textos[$i] ?? '' }}">
            @error('opciones.'.$i)
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endfor
</fieldset>

<button type="submit" class="btn btn-primary">Guardar</button>
<a href="{{ route('cursos.evaluacion.show', $curso) }}" class="btn btn-link">Cancelar</a>
