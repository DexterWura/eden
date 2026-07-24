<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('startup_comments')) {
            return;
        }

        Schema::table('startup_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('startup_comments', 'founder_reply')) {
                $table->text('founder_reply')->nullable()->after('body');
            }
            if (! Schema::hasColumn('startup_comments', 'founder_replied_by')) {
                $table->foreignId('founder_replied_by')->nullable()->after('founder_reply')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('startup_comments', 'founder_replied_at')) {
                $table->timestamp('founder_replied_at')->nullable()->after('founder_replied_by');
            }
            if (! Schema::hasColumn('startup_comments', 'addressed_at')) {
                $table->timestamp('addressed_at')->nullable()->after('founder_replied_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('startup_comments')) {
            return;
        }

        Schema::table('startup_comments', function (Blueprint $table) {
            if (Schema::hasColumn('startup_comments', 'founder_replied_by')) {
                $table->dropConstrainedForeignId('founder_replied_by');
            }
            $columns = array_values(array_filter(
                ['founder_reply', 'founder_replied_at', 'addressed_at'],
                fn (string $column): bool => Schema::hasColumn('startup_comments', $column)
            ));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
