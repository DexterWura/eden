<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->timestamp('website_last_checked_at')->nullable()->after('website');
            $table->boolean('website_is_reachable')->nullable()->after('website_last_checked_at');
            $table->unsignedTinyInteger('website_consecutive_failures')->default(0)->after('website_is_reachable');
        });
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->dropColumn([
                'website_last_checked_at',
                'website_is_reachable',
                'website_consecutive_failures',
            ]);
        });
    }
};
