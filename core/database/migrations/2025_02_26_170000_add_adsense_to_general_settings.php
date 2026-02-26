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
        if (! Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'adsense_script')) {
                $table->dropColumn('adsense_script');
            }
            if (Schema::hasColumn('general_settings', 'adsense_enabled')) {
                $table->dropColumn('adsense_enabled');
            }
        });
    }
};
