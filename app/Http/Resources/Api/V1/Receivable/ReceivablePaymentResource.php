<?php

namespace App\Http\Resources\Api\V1\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivablePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,

            'amount' => (int)$this->amount,

            'notes' => $this->notes,

            'created_at' =>
                $this->created_at?->toISOString(),
        ];
    }
}
