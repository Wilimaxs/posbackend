<?php

namespace App\Services\V1\Purchase;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseSaveService
{
    public function save(
        int $storeId,
        int $userId,
        array $data
    ): void {
        $items = collect($data['items']);
        $productIds = $items->pluck('product_id')->all();

        /*
         * Vendor harus:
         * - milik store aktif
         * - masih aktif
         */
        $vendorExists = Vendor::query()
            ->where('id', $data['vendor_id'])
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->exists();

        if (!$vendorExists) {
            throw ValidationException::withMessages([
                'vendor_id' => [
                    'Vendor tidak ditemukan atau sudah tidak aktif.',
                ],
            ]);
        }

        /*
         * Ambil semua produk sekaligus.
         *
         * store_stock_id diambil menggunakan subquery,
         * jadi tidak perlu query ProductStock satu per satu.
         */
        $products = Product::query()
            ->select([
                'id',
                'name',
                'sku',
                'barcode',
                'unit',
                'cost_price',
            ])
            ->addSelect([
                'store_stock_id' => ProductStock::query()
                    ->select('id')
                    ->whereColumn(
                        'product_id',
                        'products.id'
                    )
                    ->where('store_id', $storeId)
                    ->limit(1),
            ])
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->whereHas(
                'productStocks',
                fn ($query) =>
                $query->where('store_id', $storeId)
            )
            ->get()
            ->keyBy('id');

        /*
         * Kalau jumlah tidak sama berarti ada produk:
         * - tidak ditemukan
         * - inactive
         * - atau tidak tersedia di store ini
         */
        if ($products->count() !== count($productIds)) {
            throw ValidationException::withMessages([
                'items' => [
                    'Terdapat produk yang tidak ditemukan, tidak aktif, atau tidak tersedia pada toko ini.',
                ],
            ]);
        }

        $purchaseNumber =
            'PUR-'.
            now()->format('Ymd').'-'.
            Str::ulid();

        DB::transaction(function () use (
            $storeId,
            $userId,
            $data,
            $items,
            $products,
            $purchaseNumber
        ) {
            /*
             * 1. Header pembelian
             */
            $purchase = Purchase::create([
                'store_id' => $storeId,
                'user_id' => $userId,
                'vendor_id' => $data['vendor_id'],
                'purchase_number' => $purchaseNumber,
                'vendor_reference' =>
                    $data['vendor_reference'],
                'purchase_date' =>
                    $data['purchase_date'],
                'notes' =>
                    $data['notes'] ?? null,
            ]);

            $now = now();
            $purchaseItems = [];

            foreach ($items as $item) {
                $product = $products[
                $item['product_id']
                ];

                /*
                 * 2. Snapshot purchase item
                 */
                $purchaseItems[] = [
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,

                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'unit' => $product->unit,

                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],

                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                /*
                 * 3. Tambah stock store
                 */
                ProductStock::query()
                    ->whereKey($product->store_stock_id)
                    ->increment(
                        'stock',
                        $item['quantity']
                    );

                /*
                 * 4. Simpan harga beli terbaru
                 */
                if (
                    (float) $product->cost_price !==
                    (float) $item['cost_price']
                ) {
                    Product::query()
                        ->whereKey($product->id)
                        ->update([
                            'cost_price' =>
                                $item['cost_price'],
                        ]);
                }
            }

            /*
             * Insert semua purchase_items sekaligus.
             */
            PurchaseItem::insert($purchaseItems);
        });
    }
}
