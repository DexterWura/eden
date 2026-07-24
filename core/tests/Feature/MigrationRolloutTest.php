<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MigrationTracking;
use App\Services\MigrationDriftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MigrationRolloutTest extends TestCase
{
    use RefreshDatabase;

    private const MIGRATION = '2026_07_24_170000_create_migration_tracking_table';

    public function test_applied_migration_with_changed_execution_checksum_is_reported_as_drift(): void
    {
        $this->trackMigration('not-the-current-checksum');

        $state = app(MigrationDriftService::class)->state(self::MIGRATION);

        $this->assertSame('modified', $state['state']);
        $this->assertNotSame($state['execution_hash'], $state['current_hash']);
    }

    public function test_status_check_does_not_replace_execution_checksum(): void
    {
        $admin = $this->admin();
        $tracking = $this->trackMigration('immutable-execution-checksum');

        $this->actingAs($admin, 'admin')
            ->withSession(['admin_reconfirmed_at' => time()])
            ->post(route('admin.migration.check'))
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertSame('immutable-execution-checksum', $tracking->fresh()->file_hash);
        $this->assertGreaterThanOrEqual(1, app(MigrationDriftService::class)->drifted()->count());
    }

    public function test_first_tracking_rollout_baselines_history_without_overwriting_hashes(): void
    {
        $historical = DB::table('migrations')
            ->where('migration', '<', self::MIGRATION)
            ->orderBy('migration')
            ->value('migration');
        $this->assertNotNull($historical);
        MigrationTracking::query()->where('migration_name', $historical)->delete();
        $immutable = $this->trackMigration('immutable-execution-checksum');

        app(MigrationDriftService::class)->recordApplied(collect());

        $this->assertDatabaseHas('migration_tracking', ['migration_name' => $historical]);
        $this->assertSame('immutable-execution-checksum', $immutable->fresh()->file_hash);
    }

    public function test_dashboard_shows_forward_repair_workflow_for_drift(): void
    {
        $this->trackMigration('old-checksum');

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.migration.index'))
            ->assertOk()
            ->assertSee('Migration drift requires review')
            ->assertSee('php artisan make:migration repair_create_migration_tracking_table');
    }

    public function test_applied_migration_cannot_be_run_again(): void
    {
        $this->trackMigration(hash_file('sha256', database_path('migrations/' . self::MIGRATION . '.php')));

        $this->actingAs($this->admin(), 'admin')
            ->withSession(['admin_reconfirmed_at' => time()])
            ->postJson(route('admin.migration.run.specific', ['migration' => self::MIGRATION]), [
                'confirm' => true,
            ])
            ->assertConflict()
            ->assertJsonPath('repair.command', 'php artisan make:migration repair_create_migration_tracking_table');
    }

    private function trackMigration(string $hash): MigrationTracking
    {
        return MigrationTracking::query()->create([
            'migration_name' => self::MIGRATION,
            'file_hash' => $hash,
            'file_size' => 1,
            'file_modified_at' => now(),
            'status' => 'applied',
            'last_run_at' => now(),
            'run_count' => 1,
        ]);
    }

    private function admin(): Admin
    {
        return Admin::query()->create([
            'name' => 'Migration Owner',
            'email' => uniqid('migration-', true) . '@example.test',
            'username' => uniqid('migration-', true),
            'password' => Hash::make('long-test-password'),
            'is_super_admin' => true,
            'allowed_modules' => [],
            'status' => Admin::STATUS_ENABLED,
        ]);
    }
}
