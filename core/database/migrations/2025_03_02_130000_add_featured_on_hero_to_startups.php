<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('startups')) {
            return;
        }
        Schema::table('startups', function (Blueprint $table) {
            if (! Schema::hasColumn('startups', 'featured_on_hero')) {
                $table->boolean('featured_on_hero')->default(false)->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('startups')) {
            return;
        }
        Schema::table('startups', function (Blueprint $table) {
            if (Schema::hasColumn('startups', 'featured_on_hero')) {
                $table->dropColumn('featured_on_hero');
            }
        });
    }
};
