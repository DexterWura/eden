<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_verification_id')->constrained()->onDelete('cascade');
            $table->integer('attempt_number');
            $table->string('method', 50);
            $table->jsonb('request_data')->nullable();
            $table->jsonb('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at');
            $table->integer('duration_ms')->nullable();
            $table->index('domain_verification_id');
            $table->index('attempted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_attempts');
    }
};
