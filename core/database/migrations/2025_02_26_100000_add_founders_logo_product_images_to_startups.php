<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            if (!Schema::hasColumn('startups', 'founders')) {
                $table->json('founders')->nullable()->after('founder_linkedin_url');
            }
            if (!Schema::hasColumn('startups', 'logo_path')) {
                $table->string('logo_path', 500)->nullable()->after('founders');
            }
            if (!Schema::hasColumn('startups', 'product_images')) {
                $table->json('product_images')->nullable()->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('startups', 'founders')) $cols[] = 'founders';
            if (Schema::hasColumn('startups', 'logo_path')) $cols[] = 'logo_path';
            if (Schema::hasColumn('startups', 'product_images')) $cols[] = 'product_images';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
