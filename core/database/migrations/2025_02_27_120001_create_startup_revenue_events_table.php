<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_revenue_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained('startups')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3);
            $table->string('external_id', 255)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['startup_id', 'external_id'], 'startup_revenue_events_startup_external_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_revenue_events');
    }
};
