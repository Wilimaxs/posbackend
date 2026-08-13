<?php

namespace App\Http\Resources\Api\V1\History;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistorySummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_transactions' => $this['total_transactions'],
            'total_sales' => $this['total_sales'],
            'cash_payment' => $this['cash_payment'],
            'qris_payment' => $this['qris_payment'],
        ];
    }
}
