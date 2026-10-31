<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create article_reader_logs table for individual unique reader logs
        if (!Schema::hasTable('article_reader_logs')) {
            Schema::create('article_reader_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
                $table->string('visitor_uuid', 64)->index();
                $table->string('session_id', 100)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->boolean('is_robot')->default(false);
                $table->timestamp('first_read_at')->useCurrent();
                $table->timestamp('last_read_at')->useCurrent();
                $table->unsignedInteger('read_count')->default(1);
                $table->timestamps();

                // Unique composite index to prevent duplicate records per visitor per article
                $table->unique(['news_id', 'visitor_uuid'], 'uniq_news_visitor');
                $table->index(['news_id', 'is_robot'], 'idx_news_robot');
                $table->index('first_read_at', 'idx_first_read_at');
            });
        }

        // 2. Add granular metrics columns to news table if missing
        Schema::table('news', function (Blueprint $table) {
            if (!Schema::hasColumn('news', 'page_views')) {
                $table->unsignedBigInteger('page_views')->default(0)->after('view_count');
            }
            if (!Schema::hasColumn('news', 'unique_readers')) {
                $table->unsignedBigInteger('unique_readers')->default(0)->after('page_views');
            }
            if (!Schema::hasColumn('news', 'robot_hits')) {
                $table->unsignedBigInteger('robot_hits')->default(0)->after('unique_readers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_reader_logs');

        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'robot_hits')) {
                $table->dropColumn('robot_hits');
            }
            if (Schema::hasColumn('news', 'unique_readers')) {
                $table->dropColumn('unique_readers');
            }
            if (Schema::hasColumn('news', 'page_views')) {
                $table->dropColumn('page_views');
            }
        });
    }
};
