<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (! Schema::hasColumn('admins', 'two_factor_secret')) {
                    $table->text('two_factor_secret')->nullable();
                }
                if (! Schema::hasColumn('admins', 'two_factor_recovery_codes')) {
                    $table->text('two_factor_recovery_codes')->nullable();
                }
                if (! Schema::hasColumn('admins', 'two_factor_confirmed_at')) {
                    $table->timestamp('two_factor_confirmed_at')->nullable();
                }
            });
        }

        if (! Schema::hasTable('admin_audit_log')) {
            Schema::create('admin_audit_log', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $table->string('action', 100)->index();
                $table->nullableMorphs('subject');
                $table->text('description')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('route_name')->nullable();
                $table->string('request_method', 10)->nullable();
                $table->timestamps();
                $table->index(['admin_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('admin_operation_notifications')) {
            Schema::create('admin_operation_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->nullable()->constrained('admins')->cascadeOnDelete();
                $table->string('type', 60)->default('operations');
                $table->string('title');
                $table->text('message');
                $table->string('action_url')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['admin_id', 'read_at', 'created_at'], 'admin_operations_inbox');
            });
        }

        if (Schema::hasTable('ad_spots')) {
            Schema::table('ad_spots', function (Blueprint $table) {
                if (! Schema::hasColumn('ad_spots', 'amount')) {
                    $table->decimal('amount', 10, 2)->nullable();
                }
                if (! Schema::hasColumn('ad_spots', 'currency')) {
                    $table->string('currency', 8)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_operation_notifications');
        Schema::dropIfExists('admin_audit_log');

        if (Schema::hasTable('ad_spots')) {
            $adColumns = array_values(array_filter(['amount', 'currency'], fn (string $column): bool => Schema::hasColumn('ad_spots', $column)));
            if ($adColumns !== []) {
                Schema::table('ad_spots', fn (Blueprint $table) => $table->dropColumn($adColumns));
            }
        }

        if (Schema::hasTable('admins')) {
            $columns = array_values(array_filter([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ], fn (string $column): bool => Schema::hasColumn('admins', $column)));

            if ($columns !== []) {
                Schema::table('admins', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }
    }
};
