@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section">
        <div class="container">
            <div class="row g-4 justify-content-center">
                @foreach($tools as $tool)
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route($tool['route']) }}" class="text-decoration-none">
                            <div class="card custom--card tool-card h-100">
                                <div class="card-body text-center p-4">
                                    <div class="tool-icon mb-3">
                                        <i class="{{ $tool['icon'] }}"></i>
                                    </div>
                                    <h5 class="card-title mb-2">{{ __($tool['name']) }}</h5>
                                    <p class="card-text text-muted mb-3">{{ __($tool['description']) }}</p>
                                    <span class="btn btn--base btn--sm">
                                        @lang('Try Now') <i class="las la-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@push('style')
<style>
    .tool-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .tool-card:hover {
        transform: translateY(-5px);
        border-color: hsl(var(--base));
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
    .tool-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, hsl(var(--base)/0.1) 0%, hsl(var(--base)/0.2) 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .tool-icon i {
        font-size: 2.5rem;
        color: hsl(var(--base));
    }
    .tool-card:hover .tool-icon {
        background: linear-gradient(135deg, hsl(var(--base)) 0%, hsl(var(--base)/0.8) 100%);
    }
    .tool-card:hover .tool-icon i {
        color: #fff;
    }
</style>
@endpush
