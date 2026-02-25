<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if column exists before proceeding
        if (!Schema::hasColumn('listings', 'category_id')) {
            return; // Column doesn't exist, nothing to do
        }

        // Drop index if it exists (try different possible index names)
        $indexes = [
            'listings_category_id_index',  // Laravel auto-generated name
            'listings_category_id_idx',      // Alternative naming
            'category_id',                   // Simple name
        ];

        foreach ($indexes as $indexName) {
            try {
                DB::statement("ALTER TABLE `listings` DROP INDEX `{$indexName}`");
                break; // Successfully dropped, exit loop
            } catch (\Exception $e) {
                // Index doesn't exist with this name, try next
                continue;
            }
        }

        // Drop the column
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // Re-add the column if rolling back
            $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
            $table->index('category_id');
        });
    }
};
