<?php

namespace App\Services\V1\Checkout;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;

class CheckoutPersistenceService
{
    public function createPendingSale(
        int $storeId,
        int $userId,
        ?Customer $customer,
        array $calculated,
    ): Sale {
        $sale = Sale::create([
            'store_id' => $storeId,
            'user_id' => $userId,
            'customer_id' => $customer?->id,

            'invoice_number' => null,
            'customer_type' => $calculated['customer_type'],
            'payment_method' => null,

            'initial_payment' => 0,
            'change_amount' => 0,
            'remaining_balance' => $calculated['total_after_discount'],

            'payment_status' => 'unpaid',
            'due_date' => null,

            'status' => 'pending',
            'paid_at' => null,
            'notes' => null,
        ]);

        $saleItems = [];

        foreach ($calculated['items'] as $item) {
            $product = $item['product'];
            $discount = $item['discount'];

            $saleItems[] = [
                'sale_id' => $sale->id,
                'product_id' => $product->id,

                'product_name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'unit' => $product->unit,

                'quantity' => $item['quantity'],

                'cost_price' => $product->cost_price,
                'unit_price' => $item['unit_price'],

                'price_type' =>
                    $calculated['customer_type'] === 'member'
                        ? 'grocier'
                        : 'normal',

                'discount_id' => $discount['id'] ?? null,
                'discount_name' => $discount['name'] ?? null,
                'discount_value' => $discount['value'] ?? 0,

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        SaleItem::query()->insert($saleItems);

        return $sale;
    }
}
