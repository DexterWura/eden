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
            if (! Schema::hasColumn('general_settings', 'robots_txt')) {
                $table->text('robots_txt')->nullable()->after('adsense_script');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('general_settings') && Schema::hasColumn('general_settings', 'robots_txt')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn('robots_txt');
            });
        }
    }
};
