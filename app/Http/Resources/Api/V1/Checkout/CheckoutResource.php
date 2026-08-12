<?php

namespace App\Http\Resources\Api\V1\Checkout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $customer = $this['customer'];

        return [
            'customer' => $customer
                ? [
                    'id' => $customer->id,
                    'customer_code' => $customer->customer_code,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                ]
                : null,

            'customer_type' => $this['customer_type'],

            'items' => collect($this['items'])
                ->map(function (array $item) {
                    $product = $item['product'];

                    return [
                        'product_id' => $product->id,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'name' => $product->name,

                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],

                        'discount' => $item['discount'],

                        'subtotal' => $item['subtotal'],
                        'subtotal_after_discount' =>
                            $item['subtotal_after_discount'],
                    ];
                })
                ->values()
                ->all(),

            'total_before_discount' =>
                $this['total_before_discount'],

            'total_discount' =>
                $this['total_discount'],

            'total_after_discount' =>
                $this['total_after_discount'],
        ];
    }
}
