<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->unsignedInteger('views')->default(0)->after('upvotes');
            $table->unsignedInteger('clicks')->default(0)->after('views');
            $table->decimal('mrr', 14, 2)->nullable()->after('clicks');
            $table->decimal('revenue', 14, 2)->nullable()->after('mrr');
        });
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->dropColumn(['views', 'clicks', 'mrr', 'revenue']);
        });
    }
};
