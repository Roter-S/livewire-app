<div>
    <h1>Listado de Articulos</h1>
    <a href="{{ route('articles.create') }}">Crear</a>
    <label>
        <input wire:model.debounce.500ms="search" placeholder="Buscar" type="text">
    </label>
    <ul>
        @foreach ($articles as $article)
            <li>
                <a href="{{ route('articles.show', $article) }}">
                    {{ $article->title }}
                </a>
                <a href="{{ route('articles.edit', $article) }}">
                   Editar
                </a>
            </li>
        @endforeach
    </ul>
</div>
