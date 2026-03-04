<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'seller_id',
        'customer_id',
        'idempotency_key',
        'amount',
        'currency',
        'payment_provider',
        'payment_provider_fee',
        'commission_rate',
        'commission_amount',
        'net_amount',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'payment_provider_fee' => 'integer',
        'commission_rate' => 'decimal:4',
        'commission_amount' => 'integer',
        'net_amount' => 'integer',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction): void {
            if (! $transaction->getKey()) {
                $transaction->setAttribute('id', 'txn_'.Str::lower(Str::random(14)));
            }
        });
    }
}
