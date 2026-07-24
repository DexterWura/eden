<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cofounder_invitations')) {
            return;
        }

        Schema::table('cofounder_invitations', function (Blueprint $table): void {
            if (! Schema::hasColumn('cofounder_invitations', 'delivery_token')) {
                $table->text('delivery_token')->nullable()->after('token_hash');
            }
            if (! Schema::hasColumn('cofounder_invitations', 'email_sent_at')) {
                $table->timestamp('email_sent_at')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('cofounder_invitations')) {
            return;
        }

        $columns = array_values(array_filter(
            ['delivery_token', 'email_sent_at'],
            fn (string $column): bool => Schema::hasColumn('cofounder_invitations', $column)
        ));
        if ($columns !== []) {
            Schema::table('cofounder_invitations', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
