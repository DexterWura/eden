<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('migration_tracking')) {
            return;
        }

        Schema::create('migration_tracking', function (Blueprint $table): void {
            $table->id();
            $table->string('migration_name')->unique();
            $table->string('file_hash', 64);
            $table->unsignedBigInteger('file_size');
            $table->timestamp('file_modified_at')->nullable();
            $table->string('status', 20)->default('applied')->index();
            $table->timestamp('last_run_at');
            $table->unsignedInteger('run_count')->default(1);
            $table->timestamps();
        });

        if (! Schema::hasTable('migrations')) {
            return;
        }

        $applied = DB::table('migrations')->pluck('migration')->all();
        $now = now();
        foreach (File::glob(database_path('migrations/*.php')) as $path) {
            $name = pathinfo($path, PATHINFO_FILENAME);
            if (! in_array($name, $applied, true)) {
                continue;
            }

            DB::table('migration_tracking')->insertOrIgnore([
                'migration_name' => $name,
                'file_hash' => hash_file('sha256', $path),
                'file_size' => File::size($path),
                'file_modified_at' => date('Y-m-d H:i:s', File::lastModified($path)),
                'status' => 'applied',
                'last_run_at' => $now,
                'run_count' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_tracking');
    }
};
