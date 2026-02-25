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
        Schema::create('listing_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('user_id');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            
            // Status: 0=pending, 1=answered, 2=hidden
            $table->tinyInteger('status')->default(0);
            
            $table->timestamps();
            
            $table->index('listing_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_questions');
    }
};

