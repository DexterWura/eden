<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard_to_backoffice_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_enabled_super_admin_can_open_dashboard(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Primary Admin',
            'email' => 'dashboard-admin@example.test',
            'username' => 'dashboardadmin',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'status' => Admin::STATUS_ENABLED,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin command center')
            ->assertSee('Needs attention')
            ->assertSee('Revenue')
            ->assertSee('Ad Spots')
            ->assertSee(route('admin.ad-spots.index'), false);
    }

    public function test_disabled_admin_is_logged_out_and_redirected(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Disabled Admin',
            'email' => 'disabled-admin@example.test',
            'username' => 'disabledadmin',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'allowed_modules' => ['startups'],
            'status' => Admin::STATUS_DISABLED,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_staff_dashboard_only_exposes_permitted_operational_queues(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Startup Reviewer',
            'email' => 'startup-reviewer@example.test',
            'username' => 'startupreviewer',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'allowed_modules' => ['startups'],
            'status' => Admin::STATUS_ENABLED,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pending startups')
            ->assertDontSee('Pending ads &amp; payments', false)
            ->assertDontSee('Money over time')
            ->assertDontSee('Users over time')
            ->assertDontSee('Subscribers</div>', false)
            ->assertDontSee(route('admin.ad-spots.index'), false);
    }

    public function test_admin_password_recovery_page_is_available_to_guests(): void
    {
        $this->get(route('admin.password.reset'))
            ->assertOk()
            ->assertSee('Reset admin password')
            ->assertSee('admin-email');
    }
}
