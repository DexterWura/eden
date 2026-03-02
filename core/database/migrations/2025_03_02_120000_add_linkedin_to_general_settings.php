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
            if (! Schema::hasColumn('general_settings', 'linkedin_client_id')) {
                $table->string('linkedin_client_id', 255)->nullable()->after('adsense_script');
            }
            if (! Schema::hasColumn('general_settings', 'linkedin_client_secret')) {
                $table->string('linkedin_client_secret', 255)->nullable()->after('linkedin_client_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'linkedin_client_secret')) {
                $table->dropColumn('linkedin_client_secret');
            }
            if (Schema::hasColumn('general_settings', 'linkedin_client_id')) {
                $table->dropColumn('linkedin_client_id');
            }
        });
    }
};
