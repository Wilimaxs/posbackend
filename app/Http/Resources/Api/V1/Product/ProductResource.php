<?php

namespace App\Http\Resources\Api\V1\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stock = $this->productStocks->first();

        $discount = $stock?->discount;

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,

            'image_url' => $this->img_url ? asset('storage/'.$this->img_url) : null,

            'description' => $this->description,

            'category' => $this->category
                ? [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ]
                : null,

            'cost_price' => (int) $this->cost_price,

            'price' => [
                'normal' => (int) $this->selling_price_normal,
                'grocier' => $this->selling_price_grocier !== null
                    ? (int) $this->selling_price_grocier
                    : null,
            ],

            'unit' => $this->unit,

            'stock' => $stock?->stock ?? 0,
            'minimum_stock' => $stock?->minimum_stock ?? 0,

            'discount' => $discount
                ? [
                    'id' => $discount->id,
                    'name' => $discount->name,
                    'value' => (int) $discount->discount_value,
                    'customer_scope' => $discount->customer_scope,
                ]
                : null,

            'is_active' => $this->is_active,
        ];
    }
}
