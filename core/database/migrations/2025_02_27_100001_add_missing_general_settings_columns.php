<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('general_settings')) {
            return;
        }

        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable();
            }
            if (! Schema::hasColumn('general_settings', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (! Schema::hasColumn('general_settings', 'social_description')) {
                $table->text('social_description')->nullable();
            }
            if (! Schema::hasColumn('general_settings', 'seo_image')) {
                $table->string('seo_image', 500)->nullable();
            }
        });

        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'about_page')) {
                $table->json('about_page')->nullable()->after('seo_image');
            }
        });

        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'adsense_enabled')) {
                $table->boolean('adsense_enabled')->default(false);
            }
            if (! Schema::hasColumn('general_settings', 'adsense_script')) {
                $table->text('adsense_script')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Leave columns in place; other migrations handle their own down().
    }
};
