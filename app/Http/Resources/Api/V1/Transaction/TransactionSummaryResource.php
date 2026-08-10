<?php

namespace App\Http\Resources\Api\V1\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_transactions' =>
                (int) $this['total_transactions'],

            'total_sales' =>
                (int) $this['total_sales'],

            'cash_payment' =>
                (int) $this['cash_payment'],

            /*
             * Belum implement QRIS.
             */
            'qris_payment' =>
                0,
        ];
    }
}
