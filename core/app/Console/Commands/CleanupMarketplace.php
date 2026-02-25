<?php

namespace App\Console\Commands;

use App\Constants\Status;
use App\Models\Bid;
use App\Models\Escrow;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\ListingMetric;
use App\Models\ListingQuestion;
use App\Models\ListingView;
use App\Models\Offer;
use App\Models\Review;
use App\Models\Watchlist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupMarketplace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:cleanup
                            {--expired-listings : Clean up expired listings}
                            {--old-bids : Clean up old bid data}
                            {--orphaned-data : Clean up orphaned records}
                            {--temp-files : Clean up temporary files}
                            {--dry-run : Show what would be cleaned without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up marketplace data including expired listings, old bids, and orphaned records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $expiredListings = $this->option('expired-listings');
        $oldBids = $this->option('old-bids');
        $orphanedData = $this->option('orphaned-data');
        $tempFiles = $this->option('temp-files');

        // Run all cleanup tasks if no specific option is provided
        $runAll = !$expiredListings && !$oldBids && !$orphanedData && !$tempFiles;

        if ($runAll || $expiredListings) {
            $this->cleanupExpiredListings($dryRun);
        }

        if ($runAll || $oldBids) {
            $this->cleanupOldBids($dryRun);
        }

        if ($runAll || $orphanedData) {
            $this->cleanupOrphanedData($dryRun);
        }

        if ($runAll || $tempFiles) {
            $this->cleanupTempFiles($dryRun);
        }

        $this->info('Marketplace cleanup completed successfully!');
    }

    /**
     * Clean up expired listings and related data
     */
    private function cleanupExpiredListings($dryRun)
    {
        $this->info('Cleaning up expired listings...');

        // Find expired auctions that haven't been processed
        $expiredAuctions = Listing::where('sale_type', 'auction')
            ->where('status', Status::LISTING_ACTIVE)
            ->where('auction_end', '<', now()->subDays(30)) // Older than 30 days
            ->get();

        if ($expiredAuctions->isEmpty()) {
            $this->info('No expired auctions found.');
            return;
        }

        $this->info("Found {$expiredAuctions->count()} expired auctions.");

        foreach ($expiredAuctions as $listing) {
            if ($dryRun) {
                $this->line("Would mark auction #{$listing->listing_number} as expired");
                continue;
            }

            DB::beginTransaction();
            try {
                // Mark listing as expired
                $listing->status = Status::LISTING_EXPIRED;
                $listing->save();

                // Mark all bids as lost
                Bid::where('listing_id', $listing->id)
                    ->whereIn('status', [Status::BID_ACTIVE, Status::BID_WINNING, Status::BID_OUTBID])
                    ->update(['status' => Status::BID_LOST]);

                // Cancel any pending offers
                Offer::where('listing_id', $listing->id)
                    ->where('status', Status::OFFER_PENDING)
                    ->update(['status' => Status::OFFER_EXPIRED]);

                // Remove from watchlists (optional - could keep for history)
                // Watchlist::where('listing_id', $listing->id)->delete();

                DB::commit();

                Log::info('Cleaned up expired auction', [
                    'listing_id' => $listing->id,
                    'listing_number' => $listing->listing_number
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to cleanup expired auction', [
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('Expired listings cleanup completed.');
    }

    /**
     * Clean up old bid data
     */
    private function cleanupOldBids($dryRun)
    {
        $this->info('Cleaning up old bid data...');

        // Remove bids older than 1 year that are lost
        $oldLostBids = Bid::where('status', Status::BID_LOST)
            ->where('created_at', '<', now()->subYear())
            ->get();

        if ($oldLostBids->isNotEmpty()) {
            $this->info("Found {$oldLostBids->count()} old lost bids to clean up.");

            if (!$dryRun) {
                $count = Bid::where('status', Status::BID_LOST)
                    ->where('created_at', '<', now()->subYear())
                    ->delete();

                $this->info("Deleted {$count} old lost bids.");
            } else {
                $this->line("Would delete {$oldLostBids->count()} old lost bids.");
            }
        }

        // Clean up cancelled bids older than 6 months
        $oldCancelledBids = Bid::where('status', Status::BID_CANCELLED)
            ->where('created_at', '<', now()->subMonths(6))
            ->get();

        if ($oldCancelledBids->isNotEmpty()) {
            $this->info("Found {$oldCancelledBids->count()} old cancelled bids to clean up.");

            if (!$dryRun) {
                $count = Bid::where('status', Status::BID_CANCELLED)
                    ->where('created_at', '<', now()->subMonths(6))
                    ->delete();

                $this->info("Deleted {$count} old cancelled bids.");
            } else {
                $this->line("Would delete {$oldCancelledBids->count()} old cancelled bids.");
            }
        }

        $this->info('Old bids cleanup completed.');
    }

    /**
     * Clean up orphaned data
     */
    private function cleanupOrphanedData($dryRun)
    {
        $this->info('Cleaning up orphaned data...');

        // Find listing images for non-existent listings
        $orphanedImages = ListingImage::whereDoesntHave('listing')->get();
        if ($orphanedImages->isNotEmpty()) {
            $this->info("Found {$orphanedImages->count()} orphaned listing images.");

            if (!$dryRun) {
                // Delete actual files
                foreach ($orphanedImages as $image) {
                    if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                }

                $count = ListingImage::whereDoesntHave('listing')->delete();
                $this->info("Deleted {$count} orphaned listing images.");
            } else {
                $this->line("Would delete {$orphanedImages->count()} orphaned listing images.");
            }
        }

        // Find metrics for non-existent listings
        $orphanedMetrics = ListingMetric::whereDoesntHave('listing')->get();
        if ($orphanedMetrics->isNotEmpty()) {
            $this->info("Found {$orphanedMetrics->count()} orphaned listing metrics.");

            if (!$dryRun) {
                $count = ListingMetric::whereDoesntHave('listing')->delete();
                $this->info("Deleted {$count} orphaned listing metrics.");
            } else {
                $this->line("Would delete {$orphanedMetrics->count()} orphaned listing metrics.");
            }
        }

        // Find views for non-existent listings
        $orphanedViews = ListingView::whereDoesntHave('listing')->get();
        if ($orphanedViews->isNotEmpty()) {
            $this->info("Found {$orphanedViews->count()} orphaned listing views.");

            if (!$dryRun) {
                $count = ListingView::whereDoesntHave('listing')->delete();
                $this->info("Deleted {$count} orphaned listing views.");
            } else {
                $this->line("Would delete {$orphanedViews->count()} orphaned listing views.");
            }
        }

        // Find questions for non-existent listings
        $orphanedQuestions = ListingQuestion::whereDoesntHave('listing')->get();
        if ($orphanedQuestions->isNotEmpty()) {
            $this->info("Found {$orphanedQuestions->count()} orphaned listing questions.");

            if (!$dryRun) {
                $count = ListingQuestion::whereDoesntHave('listing')->delete();
                $this->info("Deleted {$count} orphaned listing questions.");
            } else {
                $this->line("Would delete {$orphanedQuestions->count()} orphaned listing questions.");
            }
        }

        // Find offers for non-existent listings
        $orphanedOffers = Offer::whereDoesntHave('listing')->get();
        if ($orphanedOffers->isNotEmpty()) {
            $this->info("Found {$orphanedOffers->count()} orphaned offers.");

            if (!$dryRun) {
                $count = Offer::whereDoesntHave('listing')->delete();
                $this->info("Deleted {$count} orphaned offers.");
            } else {
                $this->line("Would delete {$orphanedOffers->count()} orphaned offers.");
            }
        }

        // Find watchlist entries for non-existent listings
        $orphanedWatchlist = Watchlist::whereDoesntHave('listing')->get();
        if ($orphanedWatchlist->isNotEmpty()) {
            $this->info("Found {$orphanedWatchlist->count()} orphaned watchlist entries.");

            if (!$dryRun) {
                $count = Watchlist::whereDoesntHave('listing')->delete();
                $this->info("Deleted {$count} orphaned watchlist entries.");
            } else {
                $this->line("Would delete {$orphanedWatchlist->count()} orphaned watchlist entries.");
            }
        }

        // Find reviews for non-existent listings
        $orphanedReviews = Review::whereDoesntHave('listing')->get();
        if ($orphanedReviews->isNotEmpty()) {
            $this->info("Found {$orphanedReviews->count()} orphaned reviews.");

            if (!$dryRun) {
                $count = Review::whereDoesntHave('listing')->delete();
                $this->info("Deleted {$count} orphaned reviews.");
            } else {
                $this->line("Would delete {$orphanedReviews->count()} orphaned reviews.");
            }
        }

        $this->info('Orphaned data cleanup completed.');
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles($dryRun)
    {
        $this->info('Cleaning up temporary files...');

        $tempPaths = [
            'temp/',
            'uploads/temp/',
            'storage/temp/'
        ];

        foreach ($tempPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $files = Storage::disk('public')->allFiles($path);

                // Keep files newer than 24 hours
                $oldFiles = array_filter($files, function ($file) {
                    $timestamp = Storage::disk('public')->lastModified($file);
                    return $timestamp < now()->subDay()->timestamp;
                });

                if (!empty($oldFiles)) {
                    $this->info("Found " . count($oldFiles) . " old temporary files in {$path}.");

                    if (!$dryRun) {
                        foreach ($oldFiles as $file) {
                            Storage::disk('public')->delete($file);
                        }
                        $this->info("Deleted " . count($oldFiles) . " temporary files from {$path}.");
                    } else {
                        $this->line("Would delete " . count($oldFiles) . " temporary files from {$path}.");
                    }
                }
            }
        }

        $this->info('Temporary files cleanup completed.');
    }
}
