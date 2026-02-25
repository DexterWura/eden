<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Alters existing admins table (created at install) and creates admin_audit_log.
     */
    public function up(): void
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (!Schema::hasColumn('admins', 'is_super_admin')) {
                    $table->boolean('is_super_admin')->default(true)->after('image');
                }
                if (!Schema::hasColumn('admins', 'allowed_modules')) {
                    $table->json('allowed_modules')->nullable()->after('is_super_admin');
                }
                if (!Schema::hasColumn('admins', 'status')) {
                    $table->unsignedTinyInteger('status')->default(1)->after('allowed_modules'); // 1 = enabled, 0 = disabled
                }
            });

            // Ensure at least one admin remains super admin (e.g. if column default was overridden)
            if (DB::table('admins')->where('is_super_admin', 1)->count() === 0) {
                DB::table('admins')->orderBy('id')->limit(1)->update(['is_super_admin' => 1]);
            }
        }

        if (Schema::hasTable('admins') && !Schema::hasTable('admin_audit_log')) {
            Schema::create('admin_audit_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->string('action', 100);
                $table->string('subject_type', 255)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('route_name', 255)->nullable();
                $table->string('request_method', 10)->nullable();
                $table->timestamps();
            });

            Schema::table('admin_audit_log', function (Blueprint $table) {
                $table->index('admin_id');
                $table->index('created_at');
                $table->index('action');
                $table->index(['subject_type', 'subject_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_audit_log');

        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn(['is_super_admin', 'allowed_modules', 'status']);
            });
        }
    }
};
