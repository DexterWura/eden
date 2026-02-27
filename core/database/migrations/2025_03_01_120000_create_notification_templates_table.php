<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('act')->unique(); // Action code: WELCOME_EMAIL, EVER_CODE, etc.
            $table->string('name')->nullable(); // Template name
            $table->string('subject')->nullable(); // Email subject
            $table->longText('body')->nullable(); // Email body
            $table->json('shortcodes')->nullable(); // Available shortcodes for this template
            $table->timestamps();
        });

        // Insert default templates
        DB::table('notification_templates')->insert([
            [
                'act' => 'WELCOME_EMAIL',
                'name' => 'Welcome Email',
                'subject' => 'Welcome to Eden',
                'body' => 'Welcome to Eden! We\'re excited to have you on board.',
                'shortcodes' => json_encode(['username', 'email']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'act' => 'EVER_CODE',
                'name' => 'Email Verification Code',
                'subject' => 'Verify Your Email',
                'body' => 'Your email verification code is: {{code}}',
                'shortcodes' => json_encode(['code', 'username']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
