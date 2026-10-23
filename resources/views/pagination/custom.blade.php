@php
    $isLengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $elements = $elements ?? [];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
    <div class="text-muted small">
        @if ($isLengthAware)
            共 {{ $paginator->total() }} 条，当前显示第 {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} 条，
            第 {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }} 页
        @else
            第 {{ $paginator->currentPage() }} 页
        @endif
    </div>

    <nav aria-label="分页">
        <ul class="pagination pagination-sm mb-0">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">&lsaquo;</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a></li>
            @else
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">&rsaquo;</span></li>
            @endif
        </ul>
    </nav>
</div>
