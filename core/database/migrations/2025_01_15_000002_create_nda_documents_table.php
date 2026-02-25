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
        Schema::create('nda_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('user_id')->comment('User who signed the NDA');
            $table->string('document_path', 500)->comment('Path to uploaded NDA document');
            $table->string('signature', 255)->nullable()->comment('Digital signature or name');
            $table->timestamp('signed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('status', ['pending', 'signed', 'expired', 'revoked'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('listing_id');
            $table->index('user_id');
            $table->index(['listing_id', 'user_id']);
            $table->index('status');
            
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nda_documents');
    }
};

