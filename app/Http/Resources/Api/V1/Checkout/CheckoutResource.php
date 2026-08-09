<?php

namespace App\Http\Resources\Api\V1\Checkout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'customer' => $this['customer'],
            'customer_type' => $this['customer_type'],
            'items' => $this['items'],
            'total_before_discount' => $this['total_before_discount'],
            'total_discount' => $this['total_discount'],
            'total_after_discount' => $this['total_after_discount'],
        ];
    }
}
