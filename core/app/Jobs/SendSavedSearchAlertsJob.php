<?php

namespace App\Jobs;

use App\Constants\Status;
use App\Models\Listing;
use App\Models\SavedSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSavedSearchAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $listingId;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(int $listingId)
    {
        $this->listingId = $listingId;
    }

    public function handle(): void
    {
        $listing = Listing::with('listingCategory')
            ->where('id', $this->listingId)
            ->where('status', Status::LISTING_ACTIVE)
            ->first();

        if (!$listing) {
            return;
        }

        $categoryName = $listing->listingCategory ? $listing->listingCategory->name : __('N/A');
        $businessType = $this->humanizeBusinessType($listing->business_type ?? '');
        $price = $this->buildPriceLine($listing);
        $listingUrl = url(route('marketplace.listing.show', $listing->slug));

        $shortcodes = [
            'listing_title' => $listing->title,
            'listing_url' => $listingUrl,
            'listing_number' => $listing->listing_number ?? (string) $listing->id,
            'category_name' => $categoryName,
            'tagline' => $listing->tagline ?? '',
            'price' => $price,
            'business_type' => $businessType,
        ];

        $savedSearches = SavedSearch::withAlerts()
            ->with('user')
            ->where('user_id', '!=', $listing->user_id)
            ->get();

        foreach ($savedSearches as $savedSearch) {
            if ($savedSearch->user->status != Status::USER_ACTIVE) {
                continue;
            }
            if (!$this->listingMatchesFilters($this->listingId, $savedSearch->filters ?? [])) {
                continue;
            }
            try {
                notify($savedSearch->user, 'NEW_LISTING_ALERT', $shortcodes);
                $savedSearch->update(['last_alerted_at' => now()]);
            } catch (\Throwable $e) {
                Log::warning('Saved search alert send failed', [
                    'user_id' => $savedSearch->user_id,
                    'saved_search_id' => $savedSearch->id,
                    'listing_id' => $this->listingId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function listingMatchesFilters(int $listingId, array $filters): bool
    {
        $query = Listing::active()->where('id', $listingId);

        if (!empty($filters['business_type'])) {
            $query->where('business_type', $filters['business_type']);
        }
        if (!empty($filters['sale_type'])) {
            $query->where('sale_type', $filters['sale_type']);
        }
        if (!empty($filters['category'])) {
            $query->where('listing_category_id', $filters['category']);
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $min = (float) $filters['min_price'];
            $query->where(function ($q) use ($min) {
                $q->where(function ($q) use ($min) {
                    $q->where('sale_type', 'fixed_price')->where('asking_price', '>=', $min);
                })->orWhere(function ($q) use ($min) {
                    $q->where('sale_type', 'auction')->where(function ($subQ) use ($min) {
                        $subQ->where('current_bid', '>=', $min)->orWhere('starting_bid', '>=', $min);
                    });
                });
            });
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $max = (float) $filters['max_price'];
            $query->where(function ($q) use ($max) {
                $q->where(function ($q) use ($max) {
                    $q->where('sale_type', 'fixed_price')->where('asking_price', '<=', $max);
                })->orWhere(function ($q) use ($max) {
                    $q->where('sale_type', 'auction')->where(function ($subQ) use ($max) {
                        $subQ->where('current_bid', '<=', $max)->orWhere('starting_bid', '<=', $max);
                    });
                });
            });
        }
        if (isset($filters['min_revenue']) && $filters['min_revenue'] !== '') {
            $query->where('monthly_revenue', '>=', (float) $filters['min_revenue']);
        }
        if (isset($filters['max_revenue']) && $filters['max_revenue'] !== '') {
            $query->where('monthly_revenue', '<=', (float) $filters['max_revenue']);
        }
        if (isset($filters['min_traffic']) && $filters['min_traffic'] !== '') {
            $query->where('monthly_visitors', '>=', (float) $filters['min_traffic']);
        }
        if (isset($filters['max_traffic']) && $filters['max_traffic'] !== '') {
            $query->where('monthly_visitors', '<=', (float) $filters['max_traffic']);
        }
        if (isset($filters['min_age']) && $filters['min_age'] !== '') {
            $query->where('domain_age_years', '>=', (float) $filters['min_age']);
        }
        if (isset($filters['max_age']) && $filters['max_age'] !== '') {
            $query->where('domain_age_years', '<=', (float) $filters['max_age']);
        }
        if (!empty($filters['verified'])) {
            $query->where('is_verified', true);
        }
        if (isset($filters['featured']) && $filters['featured'] === '1') {
            $query->featured();
        }
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (!empty($filters['monetization'])) {
            $methods = is_array($filters['monetization']) ? $filters['monetization'] : [$filters['monetization']];
            foreach ($methods as $method) {
                $query->whereJsonContains('monetization_methods', $method);
            }
        }
        if (!empty($filters['traffic_source'])) {
            $sources = is_array($filters['traffic_source']) ? $filters['traffic_source'] : [$filters['traffic_source']];
            foreach ($sources as $source) {
                $query->whereJsonContains('traffic_sources', $source);
            }
        }

        return $query->exists();
    }

    protected function humanizeBusinessType(?string $type): string
    {
        if (empty($type)) {
            return __('N/A');
        }
        return ucwords(str_replace('_', ' ', $type));
    }

    protected function buildPriceLine(Listing $listing): string
    {
        $sym = gs('cur_sym') ?? '';
        $cur = gs('cur_text') ?? '';
        if ($listing->sale_type === 'auction') {
            $amount = $listing->starting_bid ?? 0;
            return __('Starting bid: :amount :cur', ['amount' => $sym . number_format($amount, 2), 'cur' => $cur]);
        }
        $amount = $listing->asking_price ?? $listing->buy_now_price ?? 0;
        return __('Asking price: :amount :cur', ['amount' => $sym . number_format($amount, 2), 'cur' => $cur]);
    }
}
