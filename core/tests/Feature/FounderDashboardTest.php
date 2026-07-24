<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ProductOfDayWinner;
use App\Models\SavedStartup;
use App\Models\Startup;
use App\Models\StartupClaimVerification;
use App\Models\StartupComment;
use App\Models\StartupRevenueEvent;
use App\Models\StartupUpvote;
use App\Models\User;
use App\Services\FounderDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FounderDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_founder_dashboard_to_login(): void
    {
        $this->get(route('founder.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_founder_can_open_dashboard(): void
    {
        $founder = User::query()->create([
            'name' => 'Test Founder',
            'email' => 'founder-dashboard@example.test',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($founder)
            ->get(route('founder.dashboard'))
            ->assertOk()
            ->assertSee(route('logout'), false)
            ->assertSee('Put your app on Eden')
            ->assertSee('Create your app profile');
    }

    public function test_dashboard_shows_profile_health_ranks_awards_activity_and_claim_state(): void
    {
        Cache::flush();
        $founder = $this->createUser('Dashboard Founder', 'dashboard-founder@example.test');
        $communityMember = $this->createUser('Community Member', 'community@example.test');
        Startup::query()->create([
            'name' => 'Rank Leader',
            'slug' => 'rank-leader',
            'category' => 'Fintech',
            'status' => Startup::STATUS_ACTIVE,
            'upvotes' => 40,
        ]);
        $startup = $this->createStartup($founder, [
            'name' => 'Founder Rocket',
            'slug' => 'founder-rocket',
            'category' => 'Fintech',
            'upvotes' => 12,
            'launch_date' => now()->subDay(),
        ]);
        $saved = $this->createStartup($communityMember, [
            'name' => 'Saved Studio',
            'slug' => 'saved-studio',
            'category' => 'Design',
        ]);
        StartupClaimVerification::query()->create([
            'startup_id' => $startup->id,
            'user_id' => $founder->id,
            'method' => StartupClaimVerification::METHOD_DNS,
            'verification_code' => 'verification-code',
            'verified_at' => now(),
        ]);
        ProductOfDayWinner::query()->create([
            'award_date' => now()->subDay()->toDateString(),
            'startup_id' => $startup->id,
            'upvote_count' => 12,
        ]);
        StartupUpvote::query()->create(['startup_id' => $startup->id, 'user_id' => $communityMember->id]);
        StartupComment::query()->create([
            'startup_id' => $startup->id,
            'user_id' => $communityMember->id,
            'body' => 'This product solves a real operational problem.',
        ]);
        StartupRevenueEvent::query()->create([
            'startup_id' => $startup->id,
            'amount' => 125.50,
            'currency' => 'USD',
        ]);
        SavedStartup::query()->create(['startup_id' => $saved->id, 'user_id' => $founder->id]);

        $response = $this->actingAs($founder)->get(route('founder.dashboard'));

        $response->assertOk()
            ->assertSee('Founder Rocket')
            ->assertSee('Profile completeness')
            ->assertSee('#2')
            ->assertSee('Product of the day')
            ->assertSee('Verified owner')
            ->assertSee('New upvote')
            ->assertSee('New comment')
            ->assertSee('Revenue recorded')
            ->assertSee('App launched')
            ->assertSee('Saved Studio');
    }

    public function test_dashboard_query_count_does_not_grow_per_startup(): void
    {
        $founder = $this->createUser('Query Founder', 'query-founder@example.test');
        $this->createStartup($founder, ['name' => 'First Startup', 'slug' => 'first-startup']);

        $singleCount = $this->dashboardQueryCount($founder);
        foreach (range(2, 7) as $number) {
            $this->createStartup($founder, [
                'name' => 'Startup ' . $number,
                'slug' => 'startup-' . $number,
            ]);
        }

        $manyCount = $this->dashboardQueryCount($founder);

        $this->assertLessThanOrEqual($singleCount + 1, $manyCount);
    }

    public function test_admin_guard_does_not_authenticate_founder_routes(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Primary Admin',
            'email' => 'primary-admin@example.test',
            'username' => 'primaryadmin',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('founder.dashboard'))
            ->assertRedirect(route('login'));
    }

    private function createUser(string $name, string $email): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }

    private function createStartup(User $founder, array $overrides = []): Startup
    {
        return Startup::query()->create(array_merge([
            'user_id' => $founder->id,
            'name' => 'Founder Startup',
            'slug' => 'founder-startup',
            'tagline' => 'A focused product for growing teams',
            'description' => str_repeat('A detailed description of the product and the value it gives growing teams. ', 5),
            'problem_solved' => str_repeat('Teams lose time coordinating fragmented operational work. ', 2),
            'target_customer' => 'Growing businesses with operations and product teams.',
            'key_features' => ['Workflow automation', 'Shared reporting', 'Team collaboration'],
            'pricing_model' => 'Monthly subscription',
            'markets_served' => 'Southern Africa',
            'traction' => 'Used by a growing group of early customers every week.',
            'founder_story' => str_repeat('The founders built this after experiencing the problem first-hand. ', 2),
            'category' => 'Productivity',
            'website' => 'https://founder-startup.example.test',
            'logo_path' => 'images/startups/founder-startup.webp',
            'status' => Startup::STATUS_ACTIVE,
            'upvotes' => 5,
        ], $overrides));
    }

    private function dashboardQueryCount(User $founder): int
    {
        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();

        app(FounderDashboardService::class)->forFounder($founder);
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }
}
