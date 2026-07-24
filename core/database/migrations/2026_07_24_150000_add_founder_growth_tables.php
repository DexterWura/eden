<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cofounder_invitations')) {
            Schema::create('cofounder_invitations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('startup_id')->constrained()->cascadeOnDelete();
                $table->foreignId('invited_by_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('email');
                $table->string('token_hash', 64)->unique();
                $table->string('pending_key', 64)->nullable()->unique();
                $table->timestamp('expires_at');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();
                $table->index(['startup_id', 'email', 'accepted_at'], 'cofounder_invitation_lookup');
            });
        }

        if (! Schema::hasTable('investor_leads')) {
            Schema::create('investor_leads', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('startup_funding_round_id')->constrained()->cascadeOnDelete();
                $table->string('name', 120);
                $table->string('email');
                $table->string('organization', 160)->nullable();
                $table->text('message')->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('new');
                $table->timestamp('contacted_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->string('ip_hash', 64)->nullable();
                $table->timestamps();
                $table->index(['startup_funding_round_id', 'status'], 'investor_lead_inbox');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_leads');
        Schema::dropIfExists('cofounder_invitations');
    }
};
