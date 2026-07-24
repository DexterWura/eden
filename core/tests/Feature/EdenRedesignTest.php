<?php

namespace Tests\Feature;

use App\Constants\Status;
use App\Models\Category;
use App\Models\Startup;
use App\Models\User;
use App\Services\SitemapService;
use App\Services\StartupApprovalNotificationService;
use App\Services\StartupActivationService;
use App\Services\StartupAwardService;
use App\Services\StartupFundingRoundService;
use App\Services\StartupService;
use App\Support\StartupContentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EdenRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_keeps_discovery_features_in_feed_layout(): void
    {
        $startup = $this->createRichStartup();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Don’t miss the next breakout app');
        $response->assertSee('placeholder="Search for apps"', false);
        $response->assertSee('Submit your app');
        $response->assertSee('discovery-layout', false);
        $response->assertSee('startup-card--feed', false);
        $response->assertSee($startup->name);
        $response->assertDontSee('id="leaderboard-heading"', false);
        $response->assertDontSee('Top apps');
        $response->assertDontSee('Products launching today');
        $response->assertDontSee('Featured products');
        $response->assertDontSee('Just listed');

        $this->get('/leaderboard')
            ->assertOk()
            ->assertSee('Leaderboard');
    }

    public function test_homepage_feed_prioritizes_pro_owners_then_upvotes_and_recency(): void
    {
        $proFounder = User::query()->create([
            'name' => 'Pro Founder',
            'email' => 'pro-founder@example.test',
            'password' => bcrypt('password'),
            'is_pro' => true,
        ]);

        $olderPopular = $this->createRichStartup();
        $olderPopular->update(['upvotes' => 100]);
        $olderPopular->timestamps = false;
        $olderPopular->created_at = now()->subMonth();
        $olderPopular->updated_at = now()->subMonth();
        $olderPopular->save();
        $olderPopular->timestamps = true;

        $recentPopular = $olderPopular->replicate();
        $recentPopular->fill([
            'name' => 'Recent Popular',
            'slug' => 'recent-popular',
            'website' => 'https://recent-popular.test',
        ]);
        $recentPopular->timestamps = true;
        $recentPopular->save();

        $recentLowerVotes = $olderPopular->replicate();
        $recentLowerVotes->fill([
            'name' => 'Recent Lower Votes',
            'slug' => 'recent-lower-votes',
            'website' => 'https://recent-lower-votes.test',
            'upvotes' => 10,
        ]);
        $recentLowerVotes->timestamps = true;
        $recentLowerVotes->save();

        $popularFree = $olderPopular->replicate();
        $popularFree->fill([
            'name' => 'Popular Free',
            'slug' => 'popular-free',
            'website' => 'https://popular-free.test',
            'upvotes' => 200,
        ]);
        $popularFree->timestamps = true;
        $popularFree->save();

        $olderPopular->update(['user_id' => $proFounder->id]);
        $recentPopular->update(['user_id' => $proFounder->id]);

        $orderedIds = collect(app(StartupService::class)->getAllStartupsPaginated(perPage: 10)->items())
            ->pluck('id')
            ->values();

        $this->assertSame([
            $recentPopular->id,
            $olderPopular->id,
            $popularFree->id,
            $recentLowerVotes->id,
        ], $orderedIds->all());

        $this->get('/')
            ->assertOk()
            ->assertDontSee('startup-card--pro', false)
            ->assertDontSee('badge badge-pro', false);
    }

    public function test_editorial_category_hub_is_indexable_and_lists_startups(): void
    {
        $startup = $this->createRichStartup();
        $category = Category::query()->where('name', $startup->category)->firstOrFail();
        $category->update([
            'introduction' => str_repeat('Useful original context about fintech customers and products. ', 5),
            'market_context' => str_repeat('Zimbabwean founders are adapting payments to local market needs. ', 3),
            'frequently_asked_questions' => [[
                'question' => 'What is included?',
                'answer' => 'Active startup profiles with founder and product information.',
            ]],
        ]);

        $response = $this->get('/categories/' . $category->slug);

        $response->assertOk();
        $response->assertSee($startup->name);
        $response->assertDontSee('noindex,follow', false);
        $response->assertSee('FAQPage', false);
    }

    public function test_startup_detail_uses_product_discovery_layout(): void
    {
        $startup = $this->createRichStartup();
        $startup->update([
            'product_of_day_at' => '2026-07-10',
            'product_of_month_at' => '2026-07-01',
            'product_of_year_at' => 2026,
        ]);

        $response = $this->get('/startup/' . $startup->slug);

        $response->assertOk();
        $response->assertSee('product-header', false);
        $response->assertSee('product-upvote-button', false);
        $response->assertSee('product-page-tabs', false);
        $response->assertSee('startup-awards-card', false);
        $response->assertSee('Product of the month');
        $response->assertSee('Product of the year');
        $response->assertDontSee('potd-seal', false);
        $response->assertSee('/advertise/startup-sidebar', false);
        $response->assertSee('Advertise on startup pages');
        $response->assertSee('About ' . $startup->name);
    }

    public function test_thin_profiles_are_removed_from_indexing_after_grace_period(): void
    {
        $richStartup = $this->createRichStartup();
        $thinStartup = Startup::query()->create([
            'name' => 'Thin Listing',
            'slug' => 'thin-listing',
            'tagline' => 'A listing without useful editorial depth',
            'description' => 'Too short.',
            'category' => 'Fintech',
            'website' => 'https://thin-listing.test',
            'status' => Startup::STATUS_ACTIVE,
        ]);
        $this->travelTo(Carbon::create(2026, 9, 1));

        $thinResponse = $this->get('/startup/' . $thinStartup->slug);
        $sitemap = app(SitemapService::class)->render();

        $thinResponse->assertOk();
        $thinResponse->assertSee('noindex,follow', false);
        $this->assertStringContainsString('/startup/' . $richStartup->slug, $sitemap);
        $this->assertStringNotContainsString('/startup/' . $thinStartup->slug, $sitemap);
    }

    public function test_dynamic_sitemap_and_robots_endpoints_are_available(): void
    {
        $this->createRichStartup();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap:');
    }

    public function test_approval_notification_emails_owner_and_links_to_badges(): void
    {
        Mail::fake();
        $owner = new User();
        $owner->name = 'Test Founder';
        $owner->email = 'founder@example.test';
        $owner->password = bcrypt('password');
        $owner->save();
        $startup = $this->createRichStartup();
        $startup->update(['user_id' => $owner->id]);

        $sent = app(StartupApprovalNotificationService::class)->send($startup);

        $this->assertSame(1, $sent);
        Mail::assertSentCount(1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
        ]);
        $notification = DB::table('notifications')->where('notifiable_id', $owner->id)->first();
        $this->assertStringContainsString('/founder/badges', (string) $notification->data);
    }

    public function test_startup_domain_rules_keep_legacy_and_new_profiles_consistent(): void
    {
        $legacy = new Startup(['content_quality_version' => 0]);
        $newStartup = new Startup(['content_quality_version' => 1]);
        $richStartup = $this->createRichStartup();
        $owner = User::query()->create([
            'name' => 'Profile Owner',
            'email' => 'profile-owner@example.com',
            'password' => bcrypt('password'),
        ]);
        $thinStartup = Startup::query()->create([
            'user_id' => $owner->id,
            'name' => 'Thin Profile',
            'slug' => 'thin-profile',
            'status' => Startup::STATUS_ACTIVE,
            'description' => 'Short',
        ]);

        $this->assertFalse($legacy->requiresEditorialContent());
        $this->assertTrue($newStartup->requiresEditorialContent());
        $this->assertGreaterThanOrEqual(StartupContentPolicy::INDEXING_SCORE_MIN, $richStartup->content_completeness_score);
        $this->assertSame(1, Startup::query()->needsEnrichment()->where('slug', 'thin-profile')->count());
        $this->assertSame('proofpay-1', Startup::uniqueSlug('ProofPay'));
        $this->assertMatchesRegularExpression('/^proofpay-[A-Za-z0-9]{4}$/', Startup::uniqueSlug('ProofPay', null, true));
        $this->assertTrue($owner->can('manage', $thinStartup));
    }

    public function test_activation_quality_gate_and_funding_round_transitions_are_preserved(): void
    {
        $thinStartup = Startup::query()->create([
            'name' => 'Pending Thin Profile',
            'slug' => 'pending-thin-profile',
            'status' => Startup::STATUS_PENDING,
            'content_quality_version' => 1,
            'description' => 'Short',
        ]);
        $rejected = app(StartupActivationService::class)->activate($thinStartup);
        $this->assertFalse($rejected['activated']);
        $this->assertTrue($thinStartup->fresh()->isPending());

        $richStartup = $this->createRichStartup();
        $richStartup->update(['status' => Startup::STATUS_PENDING]);
        $activated = app(StartupActivationService::class)->activate($richStartup->fresh());
        $this->assertTrue($activated['activated']);
        $this->assertTrue($richStartup->fresh()->isActive());

        $fundingService = app(StartupFundingRoundService::class);
        $fundingService->sync($richStartup, [
            'seeking_investors' => '1',
            'funding_round_type' => 'seed',
            'funding_amount_seeking' => 50000,
            'funding_currency' => 'usd',
        ]);
        $this->assertSame('USD', $richStartup->fresh()->activeFundingRound->currency);
        $fundingService->sync($richStartup->fresh(), ['seeking_investors' => '0']);
        $this->assertNull($richStartup->fresh()->activeFundingRound);
    }

    public function test_algorithm_selects_period_awards_and_emails_the_founder(): void
    {
        Mail::fake();
        $owner = User::query()->create([
            'name' => 'Award Founder',
            'email' => 'award-founder@example.com',
            'password' => bcrypt('password'),
        ]);
        $startup = $this->createRichStartup();
        $startup->update(['user_id' => $owner->id, 'upvotes' => 1]);
        DB::table('startup_upvotes')->insert([
            'user_id' => $owner->id,
            'startup_id' => $startup->id,
            'created_at' => '2025-06-15 12:00:00',
            'updated_at' => '2025-06-15 12:00:00',
        ]);

        $awardService = app(StartupAwardService::class);
        $day = $awardService->selectProductOfDay(Carbon::parse('2025-06-15'));
        $month = $awardService->selectProductOfMonth(Carbon::parse('2025-06-01'));
        $year = $awardService->selectProductOfYear(Carbon::parse('2025-01-01'));

        $this->assertSame($startup->id, $day['startup_id']);
        $this->assertSame($startup->id, $month['startup_id']);
        $this->assertSame($startup->id, $year['startup_id']);
        $this->assertDatabaseHas('product_of_day_winners', ['award_date' => '2025-06-15', 'startup_id' => $startup->id]);
        $this->assertDatabaseHas('product_of_month_winners', ['award_month' => '2025-06-01', 'startup_id' => $startup->id]);
        $this->assertDatabaseHas('product_of_year_winners', ['award_year' => 2025, 'startup_id' => $startup->id]);
        Mail::assertSentCount(3);
        $this->assertSame(3, DB::table('notifications')->where('notifiable_id', $owner->id)->count());
        $this->assertSame(3, DB::table('startup_award_notification_deliveries')->count());
        $this->assertSame(0, DB::table('startup_award_notification_deliveries')->whereNull('email_sent_at')->count());
        $this->assertSame(0, DB::table('startup_award_notification_deliveries')->whereNull('dashboard_created_at')->count());
        $this->assertNotNull(DB::table('product_of_day_winners')->value('notified_at'));
        $this->assertNotNull(DB::table('product_of_month_winners')->value('notified_at'));
        $this->assertNotNull(DB::table('product_of_year_winners')->value('notified_at'));

        $this->assertNull($awardService->selectProductOfDay(Carbon::parse('2025-06-15')));
        $this->assertNull($awardService->selectProductOfMonth(Carbon::parse('2025-06-01')));
        $this->assertNull($awardService->selectProductOfYear(Carbon::parse('2025-01-01')));
        Mail::assertSentCount(3);
        $this->assertSame(3, DB::table('startup_award_notification_deliveries')->count());
    }

    public function test_opening_a_funding_round_emails_other_registered_founders_once(): void
    {
        Mail::fake();
        $raisingFounder = User::query()->create([
            'name' => 'Raising Founder',
            'email' => 'raising-founder@example.com',
            'password' => bcrypt('password'),
            'status' => Status::USER_ACTIVE,
            'ev' => Status::VERIFIED,
            'sv' => Status::VERIFIED,
        ]);
        $investorFounder = User::query()->create([
            'name' => 'Investor Founder',
            'email' => 'investor-founder@example.com',
            'password' => bcrypt('password'),
            'status' => Status::USER_ACTIVE,
            'ev' => Status::VERIFIED,
            'sv' => Status::VERIFIED,
        ]);
        $raisingStartup = $this->createRichStartup();
        $raisingStartup->update(['user_id' => $raisingFounder->id]);
        Startup::query()->create([
            'user_id' => $investorFounder->id,
            'name' => 'Investor Company',
            'slug' => 'investor-company',
            'status' => Startup::STATUS_ACTIVE,
        ]);
        $input = [
            'seeking_investors' => '1',
            'funding_round_type' => 'seed',
            'funding_amount_seeking' => 75000,
            'funding_currency' => 'USD',
            'funding_description' => 'Expanding the product and sales team across the region.',
        ];

        $fundingService = app(StartupFundingRoundService::class);
        $fundingService->sync($raisingStartup, $input);
        $fundingRound = $raisingStartup->fresh()->activeFundingRound;

        Mail::assertSentCount(1);
        $this->assertDatabaseHas('fundraising_opportunity_deliveries', [
            'startup_funding_round_id' => $fundingRound->id,
            'user_id' => $investorFounder->id,
        ]);
        $this->assertDatabaseMissing('fundraising_opportunity_deliveries', [
            'startup_funding_round_id' => $fundingRound->id,
            'user_id' => $raisingFounder->id,
        ]);
        $this->assertNotNull($fundingRound->fresh()->opportunity_announced_at);

        $fundingService->sync($raisingStartup->fresh(), $input);
        Mail::assertSentCount(1);
    }

    public function test_leaderboard_defaults_to_all_time_and_filters_recent_listings(): void
    {
        $olderStartup = $this->createRichStartup();
        $olderStartup->timestamps = false;
        $olderStartup->update([
            'created_at' => now()->subMonths(2),
            'updated_at' => now()->subMonths(2),
        ]);
        $recentStartup = $olderStartup->replicate();
        $recentStartup->fill([
            'name' => 'RecentPay',
            'slug' => 'recentpay',
            'website' => 'https://recentpay.test',
            'upvotes' => 20,
        ]);
        $recentStartup->save();

        $startupService = app(StartupService::class);
        $allTimeIds = collect($startupService->getLeaderboard('upvotes', 50)->items())->pluck('id');
        $weeklyIds = collect($startupService->getLeaderboard('upvotes', 50, period: 'week')->items())->pluck('id');

        $this->assertTrue($allTimeIds->contains($olderStartup->id));
        $this->assertTrue($allTimeIds->contains($recentStartup->id));
        $this->assertFalse($weeklyIds->contains($olderStartup->id));
        $this->assertTrue($weeklyIds->contains($recentStartup->id));
    }

    public function test_free_founder_sees_optional_pro_backlink_offer_when_adding_startup(): void
    {
        $founder = User::query()->create([
            'name' => 'Listing Founder',
            'email' => 'listing-founder@example.test',
            'password' => bcrypt('password'),
            'status' => Status::USER_ACTIVE,
            'ev' => Status::VERIFIED,
            'sv' => Status::VERIFIED,
            'is_pro' => false,
        ]);

        $this->actingAs($founder)
            ->get(route('founder.startups.create'))
            ->assertOk()
            ->assertSee('Get a dofollow backlink with Pro')
            ->assertSee('Submitting is still free');
    }

    public function test_only_pro_owned_startups_receive_dofollow_website_links(): void
    {
        $founder = User::query()->create([
            'name' => 'Backlink Founder',
            'email' => 'backlink-founder@example.test',
            'password' => bcrypt('password'),
            'is_pro' => false,
        ]);
        $startup = $this->createRichStartup();
        $startup->update(['user_id' => $founder->id]);

        $this->get(route('startup.show', $startup->slug))
            ->assertOk()
            ->assertSee('href="' . url('/startup/' . $startup->slug . '/out') . '" target="_blank" rel="nofollow noopener noreferrer"', false)
            ->assertSee('href="https://proofpay.test" target="_blank" rel="nofollow noopener noreferrer"', false);

        $founder->update(['is_pro' => true]);

        $this->get(route('startup.show', $startup->slug))
            ->assertOk()
            ->assertSee('href="' . url('/startup/' . $startup->slug . '/out') . '" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="https://proofpay.test" target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('href="https://proofpay.test" target="_blank" rel="nofollow', false);
    }

    public function test_house_websites_receive_dofollow_without_pro_but_not_elevated_visibility(): void
    {
        $founder = User::query()->create([
            'name' => 'House Founder',
            'email' => 'house-founder@example.test',
            'password' => bcrypt('password'),
            'is_pro' => false,
        ]);

        $house = $this->createRichStartup();
        $house->update([
            'user_id' => $founder->id,
            'name' => 'SocialPlod',
            'slug' => 'socialplod-house',
            'website' => 'https://socialplod.com/',
            'upvotes' => 5,
        ]);

        $popularFree = $house->replicate();
        $popularFree->fill([
            'name' => 'Popular Free Rival',
            'slug' => 'popular-free-rival',
            'website' => 'https://popular-free-rival.test',
            'upvotes' => 500,
        ]);
        $popularFree->save();

        $this->assertTrue($house->fresh()->hasDofollowBacklink());
        $this->assertFalse($house->fresh()->hasElevatedListingVisibility());
        $this->assertFalse($popularFree->fresh()->hasDofollowBacklink());

        $orderedIds = collect(app(StartupService::class)->getAllStartupsPaginated(perPage: 10)->items())
            ->pluck('id')
            ->values();

        $this->assertSame($popularFree->id, $orderedIds->first());
        $this->assertTrue($orderedIds->search($house->id) > 0);

        $this->get(route('startup.show', $house->slug))
            ->assertOk()
            ->assertSee('href="' . url('/startup/' . $house->slug . '/out') . '" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="https://socialplod.com/" target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('href="https://socialplod.com/" target="_blank" rel="nofollow', false);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('startup-card--pro', false)
            ->assertDontSee('badge badge-pro', false);
    }

    private function createRichStartup(): Startup
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'fintech'],
            ['name' => 'Fintech', 'sort_order' => 1]
        );

        return Startup::query()->create([
            'name' => 'ProofPay',
            'slug' => 'proofpay',
            'tagline' => 'Payment operations built for growing African companies',
            'description' => str_repeat('ProofPay helps finance teams collect, reconcile and understand payments across their daily operations. ', 4),
            'problem_solved' => str_repeat('Finance teams lose time matching fragmented transaction records. ', 2),
            'target_customer' => 'Growing African businesses with finance and operations teams.',
            'key_features' => ['Payment links', 'Automated reconciliation', 'Operational reporting'],
            'pricing_model' => 'Transaction fee',
            'markets_served' => 'Zimbabwe and Southern Africa',
            'traction' => 'Used by a growing group of local merchants and finance teams.',
            'founder_story' => str_repeat('The founders experienced reconciliation delays while operating local businesses. ', 2),
            'category' => $category->name,
            'website' => 'https://proofpay.test',
            'logo_path' => 'images/startups/proofpay.webp',
            'status' => Startup::STATUS_ACTIVE,
            'launch_date' => now(),
        ]);
    }
}
