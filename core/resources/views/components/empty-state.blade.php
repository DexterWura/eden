@props([
    'image' => 'assets/images/empty_list.png',
    'title' => null,
    'message' => null,
    'actionHref' => null,
    'actionLabel' => null,
    'imgMaxWidth' => 140,
])

<div class="empty-state text-center py-4">
    <img
        class="empty-state__img"
        src="{{ asset($image) }}"
        alt="empty"
        style="max-width: {{ (int) $imgMaxWidth }}px;"
    >

    @if($title)
        <h5 class="mt-3 mb-1 text-muted">{{ __($title) }}</h5>
    @endif

    @if($message)
        <p class="mb-0 text-muted">{{ __($message) }}</p>
    @endif

    @if($actionHref && $actionLabel)
        <a href="{{ $actionHref }}" class="btn btn--base btn-sm mt-3">
            {{ __($actionLabel) }}
        </a>
    @endif
</div>


