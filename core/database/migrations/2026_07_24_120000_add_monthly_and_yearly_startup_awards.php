<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_of_month_winners', function (Blueprint $table) {
            $table->id();
            $table->date('award_month')->unique();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('upvote_count');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_of_year_winners', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('award_year')->unique();
            $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('upvote_count');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });

        Schema::table('product_of_day_winners', function (Blueprint $table) {
            $table->timestamp('notified_at')->nullable();
        });
        Schema::table('startups', function (Blueprint $table) {
            $table->date('product_of_month_at')->nullable()->after('product_of_day_at');
            $table->unsignedSmallInteger('product_of_year_at')->nullable()->after('product_of_month_at');
        });

        Schema::create('startup_award_notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('award_type', 10);
            $table->unsignedBigInteger('award_winner_id');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('dashboard_created_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['award_type', 'award_winner_id', 'recipient_email'],
                'startup_award_delivery_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_award_notification_deliveries');
        Schema::table('startups', function (Blueprint $table) {
            $table->dropColumn(['product_of_month_at', 'product_of_year_at']);
        });
        Schema::table('product_of_day_winners', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
        Schema::dropIfExists('product_of_year_winners');
        Schema::dropIfExists('product_of_month_winners');
    }
};
