<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('platform', ['instagram', 'youtube', 'tiktok', 'twitter', 'facebook']);
            $table->string('account_id')->nullable()->comment('Platform account ID from OAuth');
            $table->string('account_username')->nullable()->comment('Platform account username');
            $table->tinyInteger('status')->unsigned()->default(0)->comment('0:pending, 1:verified, 2:failed');
            $table->timestamp('verified_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['listing_id', 'platform']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_verifications');
    }
};

