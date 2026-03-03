<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_pro')->default(false)->after('remember_token');
            $table->timestamp('pro_since')->nullable()->after('is_pro');
        });

        Schema::create('pro_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 32);
            $table->string('trx', 64)->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 8)->default('USD');
            $table->string('status', 20)->default('pending');
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('alias', 32)->unique();
            $table->boolean('enabled')->default(false);
            $table->json('parameters')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pro_payments');
        Schema::dropIfExists('payment_gateways');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_pro', 'pro_since']);
        });
    }
};
