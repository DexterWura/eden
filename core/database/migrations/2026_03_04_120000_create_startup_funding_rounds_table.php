<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_funding_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->string('round_type', 50)->default('seed');
            $table->decimal('amount_seeking', 14, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
            $table->index(['startup_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_funding_rounds');
    }
};
