<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launch_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['email', 'startup_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launch_notifications');
    }
};
