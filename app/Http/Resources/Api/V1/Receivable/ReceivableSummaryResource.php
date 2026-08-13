<?php

namespace App\Http\Resources\Api\V1\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_active' => $this['total_active'],
            'due_today' => $this['due_today'],
            'overdue' => $this['overdue'],
            'payments_today' => $this['payments_today'],
        ];
    }
}
