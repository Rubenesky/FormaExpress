{{--
    Icono de ayuda contextual + modal de Bootstrap.
    Uso: @include('partials.ayuda', ['id' => 'ayuda-cursos', 'titulo' => 'Cursos', 'parrafos' => [...]])
--}}
<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-0 fx-boton-ayuda"
        style="width: 2rem; height: 2rem;"
        data-bs-toggle="modal" data-bs-target="#{{ $id }}"
        aria-label="Ayuda sobre esta pantalla" title="Ayuda">
    <svg class="fx-icono" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" focusable="false">
        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
        <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/>
    </svg>
</button>

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}-titulo" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="{{ $id }}-titulo">Ayuda: {{ $titulo }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @foreach ($parrafos as $parrafo)
                    <p @if ($loop->last) class="mb-0" @endif>{{ $parrafo }}</p>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
