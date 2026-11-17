<?php

namespace App\Models\BlankStock;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class KtpBlankReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'ktp_blank_receipts';

    protected $fillable = [
        'receipt_number',
        'received_date',
        'quantity',
        'source',
        'document_number',
        'document_date',
        'received_by',
        'notes',
        'attachment_path',
        'status',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'received_date'  => 'date:Y-m-d',
        'document_date'  => 'date:Y-m-d',
        'quantity'       => 'integer',
        'balance_before' => 'integer',
        'balance_after'  => 'integer',
    ];

    public const STATUS_VERIFIED = 'VERIFIED';
    public const STATUS_REVERSED = 'REVERSED';

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(KtpBlankTransaction::class, 'reference');
    }
}
