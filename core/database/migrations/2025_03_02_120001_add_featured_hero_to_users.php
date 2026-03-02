<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'linkedin_url')) {
                $table->string('linkedin_url', 500)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'featured_on_hero')) {
                $table->boolean('featured_on_hero')->default(false)->after('linkedin_url');
            }
            if (! Schema::hasColumn('users', 'hero_photo_url')) {
                $table->string('hero_photo_url', 500)->nullable()->after('featured_on_hero');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'hero_photo_url')) {
                $table->dropColumn('hero_photo_url');
            }
            if (Schema::hasColumn('users', 'featured_on_hero')) {
                $table->dropColumn('featured_on_hero');
            }
            if (Schema::hasColumn('users', 'linkedin_url')) {
                $table->dropColumn('linkedin_url');
            }
        });
    }
};
