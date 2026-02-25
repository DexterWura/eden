@php
    // Stats section disabled - not exposing business metrics publicly
    return;
    
    $content = getContent('marketplace_stats.content', true);
    if(!@$content->data_values->status) return;
    
    $totalListings = \App\Models\Listing::active()->count();
    $totalSold = \App\Models\Listing::sold()->count();
    $totalValue = \App\Models\Listing::sold()->sum('final_price');
    $activeUsers = \App\Models\User::active()->count();
@endphp

<section class="marketplace-stats py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-4 text-center">
            @if(@$content->data_values->show_total_listings)
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-icon mb-2">
                        <i class="las la-list-alt text--base" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="stat-number mb-0 fw-bold">{{ number_format($totalListings) }}</h3>
                    <p class="stat-label text-muted mb-0 small">@lang('Active Listings')</p>
                </div>
            </div>
            @endif
            
            @if(@$content->data_values->show_total_sold)
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-icon mb-2">
                        <i class="las la-handshake text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="stat-number mb-0 fw-bold">{{ number_format($totalSold) }}</h3>
                    <p class="stat-label text-muted mb-0 small">@lang('Businesses Sold')</p>
                </div>
            </div>
            @endif
            
            @if(@$content->data_values->show_total_value)
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-icon mb-2">
                        <i class="las la-dollar-sign text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="stat-number mb-0 fw-bold">{{ gs('cur_sym') }}{{ shortNumber($totalValue) }}</h3>
                    <p class="stat-label text-muted mb-0 small">@lang('Total Sales Value')</p>
                </div>
            </div>
            @endif
            
            @if(@$content->data_values->show_active_users)
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-icon mb-2">
                        <i class="las la-users text-info" style="font-size: 2rem;"></i>
                    </div>
                    <h3 class="stat-number mb-0 fw-bold">{{ number_format($activeUsers) }}</h3>
                    <p class="stat-label text-muted mb-0 small">@lang('Active Members')</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

