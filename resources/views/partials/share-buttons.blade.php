@props(['url' => url()->current(), 'title' => ''])
@php
    $encodedUrl = rawurlencode($url);
    $encodedTitle = rawurlencode($title);
    $encodedText = rawurlencode($title ?: $url);
@endphp
<div class="share-buttons" aria-label="Share">
    <a href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}&text={{ $encodedText }}" target="_blank" rel="noopener noreferrer" class="share-btn share-twitter" title="Share on X (Twitter)">X</a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer" class="share-btn share-linkedin" title="Share on LinkedIn">LinkedIn</a>
    <a href="https://wa.me/?text={{ $encodedText }}%20{{ $encodedUrl }}" target="_blank" rel="noopener noreferrer" class="share-btn share-whatsapp" title="Share on WhatsApp">WhatsApp</a>
</div>
