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
        Schema::table('listings', function (Blueprint $table) {
            $table->string('primary_asset_url', 500)->nullable()->after('url')->comment('Primary asset URL/handle for ownership validation');
            $table->boolean('owner_verified')->default(false)->after('primary_asset_url')->comment('Whether ownership has been verified');
            $table->string('ownership_verification_method', 50)->nullable()->after('owner_verified')->comment('Method used for verification: dns_txt, html_meta, file_upload, oauth_login');
            $table->timestamp('ownership_verified_at')->nullable()->after('ownership_verification_method');
            
            $table->index('owner_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['owner_verified']);
            $table->dropColumn([
                'primary_asset_url',
                'owner_verified',
                'ownership_verification_method',
                'ownership_verified_at'
            ]);
        });
    }
};

