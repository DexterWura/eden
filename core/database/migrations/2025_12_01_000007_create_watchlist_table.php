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
        Schema::create('watchlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('listing_id');
            $table->boolean('notify_bid')->default(true)->comment('Notify on new bids');
            $table->boolean('notify_price_change')->default(true)->comment('Notify on price changes');
            $table->boolean('notify_ending')->default(true)->comment('Notify when auction ending');
            $table->timestamps();
            
            $table->unique(['user_id', 'listing_id']);
            $table->index('user_id');
            $table->index('listing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlist');
    }
};

