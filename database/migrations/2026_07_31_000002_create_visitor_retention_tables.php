<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. visitor_daily_summary — retained statistics even after log cleanup
        if (!Schema::hasTable('visitor_daily_summary')) {
            Schema::create('visitor_daily_summary', function (Blueprint $table) {
                $table->id();
                $table->date('summary_date')->unique()->index();
                $table->unsignedInteger('total_visits')->default(0);
                $table->unsignedInteger('unique_visitors')->default(0);
                $table->unsignedInteger('desktop_count')->default(0);
                $table->unsignedInteger('mobile_count')->default(0);
                $table->unsignedInteger('tablet_count')->default(0);
                $table->unsignedInteger('robot_count')->default(0);
                $table->unsignedInteger('top_browser_count')->default(0);
                $table->string('top_browser', 50)->nullable();
                $table->string('top_country', 100)->nullable();
                $table->string('top_city', 100)->nullable();
                $table->timestamps();
            });
        }

        // 2. visitor_cleanup_logs — audit trail of every cleanup run
        if (!Schema::hasTable('visitor_cleanup_logs')) {
            Schema::create('visitor_cleanup_logs', function (Blueprint $table) {
                $table->id();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->unsignedInteger('deleted_records')->default(0);
                $table->decimal('deleted_size_mb', 10, 4)->default(0);
                $table->unsignedInteger('archived_records')->default(0);
                $table->enum('status', ['running', 'completed', 'failed', 'cancelled'])->default('running')->index();
                $table->unsignedInteger('duration_seconds')->nullable();
                $table->string('retention_days', 10)->nullable();
                $table->text('error_message')->nullable();
                $table->string('triggered_by', 50)->default('scheduler'); // scheduler | manual | api
                $table->timestamps();
            });
        }

        // 3. visitor_logs_archive — optional archive table (same structure as visitor_logs)
        if (!Schema::hasTable('visitor_logs_archive')) {
            Schema::create('visitor_logs_archive', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id')->index();
                $table->string('visitor_uuid', 64)->nullable()->index();
                $table->string('session_id', 100)->nullable();
                $table->string('ip_address', 45)->nullable()->index();
                $table->text('url')->nullable();
                $table->string('method', 10)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('browser', 100)->nullable();
                $table->string('browser_version', 50)->nullable();
                $table->string('operating_system', 50)->nullable();
                $table->string('operating_system_version', 50)->nullable();
                $table->string('device_type', 20)->nullable();
                $table->boolean('is_robot')->default(false);
                $table->text('referrer')->nullable();
                $table->timestamp('visited_at')->nullable()->index();
                $table->timestamp('archived_at')->useCurrent()->index();
                $table->timestamps();
            });
        }

        // 4. Seed default visitor analytics settings into website_settings
        $defaults = [
            ['key' => 'visitor_logging_enabled',       'value' => '1',       'group' => 'visitor_analytics'],
            ['key' => 'visitor_auto_cleanup_enabled',   'value' => '1',       'group' => 'visitor_analytics'],
            ['key' => 'visitor_retention_days',         'value' => '90',      'group' => 'visitor_analytics'],
            ['key' => 'visitor_cleanup_schedule',       'value' => 'daily',   'group' => 'visitor_analytics'],
            ['key' => 'visitor_cleanup_time',           'value' => '02:00',   'group' => 'visitor_analytics'],
            ['key' => 'visitor_archive_before_delete',  'value' => '0',       'group' => 'visitor_analytics'],
            ['key' => 'visitor_cleanup_chunk_size',     'value' => '1000',    'group' => 'visitor_analytics'],
            ['key' => 'visitor_last_cleanup_at',        'value' => '',        'group' => 'visitor_analytics'],
            ['key' => 'visitor_last_cleanup_deleted',   'value' => '0',       'group' => 'visitor_analytics'],
        ];

        foreach ($defaults as $setting) {
            DB::table('website_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs_archive');
        Schema::dropIfExists('visitor_cleanup_logs');
        Schema::dropIfExists('visitor_daily_summary');

        $keys = [
            'visitor_logging_enabled', 'visitor_auto_cleanup_enabled',
            'visitor_retention_days', 'visitor_cleanup_schedule',
            'visitor_cleanup_time', 'visitor_archive_before_delete',
            'visitor_cleanup_chunk_size', 'visitor_last_cleanup_at',
            'visitor_last_cleanup_deleted',
        ];
        DB::table('website_settings')->whereIn('key', $keys)->delete();
    }
};
