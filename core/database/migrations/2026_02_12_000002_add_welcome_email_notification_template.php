<?php

use App\Constants\Status;
use App\Models\NotificationTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            return;
        }

        $act = 'WELCOME_EMAIL';
        if (NotificationTemplate::where('act', $act)->exists()) {
            return;
        }

        $nt = new NotificationTemplate();
        $nt->act = $act;
        $nt->name = 'Welcome Email';
        $nt->subject = 'Welcome to {{site_name}}';
        $nt->email_body = "Hi {{fullname}},\n\nWelcome to {{site_name}}! Your account has been created successfully.\n\nUsername: {{username}}\n\nWe're glad to have you on board. You can now sign in and explore the platform.\n\nBest regards,\n{{site_name}} Team";
        $nt->sms_body = 'Welcome to {{site_name}}, {{fullname}}! Your account ({{username}}) is ready.';
        $nt->push_title = 'Welcome to {{site_name}}';
        $nt->push_body = 'Hi {{fullname}}, your account is ready. Sign in with username {{username}}.';
        $nt->shortcodes = [
            'fullname' => 'User full name',
            'username' => 'Username',
            'site_name' => 'Site name',
        ];
        $nt->email_status = Status::ENABLE;
        $nt->sms_status = Status::DISABLE;
        $nt->push_status = Status::DISABLE;
        $nt->save();
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            return;
        }

        NotificationTemplate::where('act', 'WELCOME_EMAIL')->delete();
    }
};
