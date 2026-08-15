<?php

namespace App\Http\Resources\Api\V1\History;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items->map(function ($item) {
            $subtotal = (int)$item->unit_price * (int)$item->quantity;

            $discountValue = (int)$item->discount_value;

            return [
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->sku,
                'barcode' => $item->barcode,

                'quantity' => (int)$item->quantity,

                'unit_price' => (int)$item->unit_price,

                'discount' =>
                    $item->discount_id
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

        $installmentTotal = (int)$this->receivablePayments->sum('amount');

        $totalPaid = (int)$this->initial_payment + $installmentTotal;

        return [
            'invoice_number' => $this->invoice_number,

            'status' => $this->status,

            'created_at' => $this->created_at?->toISOString(),

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
                    'id' => $this->customer->id,

                    'customer_code' => $this->customer->customer_code,

                    'name' => $this->customer->name,

                    'phone' => $this->customer->phone,

                    'address' => $this->customer->address,
                ] : null,

            'customer_type' => $this->customer_type,

            'items' => $items->values()->all(),

            'total_before_discount' => $totalBeforeDiscount,

            'total_discount' => $totalDiscount,

            'total_after_discount' => $totalBeforeDiscount - $totalDiscount,

            'payment' => [
                'method' => $this->payment_method,

                'initial_payment' => (int)$this->initial_payment,

                'installment_total' => $installmentTotal,

                'total_paid' => $totalPaid,

                'change_amount' => (int)$this->change_amount,

                'remaining_balance' => (int)$this->remaining_balance,

                'payment_status' => $this->payment_status,

                'due_date' => $this->due_date?->format('Y-m-d'),

                'paid_at' => $this->paid_at?->toISOString(),
            ],

            'receivable_payments' => $this->receivablePayments
                ->map(fn($payment) => [
                    'id' => $payment->id,

                    'amount' => (int)$payment->amount,

                    'user' => [
                        'id' => $payment->user->id,
                        'name' => $payment->user->name,
                    ],

                    'notes' => $payment->notes,

                    'created_at' => $payment->created_at?->toISOString(),
                ])
                ->values()
                ->all(),

            'notes' => $this->notes,
        ];
    }
}
