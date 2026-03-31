<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_alert_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('search_query', 500)->nullable();
            $table->string('category', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('criteria_hash', 64);
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['email', 'criteria_hash']);
            $table->index('last_notified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_alert_subscriptions');
    }
};
