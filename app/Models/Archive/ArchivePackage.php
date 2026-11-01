<?php

namespace App\Models\Archive;

use App\Models\Queue\ServiceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivePackage extends Model
{
    use SoftDeletes;

    protected $table = 'archive_packages';

    const STATUS_QUEUED = 'QUEUED';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_READY_FOR_DOWNLOAD = 'READY_FOR_DOWNLOAD';
    const STATUS_FAILED = 'FAILED';
    const STATUS_INCOMPLETE = 'INCOMPLETE';
    const STATUS_CORRUPTED = 'CORRUPTED';

    const PERM_NOT_CONFIGURED = 'NOT_CONFIGURED';
    const PERM_PENDING_TRANSFER = 'PENDING_TRANSFER';
    const PERM_TRANSFERRED = 'TRANSFERRED';
    const PERM_VERIFIED = 'PERMANENT_STORAGE_VERIFIED';
    const PERM_TRANSFER_FAILED = 'TRANSFER_FAILED';
    const PERM_VERIFICATION_FAILED = 'VERIFICATION_FAILED';

    protected $fillable = [
        'package_code',
        'archive_period',
        'service_type_id',
        'filter_criteria',
        'status',
        'total_applications',
        'total_files',
        'total_uncompressed_bytes',
        'package_size_bytes',
        'sha256_checksum',
        'zip_path',
        'disk',
        'manifest_data',
        'error_message',
        'created_by',
        'is_operator_confirmed',
        'operator_confirmed_at',
        'operator_confirmed_by',
        'operator_storage_location_notes',
        'permanent_storage_status',
        'permanent_storage_destination',
        'permanent_storage_verified_at',
        'permanent_storage_checksum',
        'source_files_cleaned',
        'source_files_cleaned_at',
    ];

    protected $casts = [
        'filter_criteria' => 'array',
        'manifest_data' => 'array',
        'total_applications' => 'integer',
        'total_files' => 'integer',
        'total_uncompressed_bytes' => 'integer',
        'package_size_bytes' => 'integer',
        'is_operator_confirmed' => 'boolean',
        'source_files_cleaned' => 'boolean',
        'operator_confirmed_at' => 'datetime',
        'permanent_storage_verified_at' => 'datetime',
        'source_files_cleaned_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_package_size',
        'formatted_uncompressed_size',
        'status_label',
        'permanent_storage_label',
        'download_url',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function items()
    {
        return $this->hasMany(ArchivePackageItem::class, 'archive_package_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'operator_confirmed_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(ArchiveAuditLog::class, 'archive_package_id')->orderBy('created_at', 'desc');
    }

    public function getFormattedPackageSizeAttribute(): string
    {
        $bytes = (int) $this->package_size_bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    public function getFormattedUncompressedSizeAttribute(): string
    {
        $bytes = (int) $this->total_uncompressed_bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_QUEUED => 'Menunggu Antrean',
            self::STATUS_PROCESSING => 'Sedang Dikompres (ZIP)',
            self::STATUS_READY_FOR_DOWNLOAD => 'Siap Diunduh',
            self::STATUS_INCOMPLETE => 'Dokumen Tidak Lengkap',
            self::STATUS_FAILED => 'Gagal Dibuat',
            self::STATUS_CORRUPTED => 'Integritas Berkas Rusak / Berubah',
        ][$this->status] ?? $this->status;
    }

    public function getPermanentStorageLabelAttribute(): string
    {
        return [
            self::PERM_NOT_CONFIGURED => 'Belum Terverifikasi di Penyimpanan Permanen',
            self::PERM_PENDING_TRANSFER => 'Menunggu Transfer',
            self::PERM_TRANSFERRED => 'Ditransfer (Menunggu Verifikasi)',
            self::PERM_VERIFIED => 'Terverifikasi di Penyimpanan Permanen',
            self::PERM_TRANSFER_FAILED => 'Transfer Gagal',
            self::PERM_VERIFICATION_FAILED => 'Verifikasi Hash Gagal',
        ][$this->permanent_storage_status] ?? 'Belum Terverifikasi';
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('admin.archive.packages.download', ['id' => $this->id]);
    }
}
