<?php

namespace App\Http\Resources\Api\V1\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $receivable_count = (int)$this->receivable_transactions_count;
        return [
            'id' => $this->id,
            'customer_code' => $this->customer_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'receivable' => $receivable_count > 0
                ? [
                    'transaction_count' => $receivable_count,
                    'total_remaining_balance' => (float)$this->receivable_total,
                ] : null,
        ];
    }
}
