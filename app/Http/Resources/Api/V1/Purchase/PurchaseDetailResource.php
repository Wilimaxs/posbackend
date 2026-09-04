<?php

namespace App\Http\Resources\Api\V1\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalQuantity = $this->items->sum('quantity');

        $totalPurchase = $this->items->sum(
            fn ($item) =>
                $item->quantity * $item->cost_price
        );

        return [
            'id' => $this->id,

            'purchase_number' =>
                $this->purchase_number,

            'purchase_date' =>
                $this->purchase_date?->format('Y-m-d'),

            'created_at' =>
                $this->created_at?->toISOString(),

            'vendor' => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'phone' => $this->vendor->phone,
                'address' => $this->vendor->address,
            ],

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],

            'vendor_reference' =>
                $this->vendor_reference,

            'notes' =>
                $this->notes,

            'items' => $this->items
                ->map(function ($item) {
                    return [
                        'product_id' =>
                            $item->product_id,

                        'product_name' =>
                            $item->product_name,

                        'sku' =>
                            $item->sku,

                        'barcode' =>
                            $item->barcode,

                        'unit' =>
                            $item->unit,

                        'quantity' =>
                            (int) $item->quantity,

                        'cost_price' =>
                            (int) $item->cost_price,

                        'subtotal' =>
                            (int) (
                                $item->quantity
                                * $item->cost_price
                            ),
                    ];
                })
                ->values(),

            'summary' => [
                'total_products' =>
                    $this->items->count(),

                'total_quantity' =>
                    (int) $totalQuantity,

                'total_purchase' =>
                    (int) $totalPurchase,
            ],
        ];
    }
}
