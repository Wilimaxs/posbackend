<?php

namespace App\Http\Resources\Api\V1\Checkout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array
    {
        $items = $this->items
            ->map(function ($item) {
                $subtotal = (int)$item->unit_price * (int)$item->quantity;

                $discountValue = (int)$item->discount_value;

                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,

                    'quantity' => (int)$item->quantity,
                    'unit_price' => (int)$item->unit_price,

                    'discount' => $item->discount_id
                        ? [
                            'id' => $item->discount_id,
                            'name' => $item->discount_name,
                            'value' => $discountValue,
                        ] : null,

                    'subtotal' => $subtotal,
                    'subtotal_after_discount' => $subtotal - $discountValue,
                ];
            });

        $totalBeforeDiscount = $items->sum('subtotal');

        $totalDiscount = $items->sum(fn(array $item) => $item['discount']['value'] ?? 0);

        return [
            'sale_id' => $this->id,

            'status' => $this->status,

            'expires_at' => $this->created_at?->copy()->addMinutes(5)->toISOString(),

            'customer' => $this->customer
                ? [
                    'id' => $this->customer->id,

                    'customer_code' => $this->customer->customer_code,

                    'name' => $this->customer->name,

                    'phone' => $this->customer->phone,
                ] : null,

            'customer_type' => $this->customer_type,

            'items' => $items->values()->all(),

            'total_before_discount' => $totalBeforeDiscount,

            'total_discount' => $totalDiscount,

            'total_after_discount' => $totalBeforeDiscount - $totalDiscount,
        ];
    }
}
