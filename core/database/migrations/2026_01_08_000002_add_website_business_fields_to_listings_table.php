<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'business_location')) {
                $table->string('business_location', 150)->nullable()->after('domain_age_years');
            }
            if (!Schema::hasColumn('listings', 'overall_churn_percent')) {
                $table->decimal('overall_churn_percent', 5, 2)->nullable()->after('business_location');
            }
            if (!Schema::hasColumn('listings', 'site_age_months')) {
                $table->unsignedInteger('site_age_months')->nullable()->after('overall_churn_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'site_age_months')) {
                $table->dropColumn('site_age_months');
            }
            if (Schema::hasColumn('listings', 'overall_churn_percent')) {
                $table->dropColumn('overall_churn_percent');
            }
            if (Schema::hasColumn('listings', 'business_location')) {
                $table->dropColumn('business_location');
            }
        });
    }
};


