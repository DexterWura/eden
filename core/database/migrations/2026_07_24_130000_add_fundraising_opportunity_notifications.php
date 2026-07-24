<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('startup_funding_rounds', function (Blueprint $table) {
            $table->timestamp('opportunity_announced_at')->nullable()->after('status');
        });

        Schema::create('fundraising_opportunity_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('startup_funding_round_id')
                ->constrained('startup_funding_rounds')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['startup_funding_round_id', 'user_id'],
                'fundraising_delivery_round_user_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fundraising_opportunity_deliveries');
        Schema::table('startup_funding_rounds', function (Blueprint $table) {
            $table->dropColumn('opportunity_announced_at');
        });
    }
};
