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
        // 1. Enhance existing visitor_logs table with Phase 1 columns
        Schema::table('visitor_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('visitor_logs', 'visitor_uuid')) {
                $table->string('visitor_uuid', 64)->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('visitor_logs', 'session_id')) {
                $table->string('session_id', 100)->nullable()->after('visitor_uuid')->index();
            }
            if (!Schema::hasColumn('visitor_logs', 'browser_version')) {
                $table->string('browser_version', 50)->nullable()->after('browser');
            }
            if (!Schema::hasColumn('visitor_logs', 'operating_system')) {
                $table->string('operating_system', 50)->nullable()->after('browser_version')->index();
            }
            if (!Schema::hasColumn('visitor_logs', 'operating_system_version')) {
                $table->string('operating_system_version', 50)->nullable()->after('operating_system');
            }
            if (!Schema::hasColumn('visitor_logs', 'device_type')) {
                $table->string('device_type', 20)->nullable()->after('operating_system_version')->index();
            }
            if (!Schema::hasColumn('visitor_logs', 'is_robot')) {
                $table->boolean('is_robot')->default(false)->after('device_type')->index();
            }
            if (!Schema::hasColumn('visitor_logs', 'visited_at')) {
                $table->timestamp('visited_at')->nullable()->after('referrer')->index();
            }
        });

        // 2. Create visitor_geolocations table
        if (!Schema::hasTable('visitor_geolocations')) {
            Schema::create('visitor_geolocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visitor_log_id')->constrained('visitor_logs')->onDelete('cascade');
                $table->string('country', 100)->nullable();
                $table->string('country_code', 10)->nullable();
                $table->string('province', 100)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('timezone', 100)->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['visitor_log_id', 'status']);
            });
        }

        // 3. Create ip_geolocation_cache table
        if (!Schema::hasTable('ip_geolocation_cache')) {
            Schema::create('ip_geolocation_cache', function (Blueprint $table) {
                $table->id();
                $table->string('ip', 45)->unique()->index();
                $table->string('country', 100)->nullable();
                $table->string('country_code', 10)->nullable();
                $table->string('province', 100)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('timezone', 100)->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_geolocation_cache');
        Schema::dropIfExists('visitor_geolocations');

        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropColumn([
                'visitor_uuid',
                'session_id',
                'browser_version',
                'operating_system',
                'operating_system_version',
                'device_type',
                'is_robot',
                'visited_at',
            ]);
        });
    }
};
