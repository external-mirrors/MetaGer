@if($paginator->hasPages())
    <nav>
        @if($paginator->onFirstPage())
            <span class="btn btn-default disabled">&laquo; Zurück</span>
        @else
            <a class="btn btn-default" href="{{ $paginator->previousPageUrl() }}">&laquo; Zurück</a>
        @endif
        <span>Seite {{ $paginator->currentPage() }} von {{ $paginator->lastPage() }}</span>
        @if($paginator->hasMorePages())
            <a class="btn btn-default" href="{{ $paginator->nextPageUrl() }}">Weiter &raquo;</a>
        @else
            <span class="btn btn-default disabled">Weiter &raquo;</span>
        @endif
    </nav>
@endif
