<?php

namespace App\Http\Resources\Api\V1\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'purchase_number' =>
                $this->purchase_number,

            'vendor' => [
                'id' => $this->vendor_id,
                'name' => $this->vendor_name,
            ],

            'purchase_date' =>
                $this->purchase_date?->format('Y-m-d'),

            'total_purchase' =>
                (int) ($this->total_purchase ?? 0),
        ];
    }
}
