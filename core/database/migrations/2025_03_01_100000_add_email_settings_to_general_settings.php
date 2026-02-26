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
            if (! Schema::hasColumn('general_settings', 'email_from_name')) {
                $table->string('email_from_name', 191)->nullable();
            }
            if (! Schema::hasColumn('general_settings', 'email_from')) {
                $table->string('email_from', 191)->nullable();
            }
            if (! Schema::hasColumn('general_settings', 'email_template')) {
                $table->longText('email_template')->nullable();
            }
        });

        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'mail_config')) {
                $table->json('mail_config')->nullable();
            }
        });

        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'en')) {
                $table->boolean('en')->default(false);
            }
            if (! Schema::hasColumn('general_settings', 'welcome_email_enable')) {
                $table->boolean('welcome_email_enable')->default(false);
            }
            if (! Schema::hasColumn('general_settings', 'ev')) {
                $table->boolean('ev')->default(false);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('general_settings')) {
            return;
        }

        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'email_template')) {
                $table->dropColumn('email_template');
            }
            if (Schema::hasColumn('general_settings', 'email_from')) {
                $table->dropColumn('email_from');
            }
            if (Schema::hasColumn('general_settings', 'email_from_name')) {
                $table->dropColumn('email_from_name');
            }
            if (Schema::hasColumn('general_settings', 'mail_config')) {
                $table->dropColumn('mail_config');
            }
            if (Schema::hasColumn('general_settings', 'en')) {
                $table->dropColumn('en');
            }
            if (Schema::hasColumn('general_settings', 'welcome_email_enable')) {
                $table->dropColumn('welcome_email_enable');
            }
            if (Schema::hasColumn('general_settings', 'ev')) {
                $table->dropColumn('ev');
            }
        });
    }
};

