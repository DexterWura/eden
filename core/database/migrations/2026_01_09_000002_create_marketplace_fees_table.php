<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_fees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            // escrow_service_fee or direct_payout_listing_fee
            $table->string('context', 40);
            // buyer or seller
            $table->string('payer', 20);
            $table->decimal('percent', 8, 4)->default(0);
            $table->decimal('fixed', 28, 8)->default(0);
            // 0 or null means no cap
            $table->decimal('cap', 28, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['context', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_fees');
    }
};


