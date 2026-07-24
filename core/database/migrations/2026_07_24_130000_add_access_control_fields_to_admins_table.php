<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admins')) {
            return;
        }

        if (! Schema::hasColumn('admins', 'is_super_admin')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('password');
            });
        }

        if (! Schema::hasColumn('admins', 'allowed_modules')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->json('allowed_modules')->nullable()->after('is_super_admin');
            });
        }

        if (! Schema::hasColumn('admins', 'status')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->unsignedTinyInteger('status')->default(1)->after('allowed_modules');
            });
        }

        $hasSuperAdmin = DB::table('admins')->where('is_super_admin', true)->exists();
        if (! $hasSuperAdmin) {
            $primaryAdminId = DB::table('admins')
                ->where('username', '!=', 'demoadmin')
                ->orderBy('id')
                ->value('id');

            if ($primaryAdminId !== null) {
                DB::table('admins')
                    ->where('id', $primaryAdminId)
                    ->update(['is_super_admin' => true, 'status' => 1]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('admins')) {
            return;
        }

        $columns = array_values(array_filter(
            ['is_super_admin', 'allowed_modules', 'status'],
            fn (string $column): bool => Schema::hasColumn('admins', $column)
        ));

        if ($columns !== []) {
            Schema::table('admins', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
