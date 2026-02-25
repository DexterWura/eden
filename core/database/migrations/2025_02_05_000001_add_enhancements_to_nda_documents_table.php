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
        // Check which columns exist before modifying
        $hasDocumentPath = Schema::hasColumn('nda_documents', 'document_path');
        $hasSignature = Schema::hasColumn('nda_documents', 'signature');
        $hasUserAgent = Schema::hasColumn('nda_documents', 'user_agent');
        $hasExpiresAt = Schema::hasColumn('nda_documents', 'expires_at');

        // Ensure document_path exists if it doesn't
        if (!$hasDocumentPath) {
            Schema::table('nda_documents', function (Blueprint $table) {
                $table->string('document_path', 500)->nullable()->comment('Path to uploaded NDA document');
            });
        }

        Schema::table('nda_documents', function (Blueprint $table) use ($hasSignature, $hasUserAgent, $hasExpiresAt) {
            // Add signature_image after signature if it exists
            if ($hasSignature) {
                $table->text('signature_image')->nullable()->after('signature')->comment('Base64 or path to signature image');
            } else {
                $table->text('signature_image')->nullable()->comment('Base64 or path to signature image');
            }
            
            // document_hash after document_path (now guaranteed to exist)
            $table->string('document_hash', 64)->nullable()->after('document_path')->comment('SHA-256 hash of document');
            
            // Add columns after user_agent if it exists
            if ($hasUserAgent) {
                $table->integer('read_time_seconds')->nullable()->after('user_agent')->comment('Time spent reading terms');
                $table->string('browser_fingerprint', 64)->nullable()->after('user_agent');
            } else {
                $table->integer('read_time_seconds')->nullable()->comment('Time spent reading terms');
                $table->string('browser_fingerprint', 64)->nullable();
            }
            
            $table->string('device_type', 20)->nullable()->after('browser_fingerprint')->comment('mobile/desktop/tablet');
            $table->string('screen_resolution', 20)->nullable()->after('device_type');
            $table->string('timezone', 50)->nullable()->after('screen_resolution');
            $table->string('referrer_url', 500)->nullable()->after('timezone');
            
            // Add revoked columns after expires_at if it exists
            if ($hasExpiresAt) {
                $table->timestamp('revoked_at')->nullable()->after('expires_at');
            } else {
                $table->timestamp('revoked_at')->nullable();
            }
            
            $table->unsignedBigInteger('revoked_by')->nullable()->after('revoked_at')->comment('User who revoked');
            $table->text('revocation_reason')->nullable()->after('revoked_by');
            $table->string('governing_law', 100)->nullable()->after('revocation_reason');
            $table->text('custom_terms')->nullable()->after('governing_law');
            $table->string('template_version', 50)->nullable()->after('custom_terms')->default('1.0');
        });

        // Add indexes separately
        Schema::table('nda_documents', function (Blueprint $table) {
            if (Schema::hasColumn('nda_documents', 'revoked_at')) {
                $table->index('revoked_at');
            }
            if (Schema::hasColumn('nda_documents', 'document_hash')) {
                $table->index('document_hash');
            }
        });

        // Add foreign key separately
        Schema::table('nda_documents', function (Blueprint $table) {
            if (Schema::hasColumn('nda_documents', 'revoked_by')) {
                // Check if foreign key already exists
                try {
                    $table->foreign('revoked_by')->references('id')->on('users')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist, ignore
                }
            }
        });

        Schema::create('nda_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nda_document_id');
            $table->string('action', 50)->comment('signed, viewed, downloaded, revoked, expired, etc.');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser_fingerprint', 64)->nullable();
            $table->json('device_info')->nullable()->comment('Device type, screen resolution, etc.');
            $table->json('metadata')->nullable()->comment('Additional action-specific data');
            $table->timestamps();
            
            $table->index('nda_document_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            
            $table->foreign('nda_document_id')->references('id')->on('nda_documents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nda_audit_logs');
        
        Schema::table('nda_documents', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('nda_documents', 'revoked_by')) {
                try {
                    $table->dropForeign(['revoked_by']);
                } catch (\Exception $e) {
                    // Ignore if doesn't exist
                }
            }
            
            // Drop indexes
            if (Schema::hasColumn('nda_documents', 'revoked_at')) {
                try {
                    $table->dropIndex(['revoked_at']);
                } catch (\Exception $e) {
                    // Ignore if doesn't exist
                }
            }
            if (Schema::hasColumn('nda_documents', 'document_hash')) {
                try {
                    $table->dropIndex(['document_hash']);
                } catch (\Exception $e) {
                    // Ignore if doesn't exist
                }
            }
            
            // Drop columns
            $columnsToDrop = [
                'signature_image',
                'document_hash',
                'read_time_seconds',
                'revoked_at',
                'revoked_by',
                'revocation_reason',
                'governing_law',
                'custom_terms',
                'template_version',
                'browser_fingerprint',
                'device_type',
                'screen_resolution',
                'timezone',
                'referrer_url'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('nda_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
