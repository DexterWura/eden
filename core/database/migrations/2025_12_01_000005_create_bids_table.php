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
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->string('bid_number', 40)->unique();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 28, 8);
            $table->decimal('max_bid', 28, 8)->default(0)->comment('Auto-bid maximum');
            $table->boolean('is_auto_bid')->default(false);
            
            // Status: 0=active, 1=outbid, 2=winning, 3=won, 4=lost, 5=cancelled
            $table->tinyInteger('status')->default(0);
            
            $table->boolean('is_buy_now')->default(false)->comment('If used buy now option');
            $table->text('notes')->nullable();
            $table->string('ip_address', 50)->nullable();
            
            $table->timestamps();
            
            $table->index('listing_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};

