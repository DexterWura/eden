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
        Schema::create('listing_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->date('period_date');
            $table->string('period_type', 20)->default('monthly'); // monthly, weekly, daily
            
            // Revenue Metrics
            $table->decimal('revenue', 28, 8)->default(0);
            $table->decimal('expenses', 28, 8)->default(0);
            $table->decimal('profit', 28, 8)->default(0);
            
            // Traffic Metrics
            $table->bigInteger('visitors')->default(0);
            $table->bigInteger('page_views')->default(0);
            $table->bigInteger('unique_visitors')->default(0);
            
            // Social/App Metrics
            $table->bigInteger('followers')->default(0);
            $table->bigInteger('subscribers')->default(0);
            $table->bigInteger('downloads')->default(0);
            $table->decimal('engagement_rate', 8, 4)->default(0);
            
            // Email List
            $table->bigInteger('email_subscribers')->default(0);
            
            // Notes
            $table->text('notes')->nullable();
            
            // Verification
            $table->boolean('is_verified')->default(false);
            $table->string('proof_document', 255)->nullable();
            
            $table->timestamps();
            
            $table->index(['listing_id', 'period_date']);
            $table->unique(['listing_id', 'period_date', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_metrics');
    }
};

