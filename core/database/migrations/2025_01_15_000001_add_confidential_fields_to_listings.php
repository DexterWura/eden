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
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('is_confidential')->default(false)->after('is_verified');
            $table->boolean('requires_nda')->default(false)->after('is_confidential');
            $table->text('confidential_reason')->nullable()->after('requires_nda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['is_confidential', 'requires_nda', 'confidential_reason']);
        });
    }
};

