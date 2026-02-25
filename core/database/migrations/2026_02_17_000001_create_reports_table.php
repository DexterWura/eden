<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reportable_type'); // App\Models\Listing or App\Models\User
            $table->unsignedBigInteger('reportable_id');
            $table->string('reason', 50); // spam, misleading, scam, inappropriate, other
            $table->text('details')->nullable();
            $table->tinyInteger('status')->default(0); // 0=pending, 1=reviewed
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();
            $table->index(['reportable_type', 'reportable_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
