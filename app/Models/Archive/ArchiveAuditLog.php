<?php

namespace App\Models\Archive;

use App\Models\Queue\QueueApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ArchiveAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'archive_audit_logs';

    protected $fillable = [
        'archive_package_id',
        'application_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'details',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(ArchivePackage::class, 'archive_package_id');
    }

    public function application()
    {
        return $this->belongsTo(QueueApplication::class, 'application_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
