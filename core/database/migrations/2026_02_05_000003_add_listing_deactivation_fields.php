<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'is_deactivated')) {
                $table->boolean('is_deactivated')->default(false)->after('status');
            }
            if (!Schema::hasColumn('listings', 'deactivation_reason')) {
                $table->text('deactivation_reason')->nullable()->after('is_deactivated');
            }
            if (!Schema::hasColumn('listings', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('deactivation_reason');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'deactivated_at')) {
                $table->dropColumn('deactivated_at');
            }
            if (Schema::hasColumn('listings', 'deactivation_reason')) {
                $table->dropColumn('deactivation_reason');
            }
            if (Schema::hasColumn('listings', 'is_deactivated')) {
                $table->dropColumn('is_deactivated');
            }
        });
    }
};
