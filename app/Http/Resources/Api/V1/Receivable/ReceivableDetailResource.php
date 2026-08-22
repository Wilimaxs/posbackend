<?php

namespace App\Http\Resources\Api\V1\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items->map(function ($item) {
            $subtotal = (int)$item->unit_price * (int)$item->quantity;

            $discount = (int)$item->discount_value;

            return [
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'quantity' => (int)$item->quantity,
                'unit_price' => (int)$item->unit_price,
                'discount' => $discount > 0 ? $discount : null,
                'subtotal' => $subtotal,
                'subtotal_after_discount' => $subtotal - $discount,
            ];
        });

        $totalBeforeDiscount = $items->sum('subtotal');

        $totalDiscount = $items->sum(fn(array $item) => $item['discount'] ?? 0);

        $installmentTotal = (int)$this->receivablePayments->sum('amount');

        return [
            'sale_id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer' => [
                'id' => $this->customer->id,
                'customer_code' => $this->customer->customer_code,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
                'address' => $this->customer->address,
            ],

            'items' => $items->values()->all(),

            'total_before_discount' => $totalBeforeDiscount,

            'total_discount' => $totalDiscount,

            'total_after_discount' => $totalBeforeDiscount - $totalDiscount,

            'due_status' => $this->getDueStatus(),

            'cashier' => [
                'name' => $this->user->name,
            ],

            'payment_method' => $this->payment_method,

            'initial_payment' => (int)$this->initial_payment, // uang DP

            'installment_total' => $installmentTotal, // total uang cicilan saja

            'total_paid' => (int)$this->initial_payment + $installmentTotal, // total uang DP + cicilan

            'remaining_balance' => (int)$this->remaining_balance, // sisa utang sekarang

            'due_date' => $this->due_date?->format('Y-m-d'), // tanggal jatuh tempo

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

            'created_at' =>
                $this->created_at?->toISOString(),
        ];
    }

    private function getDueStatus(): string
    {
        if ($this->due_date->isToday()) {
            return 'today';
        }

        if ($this->due_date->isPast()) {
            return 'overdue';
        }

        return 'active';
    }
}
