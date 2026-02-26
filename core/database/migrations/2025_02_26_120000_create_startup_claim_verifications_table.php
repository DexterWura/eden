<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_claim_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained('startups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('method', 16);
            $table->string('verification_code', 64);
            $table->string('verification_file_name', 128)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['startup_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_claim_verifications');
    }
};
