<?php

namespace App\Http\Resources\Api\V1\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /*
             * invoice_number sekaligus menjadi identifier
             * untuk membuka detail transaksi.
             */
            'invoice_number' =>
                $this->invoice_number,

            'created_at' =>
                $this->created_at?->toISOString(),

            'customer' => $this->customer
                ? [
                    'name' =>
                        $this->customer->name,
                ]
                : null,

            'customer_type' =>
                $this->customer_type,

            'payment_method' =>
                $this->payment_method,

            'total_after_discount' =>
                (int) $this->total_after_discount,

            'payment_status' =>
                $this->payment_status,
        ];
    }
}
