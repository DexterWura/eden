<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_operation_notification_reads')) {
            return;
        }

        Schema::create('admin_operation_notification_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_operation_notification_id')
                ->constrained('admin_operation_notifications')
                ->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->timestamps();
            $table->unique(
                ['admin_operation_notification_id', 'admin_id'],
                'admin_notification_read_delivery'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_operation_notification_reads');
    }
};
