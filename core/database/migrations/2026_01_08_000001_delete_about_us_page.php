<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pages')) {
            return;
        }

        DB::table('pages')->where('slug', 'about-us')->delete();
    }

    public function down(): void
    {
        // no-op: content pages are admin-managed and can be recreated at any time
    }
};


