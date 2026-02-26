<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contact_submissions')) {
            return;
        }

        Schema::table('contact_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_submissions', 'reply_subject')) {
                $table->string('reply_subject', 255)->nullable()->after('message');
            }
            if (! Schema::hasColumn('contact_submissions', 'reply_body')) {
                $table->text('reply_body')->nullable()->after('reply_subject');
            }
            if (! Schema::hasColumn('contact_submissions', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('reply_body');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contact_submissions')) {
            return;
        }

        Schema::table('contact_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('contact_submissions', 'replied_at')) {
                $table->dropColumn('replied_at');
            }
            if (Schema::hasColumn('contact_submissions', 'reply_body')) {
                $table->dropColumn('reply_body');
            }
            if (Schema::hasColumn('contact_submissions', 'reply_subject')) {
                $table->dropColumn('reply_subject');
            }
        });
    }
};

