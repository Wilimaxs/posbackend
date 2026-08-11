<?php

namespace App\Http\Resources\Api\V1\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $invoice_number
 * @property mixed $store
 * @property mixed $user
 * @property mixed $customer
 * @property mixed $customer_type
 * @property mixed $created_at
 * @property mixed $items
 * @property mixed $total_discount
 * @property mixed $total_after_discount
 * @property mixed $paid_amount
 * @property mixed $remaining_balance
 * @property mixed $payment_status
 * @property mixed $due_date
 * @property mixed $change_amount
 * @property mixed $payment_method
 * @property mixed $receivablePayments
 */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $installmentPaidAmount =
            (int)$this->receivablePayments->sum('amount');

        $initialPayment = max(
            0,
            (int)$this->paid_amount
            - $installmentPaidAmount
            - (int)($this->change_amount ?? 0)
        );


        return [
            'invoice_number' =>
                $this->invoice_number,

            'store' => [
                'name' =>
                    $this->store->name,

                'address' =>
                    $this->store->address,

                'phone' =>
                    $this->store->phone,
            ],

            'user' => [
                'name' =>
                    $this->user->name,
            ],

            'customer' => $this->customer
                ? [
                    'name' =>
                        $this->customer->name,
                ]
                : null,

            'customer_type' =>
                $this->customer_type,

            'created_at' =>
                $this->created_at?->toISOString(),

            'items' => $this->items->map(
                function ($item) {
                    return [
                        'product_name' =>
                            $item->product_name,

                        'quantity' =>
                            (int)$item->quantity,

                        'unit_price' =>
                            (int)$item->unit_price,

                        'discount' =>
                            (int)$item->discount_value > 0
                                ? (int)$item->discount_value
                                : null,

                        'subtotal_after_discount' =>
                            (int)$item->subtotal_after_discount,
                    ];
                }
            )->values(),

            'initial_payment' =>
                $initialPayment,

            'installment_paid_amount' =>
                $installmentPaidAmount,

            'total_discount' =>
                (int)$this->total_discount,

            'total_after_discount' =>
                (int)$this->total_after_discount,

            'paid_amount' =>
                (int)$this->paid_amount,

            'change_amount' =>
                $this->change_amount !== null
                    ? (int)$this->change_amount
                    : null,

            'remaining_balance' =>
                (int)$this->remaining_balance,

            'payment_method' =>
                $this->payment_method,

            'payment_status' =>
                $this->payment_status,

            'due_date' =>
                $this->due_date?->format('Y-m-d'),

            'payment_history' =>
                $this->receivablePayments
                    ->map(function ($payment) {
                        return [
                            'amount' =>
                                (int)$payment->amount,

                            'paid_at' =>
                                $payment->paid_at?->toISOString(),

                            'user' => [
                                'name' =>
                                    $payment->user?->name,
                            ],

                            'notes' =>
                                $payment->notes,
                        ];
                    })
                    ->values(),
        ];
    }
}
