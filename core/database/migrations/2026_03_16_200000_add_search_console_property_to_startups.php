<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->string('search_console_property', 500)->nullable()->after('traffic_tracking_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->dropColumn('search_console_property');
        });
    }
};

