<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates table for storing license verification data.
     */
    public function up(): void
    {
        Schema::create('license_verification', function (Blueprint $table) {
            $table->id();
            $table->text('encrypted_data')->comment('Encrypted license data (purchase code, username, etc.)');
            $table->timestamp('verified_at')->nullable()->comment('When license was first verified');
            $table->timestamp('last_check_at')->nullable()->comment('Last time license was checked');
            $table->boolean('is_valid')->default(true)->comment('Whether license is currently valid');
            $table->text('verification_response')->nullable()->comment('Last API response from Envato');
            $table->timestamps();
            
            $table->index('is_valid');
            $table->index('last_check_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_verification');
    }
};
