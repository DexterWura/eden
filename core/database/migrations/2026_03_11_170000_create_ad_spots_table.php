<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_spots', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 64);
            $table->string('image_path');
            $table->string('target_url');
            $table->string('status', 20)->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('payment_reference', 64)->nullable()->index();
            $table->string('gateway', 32)->nullable();
            $table->timestamps();

            $table->index(['placement', 'status']);
            $table->index(['placement', 'status', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_spots');
    }
};

