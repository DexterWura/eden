<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('listing_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();
            
            $table->index('listing_id');
            $table->index(['listing_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_views');
    }
};

