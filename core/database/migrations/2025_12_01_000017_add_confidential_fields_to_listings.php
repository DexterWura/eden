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
        if (!Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'is_confidential')) {
                $table->boolean('is_confidential')->default(false)->after('is_verified');
            }
            if (!Schema::hasColumn('listings', 'requires_nda')) {
                $table->boolean('requires_nda')->default(false)->after('is_confidential');
            }
            if (!Schema::hasColumn('listings', 'confidential_reason')) {
                $table->text('confidential_reason')->nullable()->after('requires_nda');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('listings')) {
            return;
        }

        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'confidential_reason')) {
                $table->dropColumn('confidential_reason');
            }
            if (Schema::hasColumn('listings', 'requires_nda')) {
                $table->dropColumn('requires_nda');
            }
            if (Schema::hasColumn('listings', 'is_confidential')) {
                $table->dropColumn('is_confidential');
            }
        });
    }
};
