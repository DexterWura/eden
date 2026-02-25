@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @if ($sections && $sections->secs != null)
        @foreach (array_filter(json_decode($sections->secs) ?? [], fn($sec) => $sec !== 'banner') as $sec)
            @include($activeTemplate . 'sections.' . $sec)
        @endforeach
    @endif
@endsection

@push('style')
    <style>
        /* Marketplace Section Styles */
        .marketplace-section {
            position: relative;
        }
        .marketplace-section .section-title {
            font-size: 1.75rem;
            color: #1a1a2e;
        }
        .marketplace-section .section-subtitle {
            font-size: 1rem;
        }
        .bg--light {
            background-color: #f8f9fa !important;
        }
        .listing-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .listing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        }
    </style>
@endpush
