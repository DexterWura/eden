<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domain_verifications', function (Blueprint $table) {
            // Add new fields from technical plan
            $table->jsonb('verification_data')->nullable()->after('verification_token');
            $table->integer('attempt_count')->default(0)->after('verification_data');
            $table->timestamp('last_attempt_at')->nullable()->after('attempt_count');
            $table->timestamp('verified_at')->nullable()->after('last_attempt_at');

            // Add indexes as per technical plan
            $table->index(['status'], 'idx_domain_verifications_status');
            $table->index(['user_id'], 'idx_domain_verifications_user');
            $table->index(['listing_id'], 'idx_domain_verifications_listing');
            $table->index(['expires_at'], 'idx_domain_verifications_expires');

            // Drop existing unique constraint if it exists and create new one
            try {
                $table->dropUnique('domain_verifications_listing_id_unique');
            } catch (\Exception $e) {
                // Constraint might not exist, continue
            }

            $table->unique(['domain', 'user_id', 'listing_id'], 'unique_domain_user_listing');
        });

        // Data migration: Convert status values from integers to strings
        DB::statement("UPDATE domain_verifications SET status = CASE
            WHEN status = '0' THEN 'pending'
            WHEN status = '1' THEN 'verified'
            WHEN status = '2' THEN 'failed'
            ELSE 'pending'
        END");

        // Data migration: Convert verification_method values
        DB::statement("UPDATE domain_verifications SET verification_method = CASE
            WHEN verification_method = 'txt_file' THEN 'file'
            WHEN verification_method = 'dns_record' THEN 'dns'
            ELSE verification_method
        END");

        // Data migration: Copy attempts to attempt_count
        DB::statement("UPDATE domain_verifications SET attempt_count = COALESCE(attempts, 0) WHERE attempt_count = 0");

        // Now change the status column type (this needs to be done after data migration)
        Schema::table('domain_verifications', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('domain_verifications', function (Blueprint $table) {
            $table->dropIndex('idx_domain_verifications_status');
            $table->dropIndex('idx_domain_verifications_user');
            $table->dropIndex('idx_domain_verifications_listing');
            $table->dropIndex('idx_domain_verifications_expires');
            $table->dropUnique('unique_domain_user_listing');

            // Drop new columns
            $table->dropColumn([
                'verification_data',
                'attempt_count',
                'last_attempt_at',
                'verified_at'
            ]);

            // Restore original unique constraint
            $table->unique('listing_id');
        });
    }
};
