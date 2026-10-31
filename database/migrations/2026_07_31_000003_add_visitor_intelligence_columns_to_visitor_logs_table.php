<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add intelligence columns to visitor_logs table
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('visitor_logs', 'is_human')) {
                    $table->boolean('is_human')->default(true)->index()->after('device');
                }
                if (!Schema::hasColumn('visitor_logs', 'visitor_category')) {
                    $table->string('visitor_category', 50)->default('human')->index()->after('is_human');
                }
                if (!Schema::hasColumn('visitor_logs', 'bot_name')) {
                    $table->string('bot_name', 100)->nullable()->after('visitor_category');
                }
                if (!Schema::hasColumn('visitor_logs', 'detection_reason')) {
                    $table->string('detection_reason', 255)->nullable()->after('bot_name');
                }
            });
        }

        // 2. Add intelligence columns to visitor_logs_archive table if exists
        if (Schema::hasTable('visitor_logs_archive')) {
            Schema::table('visitor_logs_archive', function (Blueprint $table) {
                if (!Schema::hasColumn('visitor_logs_archive', 'is_human')) {
                    $table->boolean('is_human')->default(true)->index()->after('device_type');
                }
                if (!Schema::hasColumn('visitor_logs_archive', 'visitor_category')) {
                    $table->string('visitor_category', 50)->default('human')->index()->after('is_human');
                }
                if (!Schema::hasColumn('visitor_logs_archive', 'bot_name')) {
                    $table->string('bot_name', 100)->nullable()->after('visitor_category');
                }
                if (!Schema::hasColumn('visitor_logs_archive', 'detection_reason')) {
                    $table->string('detection_reason', 255)->nullable()->after('bot_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('visitor_logs')) {
            Schema::table('visitor_logs', function (Blueprint $table) {
                $columns = ['is_human', 'visitor_category', 'bot_name', 'detection_reason'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('visitor_logs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('visitor_logs_archive')) {
            Schema::table('visitor_logs_archive', function (Blueprint $table) {
                $columns = ['is_human', 'visitor_category', 'bot_name', 'detection_reason'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('visitor_logs_archive', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
