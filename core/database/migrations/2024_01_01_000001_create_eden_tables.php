<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon_path')->nullable();
            $table->timestamps();
        });

        Schema::create('startups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('founder')->nullable();
            $table->decimal('mrr', 14, 2)->nullable();
            $table->decimal('arr', 14, 2)->nullable();
            $table->boolean('is_for_sale')->default(false);
            $table->string('status', 20)->default('seedling');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->unsignedSmallInteger('url_failure_count')->default(0);
            $table->timestamp('last_url_failure_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('category_id');
            $table->index('approved_at');
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('verification_token', 64);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index('startup_id');
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['startup_id', 'user_id']);
            $table->index('startup_id');
        });

        Schema::create('growth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->integer('points_added')->default(0);
            $table->timestamps();
        });

        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('slot', 30);
            $table->string('type', 30);
            $table->string('name')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->text('content')->nullable();
            $table->string('adsense_client')->nullable();
            $table->string('adsense_slot')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('ads');
        Schema::dropIfExists('growth_logs');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('startups');
        Schema::dropIfExists('categories');
    }
};
