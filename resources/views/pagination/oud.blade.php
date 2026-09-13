@if ($paginator->hasPages())
<nav class="pagination" aria-label="{{ __('workspace.pagination') }}">
    @if ($paginator->onFirstPage())<span>{{ __('pagination.previous') }}</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ __('pagination.previous') }}</a>@endif
    <span>{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">{{ __('pagination.next') }}</a>@else<span>{{ __('pagination.next') }}</span>@endif
</nav>
@endif
