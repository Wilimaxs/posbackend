<?php

namespace App\Http\Resources\Api\V1\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array
    {
        $items = $this->items->map(function ($item) {
            $subtotal = (int)$item->unit_price * (int)$item->quantity;

            $discount = (int)$item->discount_value;

            return [
                'product_name' => $item->product_name,

                'quantity' => (int)$item->quantity,

                'unit_price' => (int)$item->unit_price,

                'discount' => $discount > 0 ? $discount : null,

                'subtotal' => $subtotal,

                'subtotal_after_discount' => $subtotal - $discount,
            ];
        }
        );

        $totalBeforeDiscount = $items->sum('subtotal');

        $totalDiscount = $items->sum(fn(array $item) => $item['discount'] ?? 0
        );

        return [
            'sale_id' => $this->id,

            'invoice_number' => $this->invoice_number,

            'store' => [
                'name' => $this->store->name,

                'address' => $this->store->address,

                'phone' => $this->store->phone,
            ],

            'user' => [
                'name' => $this->user->name,
            ],

            'customer' =>
                $this->customer
                    ? [
                    'name' => $this->customer->name,
                ] : null,

            'customer_type' => $this->customer_type,

            'created_at' => $this->created_at?->toISOString(),

            'items' => $items->values()->all(),

            'total_before_discount' => $totalBeforeDiscount,

            'total_discount' => $totalDiscount,

            'total_after_discount' => $totalBeforeDiscount - $totalDiscount,

            'initial_payment' => (int)$this->initial_payment,

            'change_amount' => (int)$this->change_amount,

            'remaining_balance' => (int)$this->remaining_balance,

            'payment_method' => $this->payment_method,

            'payment_status' => $this->payment_status,

            'due_date' => $this->due_date?->format('Y-m-d'),

            'paid_at' => $this->paid_at?->toISOString(),

            'status' => $this->status,

            'notes' => $this->notes,
        ];
    }
}
