<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static create(array $array)
 */
class Sale extends Model
{

    protected $fillable = [
        'store_id',
        'user_id',
        'customer_id',

        'invoice_number',
        'customer_type',

        'total_before_discount',
        'total_discount',
        'total_after_discount',

        'paid_amount',
        'remaining_balance',
        'payment_status',
        'due_date',

        'status',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_before_discount' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'total_after_discount' => 'decimal:2',

            'paid_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',

            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

}
