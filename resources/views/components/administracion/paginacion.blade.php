@props(['registros'])

@if($registros->total() > 0)
    <nav class="adm-paginacion" aria-label="Paginación">
        <span>{{ $registros->firstItem() }}–{{ $registros->lastItem() }} de {{ $registros->total() }} registros</span>
        @if($registros->hasPages())
            <div>
                @if($registros->onFirstPage())<span class="adm-pagina-inactiva">Anterior</span>@else<a href="{{ $registros->previousPageUrl() }}" rel="prev">Anterior</a>@endif
                @foreach($registros->getUrlRange(max(1, $registros->currentPage() - 2), min($registros->lastPage(), $registros->currentPage() + 2)) as $pagina => $url)
                    <a href="{{ $url }}" @if($pagina === $registros->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                @endforeach
                @if($registros->hasMorePages())<a href="{{ $registros->nextPageUrl() }}" rel="next">Siguiente</a>@else<span class="adm-pagina-inactiva">Siguiente</span>@endif
            </div>
        @endif
    </nav>
@endif
