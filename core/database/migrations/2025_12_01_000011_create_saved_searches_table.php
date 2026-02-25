<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 100);
            $table->text('filters')->comment('JSON encoded search filters');
            $table->boolean('email_alerts')->default(true);
            $table->enum('alert_frequency', ['instant', 'daily', 'weekly'])->default('daily');
            $table->timestamp('last_alerted_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};

