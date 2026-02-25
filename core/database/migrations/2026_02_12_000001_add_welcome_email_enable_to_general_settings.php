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
            if (!Schema::hasColumn('general_settings', 'welcome_email_enable')) {
                $table->unsignedTinyInteger('welcome_email_enable')->default(1)->comment('0=off, 1=on; when on, new users receive a welcome email on signup');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'welcome_email_enable')) {
                $table->dropColumn('welcome_email_enable');
            }
        });
    }
};
