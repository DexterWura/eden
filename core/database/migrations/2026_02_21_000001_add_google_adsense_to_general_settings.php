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
            if (!Schema::hasColumn('general_settings', 'google_adsense_enable')) {
                $table->unsignedTinyInteger('google_adsense_enable')->default(0)->comment('0=off, 1=on');
            }
            if (!Schema::hasColumn('general_settings', 'google_adsense_script')) {
                $table->text('google_adsense_script')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'google_adsense_enable')) {
                $table->dropColumn('google_adsense_enable');
            }
            if (Schema::hasColumn('general_settings', 'google_adsense_script')) {
                $table->dropColumn('google_adsense_script');
            }
        });
    }
};
