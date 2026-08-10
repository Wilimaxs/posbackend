<?php

namespace App\Http\Resources\Api\V1\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sale_id' =>
                $this->id,

            'invoice_number' =>
                $this->invoice_number,

            'customer' => $this->customer
                ? [
                    'id' =>
                        $this->customer->id,

                    'customer_code' =>
                        $this->customer->customer_code,

                    'name' =>
                        $this->customer->name,
                ]
                : null,

            'customer_type' =>
                $this->customer_type,

            'items' => $this->items->map(
                function ($item) {
                    return [
                        'product_id' =>
                            $item->product_id,

                        'product_name' =>
                            $item->product_name,

                        'sku' =>
                            $item->sku,

                        'quantity' =>
                            (int)$item->quantity,

                        'cost_price' =>
                            (int)$item->cost_price,

                        'unit_price' =>
                            (int)$item->unit_price,

                        'price_type' =>
                            $item->price_type,

                        'subtotal' =>
                            (int)$item->subtotal,

                        'discount' =>
                            $item->discount_id
                                ? [
                                'id' =>
                                    $item->discount_id,

                                'name' =>
                                    $item->discount_name,

                                'value' =>
                                    (int)$item->discount_value,
                            ]
                                : null,

                        'subtotal_after_discount' =>
                            (int)$item->subtotal_after_discount,
                    ];
                }
            ),

            'total_before_discount' =>
                (int)$this->total_before_discount,

            'total_discount' =>
                (int)$this->total_discount,

            'total_after_discount' =>
                (int)$this->total_after_discount,

            'paid_amount' =>
                (int)$this->paid_amount,

            'remaining_balance' =>
                (int)$this->remaining_balance,

            'payment_status' =>
                $this->payment_status,

            'due_date' =>
                $this->due_date?->format('Y-m-d'),

            'status' =>
                $this->status,

            'paid_at' =>
                $this->paid_at?->toISOString(),

            'created_at' =>
                $this->created_at?->toISOString(),
        ];
    }
}
