<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Startup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_founder_search_returns_only_manageable_startups(): void
    {
        $founder = User::query()->create([
            'name' => 'Founder One',
            'email' => 'founder-one@example.test',
            'password' => Hash::make('password'),
        ]);
        $otherFounder = User::query()->create([
            'name' => 'Founder Two',
            'email' => 'founder-two@example.test',
            'password' => Hash::make('password'),
        ]);
        Startup::query()->create([
            'user_id' => $founder->id,
            'name' => 'Shared Search Alpha',
            'slug' => 'shared-search-alpha',
            'status' => Startup::STATUS_ACTIVE,
        ]);
        Startup::query()->create([
            'user_id' => $otherFounder->id,
            'name' => 'Shared Search Beta',
            'slug' => 'shared-search-beta',
            'status' => Startup::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($founder)
            ->getJson(route('founder.search', ['q' => 'Shared Search']));

        $response->assertOk()
            ->assertJsonPath('groups.0.label', 'My startups')
            ->assertJsonCount(1, 'groups.0.items')
            ->assertJsonPath('groups.0.items.0.label', 'Shared Search Alpha');
    }

    public function test_admin_search_only_returns_groups_for_permitted_modules(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Startup Staff',
            'email' => 'startup-staff@example.test',
            'username' => 'startupstaff',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'allowed_modules' => ['startups'],
            'status' => Admin::STATUS_ENABLED,
        ]);
        Startup::query()->create([
            'name' => 'Permission Search',
            'slug' => 'permission-search',
            'status' => Startup::STATUS_ACTIVE,
        ]);
        User::query()->create([
            'name' => 'Permission Search User',
            'email' => 'permission-search@example.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.search', ['q' => 'Permission Search']));

        $response->assertOk()
            ->assertJsonCount(1, 'groups')
            ->assertJsonPath('groups.0.label', 'Startups')
            ->assertJsonPath('groups.0.items.0.label', 'Permission Search');
    }

    public function test_dashboard_search_query_is_trimmed_and_bounded(): void
    {
        $founder = User::query()->create([
            'name' => 'Query Founder',
            'email' => 'query-founder@example.test',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($founder)
            ->getJson(route('founder.search', ['q' => ' a ']))
            ->assertUnprocessable();

        $this->actingAs($founder)
            ->getJson(route('founder.search', ['q' => str_repeat('a', 81)]))
            ->assertUnprocessable();
    }

    public function test_staff_navigation_hides_unassigned_modules(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Content Staff',
            'email' => 'content-staff@example.test',
            'username' => 'contentstaff',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'allowed_modules' => ['blog'],
            'status' => Admin::STATUS_ENABLED,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.blog.index'), false)
            ->assertDontSee(route('admin.users.index'), false)
            ->assertDontSee(route('admin.migration.index'), false);
    }
}
