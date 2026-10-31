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
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table) {
                if (!Schema::hasColumn('news', 'social_image')) {
                    $table->string('social_image')->nullable()->after('og_image');
                }
                if (!Schema::hasColumn('news', 'image_alt')) {
                    $table->string('image_alt')->nullable()->after('social_image');
                }
                if (!Schema::hasColumn('news', 'share_count')) {
                    $table->unsignedBigInteger('share_count')->default(0)->after('view_count')->index();
                }
                if (!Schema::hasColumn('news', 'whatsapp_shares')) {
                    $table->unsignedBigInteger('whatsapp_shares')->default(0)->after('share_count');
                }
                if (!Schema::hasColumn('news', 'facebook_shares')) {
                    $table->unsignedBigInteger('facebook_shares')->default(0)->after('whatsapp_shares');
                }
                if (!Schema::hasColumn('news', 'telegram_shares')) {
                    $table->unsignedBigInteger('telegram_shares')->default(0)->after('facebook_shares');
                }
                if (!Schema::hasColumn('news', 'twitter_shares')) {
                    $table->unsignedBigInteger('twitter_shares')->default(0)->after('telegram_shares');
                }
                if (!Schema::hasColumn('news', 'linkedin_shares')) {
                    $table->unsignedBigInteger('linkedin_shares')->default(0)->after('twitter_shares');
                }
                if (!Schema::hasColumn('news', 'copy_link_count')) {
                    $table->unsignedBigInteger('copy_link_count')->default(0)->after('linkedin_shares');
                }
            });
        }

        if (!Schema::hasTable('news_shares')) {
            Schema::create('news_shares', function (Blueprint $table) {
                $table->id();
                $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
                $table->string('platform', 50)->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['news_id', 'platform']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_shares');

        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn([
                    'social_image', 'image_alt', 'share_count',
                    'whatsapp_shares', 'facebook_shares', 'telegram_shares',
                    'twitter_shares', 'linkedin_shares', 'copy_link_count'
                ]);
            });
        }
    }
};
