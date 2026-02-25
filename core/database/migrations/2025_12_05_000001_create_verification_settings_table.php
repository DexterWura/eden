<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('require_verification')->default(false);
            $table->jsonb('allowed_methods')->default('["file", "dns"]');
            $table->integer('max_verification_attempts')->default(5);
            $table->integer('verification_timeout_seconds')->default(300);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_settings');
    }
};
