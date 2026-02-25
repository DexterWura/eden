@props([
    'src' => null,
    'alt' => '',
    'size' => 'md', // sm|md
])

@php
    $class = $size === 'sm' ? 'table-thumb table-thumb--sm' : 'table-thumb';
@endphp

@if($src)
    <img src="{{ $src }}" alt="{{ $alt }}" class="{{ $class }}">
@else
    <div class="{{ $class }} table-thumb--placeholder">
        <i class="las la-image text-muted"></i>
    </div>
@endif


