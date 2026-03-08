<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->unsignedTinyInteger('email_status')->default(1)->after('shortcodes');
            $table->longText('email_body')->nullable()->after('email_status');
        });

        $this->backfillExistingTemplates();

        if (!$this->defaultTemplateExists()) {
            DB::table('notification_templates')->insert([
                'act' => 'DEFAULT',
                'name' => 'Default (contact reply, etc.)',
                'subject' => '{{subject}}',
                'body' => null,
                'email_body' => '{{message}}',
                'email_status' => 1,
                'shortcodes' => json_encode(['subject', 'message', 'fullname', 'username']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn(['email_status', 'email_body']);
        });
    }

    private function backfillExistingTemplates(): void
    {
        foreach (DB::table('notification_templates')->get() as $row) {
            $updates = ['email_status' => 1];
            if (Schema::hasColumn('notification_templates', 'body') && $row->body !== null) {
                $updates['email_body'] = $row->body;
            }
            DB::table('notification_templates')->where('id', $row->id)->update($updates);
        }
    }

    private function defaultTemplateExists(): bool
    {
        return DB::table('notification_templates')->where('act', 'DEFAULT')->exists();
    }
};
