<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('startups', 'hero_request_status')) {
            Schema::table('startups', function (Blueprint $table) {
                $table->string('hero_request_status', 20)->nullable()->after('featured_on_hero');
            });
        }
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->dropColumn('hero_request_status');
        });
    }
};
