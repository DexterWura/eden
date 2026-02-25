<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a test admin user for demo purposes.
     */
    public function up(): void
    {
        // Check if admins table exists
        if (!Schema::hasTable('admins')) {
            return; // Admins table doesn't exist yet, skip
        }

        // Check if test admin already exists
        $testAdminExists = DB::table('admins')
            ->where('username', 'demoadmin')
            ->exists();

        if (!$testAdminExists) {
            // Create test admin user
            DB::table('admins')->insert([
                'name' => 'Demo Admin',
                'email' => 'demoadmin@demo.local',
                'username' => 'demoadmin',
                'password' => Hash::make('demoadmin123'),
                'is_super_admin' => true,
                'status' => 1,
                'allowed_modules' => null, // null means all modules for super admin
                'image' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admins')) {
            // Remove test admin user
            DB::table('admins')
                ->where('username', 'demoadmin')
                ->delete();
        }
    }
};
