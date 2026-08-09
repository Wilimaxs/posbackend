<?php

namespace App\Http\Resources\Api\V1\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $productStocks
 * @property mixed $id
 * @property mixed $sku
 * @property mixed $barcode
 * @property mixed $name
 * @property mixed $img_url
 * @property mixed $category
 * @property mixed $selling_price_normal
 * @property mixed $selling_price_grocier
 * @property mixed $is_active
 *
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stock = $this->productStocks->first();

        $discount = $stock?->discount;

        $isDiscountActive = $discount
            && $discount->is_active
            && now()->between(
                $discount->starts_date,
                $discount->ends_date
            );

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            // Todo = wajib diganti dengan yang asli
            'image_url' => "https://picsum.photos/seed/product-$this->id/400/400",

            'category' => $this->category
                ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ]
                : null,

            'price' => [
                'normal' => (float)$this->selling_price_normal,
                'grocier' => $this->selling_price_grocier !== null
                    ? (float)$this->selling_price_grocier
                    : null,
            ],

            'stock' => $stock?->stock ?? 0,
            'minimum_stock' => $stock?->minimum_stock ?? 0,

            'discount' => $isDiscountActive
                ? [
                    'id' => $discount->id,
                    'name' => $discount->name,
                    'value' => (float)$discount->discount_value,
                    'customer_scope' => $discount->customer_scope,
                ]
                : null,

            'is_active' => (bool)$this->is_active,
        ];
    }
}
