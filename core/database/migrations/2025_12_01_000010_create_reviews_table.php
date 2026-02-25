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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('escrow_id')->default(0);
            $table->unsignedBigInteger('reviewer_id');
            $table->unsignedBigInteger('reviewed_user_id');
            
            // Review Type: buyer_review, seller_review
            $table->enum('review_type', ['buyer_review', 'seller_review']);
            
            // Ratings (1-5)
            $table->tinyInteger('overall_rating');
            $table->tinyInteger('communication_rating')->nullable();
            $table->tinyInteger('accuracy_rating')->nullable()->comment('As described rating');
            $table->tinyInteger('timeliness_rating')->nullable();
            
            $table->text('review');
            $table->text('seller_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            // Status: 0=pending, 1=approved, 2=hidden
            $table->tinyInteger('status')->default(1);
            
            $table->timestamps();
            
            $table->index('listing_id');
            $table->index('reviewer_id');
            $table->index('reviewed_user_id');
            $table->unique(['listing_id', 'reviewer_id', 'review_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

