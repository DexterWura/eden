<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable();
            }
            if (!Schema::hasColumn('general_settings', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (!Schema::hasColumn('general_settings', 'social_description')) {
                $table->text('social_description')->nullable();
            }
            if (!Schema::hasColumn('general_settings', 'seo_image')) {
                $table->string('seo_image', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            $columns = ['meta_keywords', 'meta_description', 'social_description', 'seo_image'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('general_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
