<?php

namespace App\Jobs;

use App\Constants\Status;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNewListingAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $listingId;

    public int $tries = 2;

    public int $timeout = 600;

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

        User::where('id', '!=', $listing->user_id)
            ->where('status', Status::USER_ACTIVE)
            ->chunk(100, function ($users) use ($shortcodes) {
                foreach ($users as $user) {
                    try {
                        notify($user, 'NEW_LISTING_ALERT', $shortcodes);
                    } catch (\Throwable $e) {
                        Log::warning('New listing alert send failed', [
                            'user_id' => $user->id,
                            'listing_id' => $this->listingId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
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
