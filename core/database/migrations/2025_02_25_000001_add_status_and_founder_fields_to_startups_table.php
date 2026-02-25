<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->string('status', 32)->default('active')->after('linkedin_url');
            $table->string('founder_email', 255)->nullable()->after('founder_name');
            $table->string('founder_twitter_url', 500)->nullable()->after('founder_email');
            $table->string('founder_linkedin_url', 500)->nullable()->after('founder_twitter_url');
        });
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->dropColumn(['status', 'founder_email', 'founder_twitter_url', 'founder_linkedin_url']);
        });
    }
};
