<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained('startups')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_email', 255);
            $table->string('reason', 32);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('startup_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_reports');
    }
};
