<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemographicDataset extends Model
{
    protected $fillable = [
        'kecamatan_id',       // kept for legacy FK constraint only; use region_code for logic
        'region_level',
        'region_code',
        'title', 'year', 'semester', 'type',
        'file_path', 'file_size', 'file_type',
        'status', 'published_at', 'data_json', 'notes',
    ];

    protected $casts = [
        'data_json'    => 'array',
        'published_at' => 'datetime',
        'file_size'    => 'integer',
        'year'         => 'integer',
        'semester'     => 'integer',
    ];

    // ── Constants ────────────────────────────────────────────────────────

    public const REGENCY_CODE = '5205'; // BPS code for Kabupaten Dompu (52.05)

    /** BPS district codes: name → code */
    public const BPS_DISTRICT_CODES = [
        'DOMPU'      => '520501',
        'KEMPO'      => '520502',
        "HU'U"       => '520503',
        'KILO'       => '520504',
        'WOJA'       => '520505',
        'PEKAT'      => '520506',
        'MANGGALEWA' => '520507',
        'MANGGELEWA' => '520507',
        'PAJO'       => '520508',
    ];

    public const REGION_LEVELS = [
        'regency'  => 'Kabupaten',
        'district' => 'Kecamatan',
        'village'  => 'Desa / Kelurahan',
    ];

    public const SEMESTER_LABELS = [
        1 => 'Semester 1 (s.d. Juni)',
        2 => 'Semester 2 (s.d. Desember)',
    ];

    /** Valid dataset type labels for display */
    public const TYPE_LABELS = [
        'population'       => 'Kependudukan (Piramida Usia)',
        'religion'         => 'Distribusi Agama',
        'education'        => 'Tingkat Pendidikan',
        'marital'          => 'Status Perkawinan',
        'blood_type'       => 'Golongan Darah',
        'occupation'       => 'Jenis Pekerjaan',
        'disability'       => 'Jumlah Disabilitas',
        'akta_lahir'       => 'Cakupan Akta Lahir (0-17 Thn)',
        'kia'              => 'Cakupan Kartu Identitas Anak (KIA)',
        'ikd'              => 'Cakupan Identitas Kependudukan Digital (IKD)',
        'lansia'           => 'Jumlah Lansia (60+ Thn)',
        'productive_age'   => 'Jumlah Penduduk Produktif (15-59 Thn)',
        'households'       => 'Jumlah Kepala Keluarga (KK)',
        'wajib_ktp'        => 'Jumlah Wajib KTP & Perekaman',
        'dependency_ratio' => 'Rasio Ketergantungan (Dependency Ratio)',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Filter to a specific region (level + code).
     */
    public function scopeForRegion(Builder $query, string $level, string $code): Builder
    {
        return $query->where('region_level', $level)->where('region_code', $code);
    }

    /**
     * Filter to a specific reporting period.
     */
    public function scopeForPeriod(Builder $query, int $year, int $semester): Builder
    {
        return $query->where('year', $year)->where('semester', $semester);
    }

    // ── Relations ────────────────────────────────────────────────────────

    /**
     * Legacy relation kept for CMS dataset list display only.
     * Do NOT use for demographic logic; use region_level + region_code instead.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getRegionLevelLabelAttribute(): string
    {
        return self::REGION_LEVELS[$this->region_level] ?? $this->region_level;
    }

    /**
     * Human-readable region name derived from region_code.
     * Returns kecamatan name for district level, else the code.
     */
    public function getRegionNameAttribute(): string
    {
        if ($this->region_level === 'regency') {
            return 'Kabupaten Dompu';
        }
        if ($this->kecamatan) {
            return $this->kecamatan->name;
        }
        return $this->region_code ?? '—';
    }
}
