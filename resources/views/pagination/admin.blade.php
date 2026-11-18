@php
    parse_str(parse_url($paginator->url(1), PHP_URL_QUERY) ?? '', $query);
    $query = \Illuminate\Support\Arr::except($query, ['page', 'per_page']);
    $hidden = array_filter(explode('&', \Illuminate\Support\Arr::query($query)));
@endphp
<section class="admin-pagination" aria-label="Paginación de resultados">
    <p class="admin-pagination-summary">Mostrando {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} de {{ $paginator->total() }}</p>
    <nav class="admin-pagination-pages" aria-label="Páginas de resultados">
        @if($paginator->onFirstPage())
            <button class="page-control" type="button" disabled>Anterior</button>
        @else
            <a class="page-control" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
        @endif
        <span class="page-numbers">
            @foreach($elements as $element)
                @if(is_string($element))
                    <span class="page-gap" aria-hidden="true">{{ $element }}</span>
                @else
                    @foreach($element as $page => $url)
                        @if($page === $paginator->currentPage())
                            <strong class="page-control is-current" aria-current="page" aria-label="Página {{ $page }}, actual">{{ $page }}</strong>
                        @else
                            <a class="page-control" href="{{ $url }}" aria-label="Ir a la página {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </span>
        @if($paginator->hasMorePages())
            <a class="page-control" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
        @else
            <button class="page-control" type="button" disabled>Siguiente</button>
        @endif
    </nav>
    <div class="admin-pagination-preferences">
        <form method="GET" action="{{ $paginator->path() }}">
            @foreach($hidden as $pair)
                @php([$name, $value] = array_pad(explode('=', $pair, 2), 2, ''))
                <input type="hidden" name="{{ urldecode($name) }}" value="{{ urldecode($value) }}">
            @endforeach
            <input type="hidden" name="per_page" value="{{ $paginator->perPage() }}">
            <label>Ir a la página
                @if($paginator->lastPage() <= 200)
                    <select name="page">
                        @for($page = 1; $page <= $paginator->lastPage(); $page++)
                            <option value="{{ $page }}" @selected($page === $paginator->currentPage())>{{ $page }}</option>
                        @endfor
                    </select>
                @else
                    <input type="number" name="page" min="1" max="{{ $paginator->lastPage() }}" value="{{ $paginator->currentPage() }}" required>
                @endif
            </label>
            <button class="button button-secondary" type="submit">Ir</button>
        </form>
        <form method="GET" action="{{ $paginator->path() }}">
            @foreach($hidden as $pair)
                @php([$name, $value] = array_pad(explode('=', $pair, 2), 2, ''))
                <input type="hidden" name="{{ urldecode($name) }}" value="{{ urldecode($value) }}">
            @endforeach
            <input type="hidden" name="page" value="1">
            <label>Registros por página
                <select name="per_page">
                    @foreach(\App\Support\AdminPagination::SIZES as $size)
                        <option value="{{ $size }}" @selected($size === $paginator->perPage())>{{ $size }}</option>
                    @endforeach
                </select>
            </label>
            <button class="button button-secondary" type="submit">Aplicar</button>
        </form>
    </div>
</section>
