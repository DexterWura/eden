<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique();
            $table->string('display_name', 128);
            $table->text('description')->nullable();
            $table->unsignedInteger('interval_minutes')->default(1440);
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_status', 16)->nullable();
            $table->text('last_message')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('scheduled_tasks')->insert([
            'name' => 'sitemap',
            'display_name' => 'Sitemap',
            'description' => 'Generates and updates sitemap.xml for the whole site (home, pages, startups, categories).',
            'interval_minutes' => 1440,
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
