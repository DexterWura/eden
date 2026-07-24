<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->text('problem_solved')->nullable()->after('description');
            $table->text('target_customer')->nullable()->after('problem_solved');
            $table->json('key_features')->nullable()->after('target_customer');
            $table->string('pricing_model', 120)->nullable()->after('key_features');
            $table->string('markets_served', 500)->nullable()->after('pricing_model');
            $table->text('traction')->nullable()->after('markets_served');
            $table->text('founder_story')->nullable()->after('traction');
            $table->timestamp('editorial_reviewed_at')->nullable()->after('founder_story');
            $table->unsignedTinyInteger('content_quality_version')->default(0)->after('editorial_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('startups', function (Blueprint $table) {
            $table->dropColumn([
                'problem_solved',
                'target_customer',
                'key_features',
                'pricing_model',
                'markets_served',
                'traction',
                'founder_story',
                'editorial_reviewed_at',
                'content_quality_version',
            ]);
        });
    }
};
