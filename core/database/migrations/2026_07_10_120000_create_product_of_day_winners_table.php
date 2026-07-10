<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_of_day_winners', function (Blueprint $table) {
            $table->id();
            $table->date('award_date')->unique();
            $table->foreignId('startup_id')->constrained('startups')->cascadeOnDelete();
            $table->unsignedInteger('upvote_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_of_day_winners');
    }
};
