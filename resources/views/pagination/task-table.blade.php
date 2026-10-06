@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center">
        <span class="inline-flex items-center overflow-hidden rounded-lg border border-gray-200 bg-white shadow-xs">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 w-9 items-center justify-center text-gray-300" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-9 w-9 items-center justify-center text-gray-500 hover:bg-gray-50" aria-label="{{ __('pagination.previous') }}">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-9 min-w-9 items-center justify-center border-l border-gray-200 text-sm text-gray-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-9 min-w-9 items-center justify-center border-l border-gray-200 bg-slate-800 text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex h-9 min-w-9 items-center justify-center border-l border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-9 w-9 items-center justify-center border-l border-gray-200 text-gray-500 hover:bg-gray-50" aria-label="{{ __('pagination.next') }}">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </a>
            @else
                <span class="inline-flex h-9 w-9 items-center justify-center border-l border-gray-200 text-gray-300" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                </span>
            @endif
        </span>
    </nav>
@endif
