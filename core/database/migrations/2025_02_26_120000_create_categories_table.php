<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('slug', 64)->unique();
            $table->string('icon', 64)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['Artificial Intelligence', 'artificial-intelligence', 'fa-solid fa-robot', 1],
            ['SaaS', 'saas', 'fa-solid fa-cloud', 2],
            ['Developer Tools', 'developer-tools', 'fa-solid fa-code', 3],
            ['Fintech', 'fintech', 'fa-solid fa-coins', 4],
            ['Productivity', 'productivity', 'fa-solid fa-check-double', 5],
            ['Marketing', 'marketing', 'fa-solid fa-bullhorn', 6],
            ['E-commerce', 'e-commerce', 'fa-solid fa-cart-shopping', 7],
            ['Design Tools', 'design-tools', 'fa-solid fa-palette', 8],
            ['No-Code', 'no-code', 'fa-solid fa-layer-group', 9],
            ['Analytics', 'analytics', 'fa-solid fa-chart-line', 10],
            ['Education', 'education', 'fa-solid fa-graduation-cap', 11],
            ['Health & Fitness', 'health-fitness', 'fa-solid fa-heart-pulse', 12],
            ['Social Media', 'social-media', 'fa-solid fa-share-nodes', 13],
            ['Content Creation', 'content-creation', 'fa-solid fa-video', 14],
            ['Sales', 'sales', 'fa-solid fa-arrow-trend-up', 15],
            ['Customer Support', 'customer-support', 'fa-solid fa-headset', 16],
            ['Recruiting & HR', 'recruiting-hr', 'fa-solid fa-people-group', 17],
            ['Real Estate', 'real-estate', 'fa-solid fa-house', 18],
            ['Travel', 'travel', 'fa-solid fa-plane', 19],
            ['Security', 'security', 'fa-solid fa-shield-halved', 20],
        ];
        foreach ($rows as $i => $row) {
            \DB::table('categories')->insert([
                'name' => $row[0],
                'slug' => $row[1],
                'icon' => $row[2],
                'sort_order' => $row[3],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
