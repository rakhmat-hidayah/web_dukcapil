<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BPS official region codes for Kabupaten Dompu (NTB).
     * Format: 52.05     = Kabupaten Dompu
     *         52.05.01  = Kec. Dompu, etc.
     * Stored without dots: 5205, 520501, …
     */
    private const BPS_CODES = [
        'regency' => '5205',
        'districts' => [
            'DOMPU'      => '520501',
            'KEMPO'      => '520502',
            "HU'U"       => '520503',
            'KILO'       => '520504',
            'WOJA'       => '520505',
            'PEKAT'      => '520506',
            'MANGGALEWA' => '520507',
            'MANGGELEWA' => '520507',
            'PAJO'       => '520508',
        ],
    ];

    public function up(): void
    {
        // ── 1. Add region columns to demographic_datasets ─────────────────
        Schema::table('demographic_datasets', function (Blueprint $table) {
            $table->enum('region_level', ['regency', 'district', 'village'])
                  ->default('regency')
                  ->after('kecamatan_id');

            $table->string('region_code', 20)
                  ->nullable()
                  ->after('region_level')
                  ->comment('BPS region code: 5205=Kab.Dompu, 520501=Kec.Dompu, …');

            $table->index(
                ['region_level', 'region_code', 'year', 'semester', 'status'],
                'idx_region_period_status'
            );
        });

        // ── 2. Update kecamatans.code to official BPS codes ───────────────
        foreach (self::BPS_CODES['districts'] as $name => $bpsCode) {
            DB::statement(
                "UPDATE kecamatans SET code = ? WHERE UPPER(name) = UPPER(?)",
                [$bpsCode, $name]
            );
        }

        // ── 3. Migrate existing demographic_datasets ──────────────────────
        // Kabupaten-level datasets (kecamatan_id IS NULL)
        DB::statement("
            UPDATE demographic_datasets
            SET region_level = 'regency',
                region_code  = '5205'
            WHERE kecamatan_id IS NULL
        ");

        // District-level datasets (kecamatan_id IS NOT NULL) — use updated BPS code
        DB::statement("
            UPDATE demographic_datasets d
            JOIN kecamatans k ON k.id = d.kecamatan_id
            SET d.region_level = 'district',
                d.region_code  = k.code
            WHERE d.kecamatan_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('demographic_datasets', function (Blueprint $table) {
            $table->dropIndex('idx_region_period_status');
            $table->dropColumn(['region_level', 'region_code']);
        });

        // Restore old Permendagri codes (best-effort)
        $restore = [
            'DOMPU'      => '5208010',
            'WOJA'       => '5208011',
            "HU'U"       => '5208020',
            'MANGGALEWA' => '5208030',
            'MANGGELEWA' => '5208030',
            'KEMPO'      => '5208040',
            'KILO'       => '5208050',
            'PEKAT'      => '5208060',
            'PAJO'       => '5208070',
        ];

        foreach ($restore as $name => $oldCode) {
            DB::statement(
                "UPDATE kecamatans SET code = ? WHERE UPPER(name) = UPPER(?)",
                [$oldCode, $name]
            );
        }
    }
};
