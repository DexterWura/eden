<?php

namespace App\Services;

use App\Models\MigrationTracking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MigrationDriftService
{
    public function states(): Collection
    {
        $files = $this->migrationFiles()->keyBy('migration_name');
        $applied = $this->appliedMigrations();
        $tracking = $this->trackingRecords();
        $names = $files->keys()->merge(array_keys($applied))->unique()->sort()->values();

        return $names->map(function (string $name) use ($files, $applied, $tracking): array {
            $file = $files->get($name);
            $record = $tracking->get($name);
            $isApplied = array_key_exists($name, $applied);
            $state = 'pending';

            if ($isApplied && ! $file) {
                $state = 'missing_file';
            } elseif ($isApplied && ! $record) {
                $state = 'untracked';
            } elseif ($isApplied && ! hash_equals((string) $record->file_hash, (string) $file['hash'])) {
                $state = 'modified';
            } elseif ($isApplied) {
                $state = 'applied';
            }

            return [
                'name' => $name,
                'file' => $file,
                'state' => $state,
                'batch' => $applied[$name] ?? null,
                'execution_hash' => $record?->file_hash,
                'current_hash' => $file['hash'] ?? null,
                'tracked_at' => $record?->last_run_at,
            ];
        });
    }

    public function pending(): Collection
    {
        return $this->states()->where('state', 'pending')->values();
    }

    public function drifted(): Collection
    {
        return $this->states()
            ->whereIn('state', ['modified', 'missing_file', 'untracked'])
            ->values();
    }

    public function recordApplied(Collection $beforeStates): void
    {
        if (! Schema::hasTable('migration_tracking')) {
            return;
        }

        $applied = $this->appliedMigrations();
        $this->recordCurrentAppliedMigrations($applied);
        foreach ($beforeStates->where('state', 'pending') as $state) {
            $name = $state['name'];
            $file = $state['file'];
            if (! isset($applied[$name]) || ! $file) {
                continue;
            }

            MigrationTracking::query()->firstOrCreate(['migration_name' => $name], [
                'file_hash' => $file['hash'],
                'file_size' => $file['size'],
                'file_modified_at' => $file['modified_at'],
                'status' => 'applied',
                'last_run_at' => now(),
                'run_count' => 1,
            ]);
        }
    }

    public function state(string $migrationName): ?array
    {
        return $this->states()->firstWhere('name', $migrationName);
    }

    private function migrationFiles(): Collection
    {
        return collect(File::glob(database_path('migrations/*.php')))
            ->map(function (string $path): array {
                $filename = basename($path);

                return [
                    'name' => $filename,
                    'migration_name' => substr($filename, 0, -4),
                    'path' => $path,
                    'size' => File::size($path),
                    'modified_at' => date('Y-m-d H:i:s', File::lastModified($path)),
                    'hash' => hash_file('sha256', $path),
                ];
            })
            ->sortBy('name')
            ->values();
    }

    private function appliedMigrations(): array
    {
        if (! Schema::hasTable('migrations')) {
            return [];
        }

        return DB::table('migrations')->pluck('batch', 'migration')->all();
    }

    private function trackingRecords(): Collection
    {
        if (! Schema::hasTable('migration_tracking')) {
            return collect();
        }

        return MigrationTracking::query()->get()->keyBy('migration_name');
    }

    public function recordCurrentAppliedMigrations(?array $applied = null): int
    {
        if (! Schema::hasTable('migration_tracking')) {
            return 0;
        }

        $applied ??= $this->appliedMigrations();
        $files = $this->migrationFiles()->keyBy('migration_name');
        $recorded = 0;
        foreach (array_keys($applied) as $name) {
            $file = $files->get($name);
            if (! $file) {
                continue;
            }

            $tracking = MigrationTracking::query()->firstOrCreate(['migration_name' => $name], [
                'file_hash' => $file['hash'],
                'file_size' => $file['size'],
                'file_modified_at' => $file['modified_at'],
                'status' => 'applied',
                'last_run_at' => now(),
                'run_count' => 1,
            ]);
            if ($tracking->wasRecentlyCreated) {
                $recorded++;
            }
        }

        return $recorded;
    }

    public function removeTrackingForRolledBackMigrations(): int
    {
        if (! Schema::hasTable('migration_tracking')) {
            return 0;
        }

        $appliedNames = array_keys($this->appliedMigrations());
        if ($appliedNames === []) {
            return MigrationTracking::query()->delete();
        }

        return MigrationTracking::query()
            ->whereNotIn('migration_name', $appliedNames)
            ->delete();
    }
}
