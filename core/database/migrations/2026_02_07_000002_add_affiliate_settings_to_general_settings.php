<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'affiliate_enable')) {
                $table->unsignedTinyInteger('affiliate_enable')->default(0)->comment('0=off, 1=on');
            }
            if (!Schema::hasColumn('general_settings', 'affiliate_signup_amount')) {
                $table->decimal('affiliate_signup_amount', 18, 2)->default(0)->comment('Amount paid to referrer when referred user registers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('general_settings')) {
            return;
        }
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'affiliate_enable')) {
                $table->dropColumn('affiliate_enable');
            }
            if (Schema::hasColumn('general_settings', 'affiliate_signup_amount')) {
                $table->dropColumn('affiliate_signup_amount');
            }
        });
    }
};
