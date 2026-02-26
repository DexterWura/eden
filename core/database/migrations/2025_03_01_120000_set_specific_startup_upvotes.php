<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('startups')) {
            return;
        }

        $map = [
            'FLIPit' => 10,
            'ZImAdsense' => 4,
            'dextersoft' => 0,
            'Linkgenie' => 6,
        ];

        foreach ($map as $name => $upvotes) {
            DB::table('startups')
                ->where('name', $name)
                ->update(['upvotes' => $upvotes]);
        }
    }

    public function down(): void
    {
        // No-op: we don't know previous upvote values to restore.
    }
};

