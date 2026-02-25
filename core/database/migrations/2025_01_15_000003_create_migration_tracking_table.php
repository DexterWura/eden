<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('migration_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('migration_name', 255)->unique();
            $table->string('file_hash', 64)->comment('SHA256 hash of migration file');
            $table->integer('file_size')->nullable();
            $table->timestamp('file_modified_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->enum('status', ['pending', 'ran', 'modified', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('migration_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('migration_tracking');
    }
};

