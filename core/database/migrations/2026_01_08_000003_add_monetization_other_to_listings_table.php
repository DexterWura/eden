<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'monetization_other')) {
                $table->string('monetization_other', 255)->nullable()->after('monetization_methods');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'monetization_other')) {
                $table->dropColumn('monetization_other');
            }
        });
    }
};


