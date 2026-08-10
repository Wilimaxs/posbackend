<?php

namespace App\Services\V1\Payment;

use App\Models\ProductStock;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 *
 * Service untuk mengelola stock produk
 *
 * @param array $items
 * @return array
 * @return Collection
 * @throws ValidationException
 */
class PaymentStockService
{
    public function lockStocks(
        int   $storeId,
        array $items
    ): Collection
    {
        $productIds = collect($items)
            ->pluck('product_id')
            ->unique()
            ->sort()
            ->values();

        /*
         * SELECT ...
         * FROM product_stocks
         * WHERE store_id = ?
         * AND product_id IN (...)
         * FOR UPDATE
         */
        $stocks = ProductStock::query()
            ->where('store_id', $storeId)
            ->whereIn('product_id', $productIds)
            ->orderBy('product_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        foreach ($productIds as $productId) {
            if (!$stocks->has($productId)) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Produk ID $productId tidak tersedia pada toko ini.",
                    ],
                ]);
            }
        }

        return $stocks;
    }

    /**
     *
     * Decrease stock produk
     *
     * @param Collection $stocks
     * @param array $items
     * @return void
     */
    public function decreaseStocks(
        Collection $stocks,
        array      $items
    ): void
    {
        foreach ($items as $item) {
            $productStock = $stocks->get(
                $item['product_id']
            );

            /**
             * @var ProductStock $productStock
             */
            $productStock->decrement(
                'stock',
                (int)$item['quantity']
            );
        }
    }
}
