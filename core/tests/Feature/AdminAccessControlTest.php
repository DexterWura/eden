<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_defaults_do_not_grant_super_admin_access(): void
    {
        $admin = new Admin();

        $this->assertFalse($admin->isSuperAdmin());
        $this->assertTrue($admin->isEnabled());
        $this->assertFalse($admin->hasModule('startups'));
    }

    public function test_access_control_migration_preserves_primary_admin_access_idempotently(): void
    {
        $admin = $this->createAdmin(false);
        $migration = require database_path('migrations/2026_07_24_130000_add_access_control_fields_to_admins_table.php');

        $migration->up();
        $migration->up();

        $this->assertTrue($admin->fresh()->isSuperAdmin());
        $this->assertTrue($admin->fresh()->isEnabled());
    }

    public function test_staff_can_access_assigned_module_and_not_other_modules(): void
    {
        $admin = $this->createAdmin(false, ['startups']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.startups.index'))
            ->assertOk();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_staff_cannot_access_migrations_or_destructive_system_routes(): void
    {
        $admin = $this->createAdmin(false, ['scheduled_tasks', 'gateways']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.migration.index'))
            ->assertForbidden();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.cache.clear'))
            ->assertForbidden();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.gateways.seed'))
            ->assertForbidden();
    }

    public function test_super_admin_can_access_migration_status_page(): void
    {
        $admin = $this->createAdmin(true);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.migration.index'))
            ->assertOk();
    }

    public function test_admin_logout_requires_post_and_clears_admin_session(): void
    {
        $admin = $this->createAdmin(true);
        $logoutRoute = Route::getRoutes()->getByName('admin.logout');

        $this->assertSame(['POST'], $logoutRoute->methods());
        $this->actingAs($admin, 'admin')
            ->get(route('admin.logout'))
            ->assertMethodNotAllowed();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_cron_endpoint_fails_closed_when_secret_is_not_configured(): void
    {
        putenv('CRON_SECRET');
        unset($_ENV['CRON_SECRET'], $_SERVER['CRON_SECRET']);

        $this->get(route('cron'))
            ->assertServiceUnavailable();
    }

    public function test_admin_can_sign_in_as_user_and_return_to_admin(): void
    {
        $admin = $this->createAdmin(true);
        $user = \App\Models\User::query()->create([
            'name' => 'Founder View',
            'email' => 'founder-view@example.test',
            'password' => Hash::make('password'),
            'status' => \App\Constants\Status::USER_ACTIVE,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.users.login-as', $user))
            ->assertRedirect(route('founder.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertTrue(session()->has('eden_impersonator_admin_id'));

        $this->get(route('founder.dashboard'))
            ->assertOk()
            ->assertSee('Viewing as')
            ->assertSee('Return to admin');

        $this->post(route('impersonation.leave'))
            ->assertRedirect(route('admin.users.index'));

        $this->assertGuest();
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertFalse(session()->has('eden_impersonator_admin_id'));
    }

    private function createAdmin(bool $superAdmin, array $modules = []): Admin
    {
        return Admin::query()->create([
            'name' => $superAdmin ? 'Super Admin' : 'Staff Admin',
            'email' => ($superAdmin ? 'super' : 'staff') . '-' . uniqid() . '@example.test',
            'username' => ($superAdmin ? 'super' : 'staff') . uniqid(),
            'password' => Hash::make('password'),
            'is_super_admin' => $superAdmin,
            'allowed_modules' => $modules,
            'status' => Admin::STATUS_ENABLED,
        ]);
    }
}
