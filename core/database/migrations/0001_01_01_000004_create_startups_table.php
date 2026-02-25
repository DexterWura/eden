<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('category', 64)->nullable();
            $table->string('website', 500)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('founder_name', 255)->nullable();
            $table->date('launch_date')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('upvotes')->default(0);
            $table->string('twitter_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->timestamps();
        });

        $now = now();
        $sample = [
            [
                'name' => 'Nexus Pay',
                'slug' => 'nexus-pay',
                'tagline' => 'Instant cross-border payments and treasury for African businesses.',
                'description' => 'Nexus Pay helps African businesses send and receive payments globally with low fees and fast settlement.',
                'category' => 'Fintech',
                'website' => 'https://nexuspay.example',
                'location' => 'Harare',
                'founder_name' => 'Sarah Chen',
                'launch_date' => now()->subMonths(6),
                'is_featured' => true,
                'upvotes' => 127,
                'twitter_url' => '#',
                'linkedin_url' => '#',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'VitaFlow',
                'slug' => 'vitaflow',
                'tagline' => 'Telehealth and prescription delivery across Zimbabwe.',
                'description' => 'VitaFlow connects patients with doctors and delivers prescriptions to your door.',
                'category' => 'Health',
                'website' => 'https://vitaflow.example',
                'location' => 'Bulawayo',
                'founder_name' => 'James Moyo',
                'launch_date' => now()->subMonths(4),
                'is_featured' => false,
                'upvotes' => 98,
                'twitter_url' => '#',
                'linkedin_url' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'QuickPay',
                'slug' => 'quickpay',
                'tagline' => 'One-tap payments for merchants. No hardware, no monthly fees.',
                'description' => 'QuickPay enables merchants to accept payments via mobile with no terminal or subscription.',
                'category' => 'Fintech',
                'website' => 'https://quickpay.example',
                'location' => 'Harare',
                'founder_name' => 'Tendai Banda',
                'launch_date' => now(),
                'is_featured' => true,
                'upvotes' => 76,
                'twitter_url' => '#',
                'linkedin_url' => '#',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($sample as $row) {
            Schema::getConnection()->table('startups')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('startups');
    }
};
