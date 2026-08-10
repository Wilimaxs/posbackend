<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 */
class SaleItem extends Model
{

    protected $fillable = [
        'sale_id',
        'product_id',

        'product_name',
        'sku',
        'barcode',
        'unit',

        'quantity',

        'cost_price',
        'unit_price',
        'price_type',

        'subtotal',

        'discount_id',
        'discount_name',
        'discount_value',

        'subtotal_after_discount',
    ];


    protected function casts(): array
    {
        return [
            'quantity' => 'integer',

            'cost_price' => 'decimal:2',
            'unit_price' => 'decimal:2',

            'subtotal' => 'decimal:2',

            'discount_value' => 'decimal:2',

            'subtotal_after_discount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
