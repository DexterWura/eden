<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('author_type', 32)->nullable()->after('author_id');
            $table->json('source_urls')->nullable()->after('body');
            $table->timestamp('editorial_reviewed_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['author_type', 'source_urls', 'editorial_reviewed_at']);
        });
    }
};
