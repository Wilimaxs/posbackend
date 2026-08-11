<?php

namespace App\Http\Resources\Api\V1\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $invoice_number
 * @property mixed $customer
 * @property mixed $due_date
 * @property mixed $remaining_balance
 */
class ReceivableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'invoice_number' =>
                $this->invoice_number,

            'customer' => [
                'name' =>
                    $this->customer?->name,
            ],

            'due_date' =>
                $this->due_date?->format('Y-m-d'),

            'due_status' =>
                $this->resolveDueStatus(),

            'remaining_balance' =>
                (int)$this->remaining_balance,
        ];
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
