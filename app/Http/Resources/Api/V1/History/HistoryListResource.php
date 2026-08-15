<?php

namespace App\Http\Resources\Api\V1\History;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sale_id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'created_at' => $this->created_at?->toISOString(),
            'customer' => $this->customer
                ? [
                    'name' => $this->customer->name,
                ] : null,
            'customer_type' => $this->customer_type,
            'total_after_discount' => (int)$this->total_after_discount,
            'payment_status' => $this->payment_status,
        ];
    }
}
