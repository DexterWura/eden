<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->text('introduction')->nullable()->after('icon');
            $table->text('market_context')->nullable()->after('introduction');
            $table->json('frequently_asked_questions')->nullable()->after('market_context');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['introduction', 'market_context', 'frequently_asked_questions']);
        });
    }
};
