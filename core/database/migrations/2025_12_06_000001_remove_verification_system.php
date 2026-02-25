<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('verification_attempts');
        Schema::dropIfExists('verification_settings');
        Schema::dropIfExists('social_media_verifications');
        
        if (Schema::hasTable('domain_verifications')) {
            Schema::dropIfExists('domain_verifications');
        }

        if (Schema::hasColumn('listings', 'requires_verification')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn('requires_verification');
            });
        }

        if (Schema::hasColumn('listings', 'verification_method')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn('verification_method');
            });
        }

        if (Schema::hasColumn('listings', 'verification_token')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->dropColumn('verification_token');
            });
        }
    }

    public function down()
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'requires_verification')) {
                $table->boolean('requires_verification')->default(false);
            }
            if (!Schema::hasColumn('listings', 'verification_method')) {
                $table->string('verification_method')->nullable();
            }
            if (!Schema::hasColumn('listings', 'verification_token')) {
                $table->text('verification_token')->nullable();
            }
        });
    }
};
