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
        Schema::table('milestones', function (Blueprint $table) {
            $table->enum('requested_by', ['seller', 'buyer'])->default('seller')->after('user_id')->comment('Who requested this milestone');
            $table->boolean('approved_by_seller')->default(false)->after('requested_by');
            $table->boolean('approved_by_buyer')->default(false)->after('approved_by_seller');
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'modified'])->default('pending')->after('approved_by_buyer');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('rejection_reason');
            $table->string('milestone_type', 50)->nullable()->after('rejected_by')->comment('Template type: domain_transfer, account_access, etc.');
            $table->integer('sort_order')->default(0)->after('milestone_type')->comment('Order of milestone execution');
            $table->timestamp('approved_at')->nullable()->after('sort_order');
            $table->timestamp('completed_at')->nullable()->after('approved_at');
            
            $table->index('approval_status');
            $table->index('requested_by');
            $table->index(['escrow_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropColumn([
                'requested_by',
                'approved_by_seller',
                'approved_by_buyer',
                'approval_status',
                'rejection_reason',
                'rejected_by',
                'milestone_type',
                'sort_order',
                'approved_at',
                'completed_at'
            ]);
        });
    }
};

