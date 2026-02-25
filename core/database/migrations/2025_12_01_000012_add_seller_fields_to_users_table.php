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
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('address');
            $table->string('website', 255)->nullable()->after('bio');
            $table->string('company_name', 255)->nullable()->after('website');
            $table->boolean('is_verified_seller')->default(false)->after('company_name');
            $table->timestamp('seller_verified_at')->nullable()->after('is_verified_seller');
            
            // Seller Stats
            $table->integer('total_listings')->default(0)->after('seller_verified_at');
            $table->integer('total_sales')->default(0)->after('total_listings');
            $table->decimal('total_sales_value', 28, 8)->default(0)->after('total_sales');
            $table->integer('total_purchases')->default(0)->after('total_sales_value');
            $table->decimal('avg_rating', 3, 2)->default(0)->after('total_purchases');
            $table->integer('total_reviews')->default(0)->after('avg_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'website',
                'company_name',
                'is_verified_seller',
                'seller_verified_at',
                'total_listings',
                'total_sales',
                'total_sales_value',
                'total_purchases',
                'avg_rating',
                'total_reviews'
            ]);
        });
    }
};

