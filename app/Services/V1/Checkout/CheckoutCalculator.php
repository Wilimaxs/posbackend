<?php

namespace App\Services\V1\Checkout;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CheckoutCalculator
{
    public function calculate(
        ?Customer  $customer,
        string     $customerType,
        string     $priceType,
        array      $items,
        Collection $products,
    ): array
    {
        $resultItems = [];

        $totalBeforeDiscount = 0;
        $totalDiscount = 0;

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];

            $product = $products->get($productId);

            if (!$product) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Produk ID $productId tidak tersedia pada toko ini.",
                    ],
                ]);
            }
            /** @var Product|null $product */
            $stock = $product->productStocks->first();

            if ($stock->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Stok $product->name tidak mencukupi. "
                        . "Stok tersedia: $stock->stock.",
                    ],
                ]);
            }

            $unitPrice = (int)$product->{
            'selling_price_' . $priceType
            };

            $discount = $stock->discount;

            $discountValue = $discount
                ? min(
                    (int)$discount->discount_value,
                    $unitPrice
                )
                : 0;

            $subtotal = $unitPrice * $quantity;

            $totalBeforeDiscount += $subtotal;
            $totalDiscount += $discountValue;

            $resultItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,

                'discount' => $discount
                    ? [
                        'id' => $discount->id,
                        'name' => $discount->name,
                        'value' => $discountValue,
                    ]
                    : null,

                'subtotal' => $subtotal,

                'subtotal_after_discount' =>
                    $subtotal - $discountValue,
            ];
        }

        return [
            'customer' => $customer,
            'customer_type' => $customerType,

            'items' => $resultItems,

            'total_before_discount' =>
                $totalBeforeDiscount,

            'total_discount' =>
                $totalDiscount,

            'total_after_discount' =>
                $totalBeforeDiscount - $totalDiscount,
        ];
    }
}
