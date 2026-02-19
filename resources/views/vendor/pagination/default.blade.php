@if ($paginator->hasPages())
    <nav class="pagination" role="navigation">
        <ul>
            @if ($paginator->onFirstPage())
                <li><span class="disabled">Previous</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}">Previous</a></li>
            @endif
            @foreach ($elements as $element)
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="current">{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}" class="page-link">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}">Next</a></li>
            @else
                <li><span class="disabled">Next</span></li>
            @endif
        </ul>
    </nav>
@endif
