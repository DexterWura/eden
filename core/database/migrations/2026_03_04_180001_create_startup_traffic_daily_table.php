<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_traffic_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('visits')->default(0);
            $table->timestamps();

            $table->unique(['startup_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_traffic_daily');
    }
};
