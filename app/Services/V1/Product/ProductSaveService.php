<?php

namespace App\Services\V1\Product;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductSaveService
{
    public function save(
        int   $storeId,
        array $data
    ): void
    {
        $productId = $data['id'] ?? null;
        $sku = $data['sku'];
        $barcode = $data['barcode'] ?? null;

        $products = Product::query()
            ->with(['productStocks' => fn($query) => $query->where('store_id', $storeId),
            ])
            ->where(function ($query) use (
                $productId,
                $sku,
                $barcode
            ) {
                if ($productId !== null) {
                    $query->where('id', $productId);
                }

                $query->orWhere('sku', $sku);

                if ($barcode !== null) {
                    $query->orWhere('barcode', $barcode);
                }
            })->get();

        $minimumStock = $data['minimum_stock'];

        unset(
            $data['id'],
            $data['minimum_stock']
        );

        /*
         * UPDATE
         */
        if ($productId !== null) {
            $product = $products->firstWhere('id', $productId);

            if (!$product) {
                throw ValidationException::withMessages([
                    'id' => [
                        'Produk tidak ditemukan.',
                    ],
                ]);
            }

            $productStock = $product->productStocks->first();

            if (!$productStock) {
                throw ValidationException::withMessages([
                    'id' => [
                        'Produk tidak tersedia pada toko ini.',
                    ],
                ]);
            }

            $skuUsedByOtherProduct = $products->contains(
                fn(Product $item) => $item->id !== $product->id && $item->sku === $sku
            );

            if ($skuUsedByOtherProduct) {
                throw ValidationException::withMessages([
                    'sku' => [
                        'SKU sudah digunakan produk lain.',
                    ],
                ]);
            }

            if ($barcode !== null) {
                $barcodeUsedByOtherProduct = $products->contains(
                    fn(Product $item) => $item->id !== $product->id && $item->barcode === $barcode
                );

                if ($barcodeUsedByOtherProduct) {
                    throw ValidationException::withMessages([
                        'barcode' => [
                            'Barcode sudah digunakan produk lain.',
                        ],
                    ]);
                }
            }

            DB::transaction(function () use (
                $product,
                $productStock,
                $data,
                $minimumStock
            ) {
                $product->update($data);

                $productStock->update([
                    'minimum_stock' => $minimumStock,
                ]);
            });

            return;
        }

        /*
         * CREATE
         */
        if ($products->contains(
            fn(Product $item) => $item->sku === $sku
        )) {
            throw ValidationException::withMessages([
                'sku' => [
                    'SKU sudah digunakan produk lain.',
                ],
            ]);
        }

        if ($barcode !== null && $products->contains(fn(Product $item) => $item->barcode === $barcode
            )
        ) {
            throw ValidationException::withMessages([
                'barcode' => [
                    'Barcode sudah digunakan produk lain.',
                ],
            ]);
        }

        DB::transaction(function () use (
            $storeId,
            $data,
            $minimumStock
        ) {
            $product = Product::create($data);

            ProductStock::create([
                'store_id' => $storeId,
                'product_id' => $product->id,
                'discount_id' => null,
                'stock' => 0,
                'minimum_stock' => $minimumStock,
            ]);
        });
    }
}
