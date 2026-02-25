<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('requires_verification')->default(false)->after('is_verified')
                ->comment('Does this listing require domain verification');
            $table->integer('auction_duration_days')->nullable()->after('verification_notes')
                ->comment('Stored duration for auction start after approval');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['requires_verification', 'auction_duration_days']);
        });
    }
};

