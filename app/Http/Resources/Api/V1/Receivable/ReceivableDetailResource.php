<?php

namespace App\Http\Resources\Api\V1\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $invoice_number
 * @property mixed $customer
 * @property mixed $due_date
 * @property mixed $remaining_balance
 * @property mixed $total_after_discount
 * @property mixed $created_at
 * @property mixed $user
 * @property mixed $items
 * @property mixed $payment_method
 * @property mixed $total_discount
 * @property mixed $receivablePayments
 */
class ReceivableDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $installmentTotal =
            $this->getInstallmentTotal();

        $initialPayment =
            $this->getInitialPayment(
                installmentTotal: $installmentTotal
            );

        return [

            'invoice_number' =>
                $this->invoice_number,

            'customer' => [
                'name' =>
                    $this->customer?->name,

                'phone' =>
                    $this->customer?->phone,

                'address' =>
                    $this->customer?->address,
            ],

            'due_date' =>
                $this->due_date?->format('Y-m-d'),

            'due_status' =>
                $this->resolveDueStatus(),

            'total_after_discount' => (int)$this->total_after_discount,

            'initial_payment' => $initialPayment,

            'paid_amount' => $installmentTotal,

            'remaining_balance' => (int)$this->remaining_balance,


            'original_transaction' => [

                'created_at' => $this->created_at?->toISOString(),

                'user' => [
                    'name' => $this->user?->name,
                ],

                'item_count' =>
                    (int)$this->items->sum('quantity'),

                'payment_method' => $this->payment_method,

                'items' =>
                    $this->items
                        ->map(function ($item) {
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
                        })
                        ->values(),

                'total_discount' =>
                    (int)$this->total_discount,

                'total_after_discount' =>
                    (int)$this->total_after_discount,
            ],

            'payment_history' =>
                $this->receivablePayments
                    ->map(function ($payment) {
                        return [
                            'amount' =>
                                (int)$payment->amount,

                            'paid_at' =>
                                $payment->paid_at
                                    ?->toISOString(),

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

    private function getInstallmentTotal(): int
    {
        return (int)$this->receivablePayments
            ->sum('amount');
    }

    private function getInitialPayment(
        int $installmentTotal
    ): int
    {
        return max(
            0,
            (int)$this->resource->paid_amount
            - $installmentTotal
        );
    }

    private function resolveDueStatus(): string
    {
        if ($this->due_date->isToday()) {
            return 'due_today';
        }

        if ($this->due_date->isPast()) {
            return 'overdue';
        }

        return 'active';
    }
}
