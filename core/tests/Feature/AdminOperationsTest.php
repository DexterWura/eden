<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminOperationNotification;
use App\Models\AdSpot;
use App\Models\Startup;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_totp_matches_rfc_6238_sha1_vector_and_rejects_bad_codes(): void
    {
        $totp = app(TotpService::class);
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertTrue($totp->verify($secret, '287082', 59));
        $this->assertFalse($totp->verify($secret, '287083', 59));
        $this->assertFalse($totp->verify($secret, 'not-a-code', 59));
    }

    public function test_enabled_two_factor_redirects_admin_to_challenge(): void
    {
        $admin = $this->admin(true);
        $admin->forceFill([
            'two_factor_secret' => app(TotpService::class)->generateSecret(),
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.operations.health'))
            ->assertRedirect(route('admin.security.challenge'));
    }

    public function test_admin_search_requires_completed_two_factor_challenge(): void
    {
        $admin = $this->admin(true);
        $admin->forceFill([
            'two_factor_secret' => app(TotpService::class)->generateSecret(),
            'two_factor_recovery_codes' => [],
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.search', ['q' => 'startup']))
            ->assertRedirect(route('admin.security.challenge'));
    }

    public function test_staff_can_only_open_assigned_operations_modules(): void
    {
        $admin = $this->admin(false, ['moderation']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.operations.moderation'))
            ->assertOk();
        $this->actingAs($admin, 'admin')
            ->get(route('admin.operations.payments'))
            ->assertForbidden();
        $this->actingAs($admin, 'admin')
            ->get(route('admin.operations.health'))
            ->assertForbidden();
    }

    public function test_bulk_moderation_validates_action_per_queue_and_audits_success(): void
    {
        $admin = $this->admin(false, ['moderation']);
        $startup = Startup::query()->create([
            'name' => 'Queue Test',
            'slug' => 'queue-test',
            'status' => Startup::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.operations.moderation.bulk'), [
                'queue' => 'startups',
                'action' => 'activate',
                'ids' => [$startup->id, 999999],
            ])
            ->assertRedirect();

        $this->assertSame(Startup::STATUS_ACTIVE, $startup->fresh()->status);
        $this->assertDatabaseHas('admin_audit_log', [
            'admin_id' => $admin->id,
            'action' => 'moderation.startup.activate',
            'subject_id' => $startup->id,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.operations.moderation.bulk'), [
                'queue' => 'reports',
                'action' => 'activate',
                'ids' => [1],
            ])
            ->assertSessionHasErrors('action');
    }

    public function test_bulk_startup_activation_uses_the_publication_quality_gate(): void
    {
        $admin = $this->admin(false, ['moderation']);
        $startup = Startup::query()->create([
            'name' => 'Incomplete Queue Test',
            'slug' => 'incomplete-queue-test',
            'status' => Startup::STATUS_PENDING,
            'content_quality_version' => 1,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.operations.moderation.bulk'), [
                'queue' => 'startups',
                'action' => 'activate',
                'ids' => [$startup->id],
            ])
            ->assertRedirect();

        $this->assertSame(Startup::STATUS_PENDING, $startup->fresh()->status);
        $this->assertDatabaseMissing('admin_audit_log', [
            'action' => 'moderation.startup.activate',
            'subject_id' => $startup->id,
        ]);
    }

    public function test_destructive_super_admin_route_requires_recent_reconfirmation(): void
    {
        $admin = $this->admin(true);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.cache.clear'))
            ->assertRedirect(route('admin.security.reconfirm'));
    }

    public function test_reconfirmation_never_stores_an_external_referer(): void
    {
        $admin = $this->admin(true);

        $this->actingAs($admin, 'admin')
            ->withHeader('Referer', 'https://attacker.example/phishing')
            ->post(route('admin.cache.clear'))
            ->assertRedirect(route('admin.security.reconfirm'))
            ->assertSessionHas('admin_reconfirm_return_to', route('admin.dashboard', absolute: false));
    }

    public function test_validation_redirect_is_audited_as_failed(): void
    {
        $admin = $this->admin(false, ['moderation']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.operations.moderation.bulk'), [])
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('admin_audit_log', [
            'admin_id' => $admin->id,
            'action' => 'admin.request.admin_operations_moderation_bulk',
            'description' => 'Admin operation failed.',
        ]);
    }

    public function test_broadcast_notification_read_state_is_per_admin(): void
    {
        $first = $this->admin(false, ['admin_notifications']);
        $second = $this->admin(false, ['admin_notifications']);
        $notification = AdminOperationNotification::query()->create([
            'admin_id' => null,
            'type' => 'operations',
            'title' => 'Maintenance',
            'message' => 'Maintenance scheduled.',
        ]);

        $this->actingAs($first, 'admin')
            ->post(route('admin.operations.notifications.read'), ['ids' => [$notification->id]])
            ->assertRedirect();

        $this->assertDatabaseHas('admin_operation_notification_reads', [
            'admin_operation_notification_id' => $notification->id,
            'admin_id' => $first->id,
        ]);
        $this->assertDatabaseMissing('admin_operation_notification_reads', [
            'admin_operation_notification_id' => $notification->id,
            'admin_id' => $second->id,
        ]);
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_payment_ledger_filters_in_database_and_never_infers_ad_price(): void
    {
        $admin = $this->admin(false, ['payments']);
        AdSpot::query()->create([
            'placement' => 'home-header',
            'target_url' => 'https://example.test',
            'contact_email' => 'ledger@example.test',
            'payment_reference' => 'MATCH-REFERENCE',
            'gateway' => 'manual',
            'status' => AdSpot::STATUS_PENDING,
            'amount' => null,
            'currency' => null,
        ]);
        AdSpot::query()->create([
            'placement' => 'home-header',
            'target_url' => 'https://example.test',
            'contact_email' => 'hidden@example.test',
            'payment_reference' => 'OTHER-REFERENCE',
            'gateway' => 'manual',
            'status' => AdSpot::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.operations.payments', ['type' => 'ad', 'reference' => 'MATCH']))
            ->assertOk()
            ->assertSee('ledger@example.test')
            ->assertSee('Unknown')
            ->assertDontSee('hidden@example.test');
    }

    public function test_admin_operations_migration_is_idempotent(): void
    {
        $migration = require database_path('migrations/2026_07_24_160000_add_admin_operations_security.php');
        $migration->up();
        $migration->up();

        $this->assertDatabaseCount('admin_audit_log', 0);
        $this->assertDatabaseCount('admin_operation_notifications', 0);
    }

    public function test_per_admin_notification_delivery_migration_is_idempotent(): void
    {
        $migration = require database_path('migrations/2026_07_24_190000_add_per_admin_operation_notification_reads.php');
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasTable('admin_operation_notification_reads'));
    }

    private function admin(bool $superAdmin, array $modules = []): Admin
    {
        return Admin::query()->create([
            'name' => $superAdmin ? 'Operations Owner' : 'Operations Staff',
            'email' => uniqid('admin-', true) . '@example.test',
            'username' => uniqid('admin-', true),
            'password' => Hash::make('long-test-password'),
            'is_super_admin' => $superAdmin,
            'allowed_modules' => $modules,
            'status' => Admin::STATUS_ENABLED,
        ]);
    }
}
