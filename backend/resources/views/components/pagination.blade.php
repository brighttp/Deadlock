@props(['paginator'])
@if ($paginator->total() > 0)
    <div class="pagination-bar">
        <span>Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data</span>
        @if ($paginator->hasPages())
            <div class="pagination-links">
                @if ($paginator->onFirstPage())
                    <span class="disabled">&laquo;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}">&laquo;</a>
                @endif
                @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}">&raquo;</a>
                @else
                    <span class="disabled">&raquo;</span>
                @endif
            </div>
        @endif
    </div>
@endif
