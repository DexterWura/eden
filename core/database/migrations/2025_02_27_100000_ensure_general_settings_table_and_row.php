<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('general_settings')) {
            Schema::create('general_settings', function (Blueprint $table) {
                $table->id();
                $table->string('site_name', 100)->nullable()->default('Eden');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('general_settings') && DB::table('general_settings')->count() === 0) {
            DB::table('general_settings')->insert([
                'site_name' => 'Eden',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Do not drop the table in down() - other migrations only add columns.
        // If you need to roll back the row, you could delete it here.
    }
};
