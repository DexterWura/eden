<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('listings')) {
            Schema::table('listings', function (Blueprint $table) {
                if (!Schema::hasColumn('listings', 'payout_method')) {
                    $table->string('payout_method', 20)->default('system')->after('sale_type');
                }
                if (!Schema::hasColumn('listings', 'direct_payment_link')) {
                    $table->string('direct_payment_link', 500)->nullable()->after('payout_method');
                }
                if (!Schema::hasColumn('listings', 'direct_payout_fee')) {
                    $table->decimal('direct_payout_fee', 28, 8)->default(0)->after('direct_payment_link');
                }
                if (!Schema::hasColumn('listings', 'direct_payout_fee_paid_at')) {
                    $table->timestamp('direct_payout_fee_paid_at')->nullable()->after('direct_payout_fee');
                }
                if (!Schema::hasColumn('listings', 'direct_payout_fee_trx')) {
                    $table->string('direct_payout_fee_trx', 40)->nullable()->after('direct_payout_fee_paid_at');
                }
            });
        }

        if (Schema::hasTable('escrows')) {
            Schema::table('escrows', function (Blueprint $table) {
                if (!Schema::hasColumn('escrows', 'payment_mode')) {
                    $table->string('payment_mode', 20)->default('system')->after('amount');
                }
                if (!Schema::hasColumn('escrows', 'external_amount')) {
                    $table->decimal('external_amount', 28, 8)->default(0)->after('payment_mode');
                }
                if (!Schema::hasColumn('escrows', 'external_payment_link')) {
                    $table->string('external_payment_link', 500)->nullable()->after('external_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('listings')) {
            Schema::table('listings', function (Blueprint $table) {
                foreach (['direct_payout_fee_trx', 'direct_payout_fee_paid_at', 'direct_payout_fee', 'direct_payment_link', 'payout_method'] as $col) {
                    if (Schema::hasColumn('listings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('escrows')) {
            Schema::table('escrows', function (Blueprint $table) {
                foreach (['external_payment_link', 'external_amount', 'payment_mode'] as $col) {
                    if (Schema::hasColumn('escrows', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};


