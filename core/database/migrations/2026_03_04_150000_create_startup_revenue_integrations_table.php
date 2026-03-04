<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_revenue_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 50);
            $table->text('credentials');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status', 50)->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['startup_id', 'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_revenue_integrations');
    }
};
