<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add reaction count columns & last editor to news table
        Schema::table('news', function (Blueprint $table) {
            if (!Schema::hasColumn('news', 'beneficial_count')) {
                $table->unsignedBigInteger('beneficial_count')->default(0)->after('robot_hits');
            }
            if (!Schema::hasColumn('news', 'informative_count')) {
                $table->unsignedBigInteger('informative_count')->default(0)->after('beneficial_count');
            }
            if (!Schema::hasColumn('news', 'last_updated_user_id')) {
                $table->foreignId('last_updated_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Create article_reactions table
        if (!Schema::hasTable('article_reactions')) {
            Schema::create('article_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
                $table->string('visitor_uuid', 64)->index();
                $table->string('session_id', 128)->nullable();
                $table->enum('reaction_type', ['beneficial', 'informative'])->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->unique(['news_id', 'visitor_uuid'], 'idx_article_visitor_unique_reaction');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('article_reactions');

        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'beneficial_count')) {
                $table->dropColumn('beneficial_count');
            }
            if (Schema::hasColumn('news', 'informative_count')) {
                $table->dropColumn('informative_count');
            }
            if (Schema::hasColumn('news', 'last_updated_user_id')) {
                $table->dropForeign(['last_updated_user_id']);
                $table->dropColumn('last_updated_user_id');
            }
        });
    }
};
