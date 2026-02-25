<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename module key "extra" to "system_tools" in admins.allowed_modules
     * so staff with "extra" keep access to System & Server (Application, Server, Cache, Update).
     */
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('admins') || !\Illuminate\Support\Facades\Schema::hasColumn('admins', 'allowed_modules')) {
            return;
        }

        $admins = DB::table('admins')->whereNotNull('allowed_modules')->get(['id', 'allowed_modules']);
        foreach ($admins as $admin) {
            $modules = json_decode($admin->allowed_modules, true);
            if (!is_array($modules)) {
                continue;
            }
            $updated = false;
            foreach ($modules as $i => $key) {
                if ($key === 'extra') {
                    $modules[$i] = 'system_tools';
                    $updated = true;
                }
            }
            if ($updated) {
                DB::table('admins')->where('id', $admin->id)->update([
                    'allowed_modules' => json_encode(array_values($modules)),
                ]);
            }
        }
    }

    /**
     * Reverse: rename "system_tools" back to "extra" in allowed_modules.
     */
    public function down(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('admins') || !\Illuminate\Support\Facades\Schema::hasColumn('admins', 'allowed_modules')) {
            return;
        }

        $admins = DB::table('admins')->whereNotNull('allowed_modules')->get(['id', 'allowed_modules']);
        foreach ($admins as $admin) {
            $modules = json_decode($admin->allowed_modules, true);
            if (!is_array($modules)) {
                continue;
            }
            $updated = false;
            foreach ($modules as $i => $key) {
                if ($key === 'system_tools') {
                    $modules[$i] = 'extra';
                    $updated = true;
                }
            }
            if ($updated) {
                DB::table('admins')->where('id', $admin->id)->update([
                    'allowed_modules' => json_encode(array_values($modules)),
                ]);
            }
        }
    }
};
