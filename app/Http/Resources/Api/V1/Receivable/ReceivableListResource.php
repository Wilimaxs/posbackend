<?php

namespace App\Http\Resources\Api\V1\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sale_id' => $this->id,

            'invoice_number' =>
                $this->invoice_number,

            'customer' => [
                'id' => $this->customer->id,
                'customer_code' =>
                    $this->customer->customer_code,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ],

            'remaining_balance' =>
                (int) $this->remaining_balance,

            'due_date' =>
                $this->due_date?->format('Y-m-d'),

            'due_status' =>
                $this->getDueStatus(),

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
