<?php

namespace App\Services\V1\Checkout;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CheckoutPreparationService
{
    public function getCustomer(
        int  $storeId,
        ?int $customerId
    ): ?Customer
    {
        $customer = Customer::query()
            ->where('id', $customerId)
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'customer_id' => [
                    'Member tidak ditemukan atau tidak aktif pada toko ini.',
                ],
            ]);
        }
        return $customer;
    }

    public function getProducts(
        int    $storeId,
        array  $items,
        string $customerScope,
    ): Collection
    {
        $productIds = collect($items)->pluck('product_id');

        return Product::query()
            ->with([
                'productStocks' => function ($query) use ($customerScope, $storeId) {
                    $query
                        ->where('store_id', $storeId)
                        ->with(['discount' => function ($query) use ($customerScope) {
                            $query
                                ->where('is_active', true)
                                ->where('starts_date', '<=', now())
                                ->where('ends_date', '>=', now())
                                ->whereIn('customer_scope', ['all', $customerScope]);
                        }
                        ]);
                }
            ])
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->whereHas('productStocks', fn($query) => $query
                ->where('store_id', $storeId)
            )
            ->get()
            ->keyBy('id');
    }
}
