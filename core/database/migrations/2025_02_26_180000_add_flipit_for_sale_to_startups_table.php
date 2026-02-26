<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->boolean('for_sale')->default(false)->after('dormant_at');
            $table->string('flipit_listing_id', 255)->nullable()->after('for_sale');
            $table->timestamp('sold_at')->nullable()->after('flipit_listing_id');
        });

        Schema::table('startups', function (Blueprint $table) {
            $table->index('flipit_listing_id');
        });
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->dropIndex(['flipit_listing_id']);
            $table->dropColumn(['for_sale', 'flipit_listing_id', 'sold_at']);
        });
    }
};
