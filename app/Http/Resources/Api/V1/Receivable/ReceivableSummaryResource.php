<?php

namespace App\Http\Resources\Api\V1\Receivable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_active' => [
                'amount' =>
                    (int) $this['total_active']['amount'],

                'count' =>
                    (int) $this['total_active']['count'],
            ],

            'due_today' => [
                'amount' =>
                    (int) $this['due_today']['amount'],

                'count' =>
                    (int) $this['due_today']['count'],
            ],

            'overdue' => [
                'amount' =>
                    (int) $this['overdue']['amount'],

                'count' =>
                    (int) $this['overdue']['count'],
            ],

            'payments_today' => [
                'amount' =>
                    (int) $this['payments_today']['amount'],

                'count' =>
                    (int) $this['payments_today']['count'],
            ],
        ];
    }
}
