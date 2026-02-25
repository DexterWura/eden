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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('offer_number', 40)->unique();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('buyer_id');
            $table->unsignedBigInteger('seller_id');
            $table->decimal('amount', 28, 8);
            $table->text('message')->nullable();
            
            // Status: 0=pending, 1=accepted, 2=rejected, 3=countered, 4=expired, 5=cancelled, 6=completed
            $table->tinyInteger('status')->default(0);
            
            // Counter offer
            $table->decimal('counter_amount', 28, 8)->default(0);
            $table->text('counter_message')->nullable();
            $table->timestamp('countered_at')->nullable();
            
            // Expiration
            $table->timestamp('expires_at')->nullable();
            
            // Response
            $table->text('rejection_reason')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            // Escrow Integration
            $table->unsignedBigInteger('escrow_id')->default(0);
            
            $table->timestamps();
            
            $table->index('listing_id');
            $table->index('buyer_id');
            $table->index('seller_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};

