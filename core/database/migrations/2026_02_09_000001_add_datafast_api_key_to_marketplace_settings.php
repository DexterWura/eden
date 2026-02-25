<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('marketplace_settings')->updateOrInsert(
            ['key' => 'datafast_api_key'],
            [
                'value' => 'df_6080d6bb349a8f3c43150ae39c0ab36f66cfaeb7326e29d2',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('marketplace_settings')->where('key', 'datafast_api_key')->delete();
    }
};
