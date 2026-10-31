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
        if (Schema::hasTable('visitor_geolocations')) {
            Schema::table('visitor_geolocations', function (Blueprint $table) {
                if (!Schema::hasColumn('visitor_geolocations', 'district')) {
                    $table->string('district', 100)->nullable()->after('city');
                }
                if (!Schema::hasColumn('visitor_geolocations', 'isp')) {
                    $table->string('isp', 150)->nullable()->after('timezone');
                }
                if (!Schema::hasColumn('visitor_geolocations', 'asn')) {
                    $table->string('asn', 150)->nullable()->after('isp');
                }
                if (!Schema::hasColumn('visitor_geolocations', 'confidence')) {
                    $table->string('confidence', 50)->nullable()->after('asn');
                }
                if (!Schema::hasColumn('visitor_geolocations', 'ip_version')) {
                    $table->string('ip_version', 10)->nullable()->after('confidence');
                }
            });
        }

        if (Schema::hasTable('ip_geolocation_cache')) {
            Schema::table('ip_geolocation_cache', function (Blueprint $table) {
                if (!Schema::hasColumn('ip_geolocation_cache', 'district')) {
                    $table->string('district', 100)->nullable()->after('city');
                }
                if (!Schema::hasColumn('ip_geolocation_cache', 'isp')) {
                    $table->string('isp', 150)->nullable()->after('timezone');
                }
                if (!Schema::hasColumn('ip_geolocation_cache', 'asn')) {
                    $table->string('asn', 150)->nullable()->after('isp');
                }
                if (!Schema::hasColumn('ip_geolocation_cache', 'confidence')) {
                    $table->string('confidence', 50)->nullable()->after('asn');
                }
                if (!Schema::hasColumn('ip_geolocation_cache', 'ip_version')) {
                    $table->string('ip_version', 10)->nullable()->after('confidence');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('visitor_geolocations')) {
            Schema::table('visitor_geolocations', function (Blueprint $table) {
                $table->dropColumn(['district', 'isp', 'asn', 'confidence', 'ip_version']);
            });
        }

        if (Schema::hasTable('ip_geolocation_cache')) {
            Schema::table('ip_geolocation_cache', function (Blueprint $table) {
                $table->dropColumn(['district', 'isp', 'asn', 'confidence', 'ip_version']);
            });
        }
    }
};
