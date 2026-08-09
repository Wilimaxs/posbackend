<?php

namespace App\Services\V1\Checkout;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function preview(
        int   $storeId,
        ?int  $customerId,
        array $items
    ): array
    {
        /*
         * 1. Normalize items.
         *
         * Jika mobile mengirim product_id yang sama
         * beberapa kali, backend akan menggabungkannya.
         *
         * Contoh:
         *
         * product_id 1 qty 1
         * product_id 1 qty 2
         *
         * menjadi:
         *
         * product_id 1 qty 3
         */
        $items = $this->normalizeItems($items);

        /*
         * 2. Ambil customer.
         *
         * customer_id null = Guest
         * customer_id ada  = Member
         */
        $customer = $this->getCustomer(
            storeId: $storeId,
            customerId: $customerId,
        );

        $isMember = $customer !== null;

        /*
         * 3. Ambil seluruh produk dari database.
         *
         * Sekaligus mengambil:
         * - stock toko
         * - discount
         */
        $products = $this->getProducts(
            storeId: $storeId,
            items: $items,
        );

        /*
         * 4. Hitung preview checkout.
         */
        return $this->calculatePreview(
            products: $products,
            items: $items,
            customer: $customer,
            isMember: $isMember,
        );
    }

    private function normalizeItems(array $items): array
    {
        /*
         * Gabungkan product_id yang sama.
         *
         * groupBy:
         * mengelompokkan berdasarkan product_id.
         *
         * sum:
         * menjumlahkan quantity dari product yang sama.
         */
        return collect($items)
            ->groupBy('product_id')
            ->map(function ($group, $productId) {
                return [
                    'product_id' => (int)$productId,

                    'quantity' => (int)$group->sum('quantity'),
                ];
            })
            ->values()
            ->all();
    }

    private function getCustomer(
        int  $storeId,
        ?int $customerId
    ): ?Customer
    {
        /*
         * Tidak ada customer_id berarti transaksi Guest.
         */
        if ($customerId === null) {
            return null;
        }

        $customer = Customer::query()

            // Cari customer berdasarkan ID.
            ->where('id', $customerId)

            // Customer harus berasal dari toko yang sama.
            ->where('store_id', $storeId)

            // Customer harus masih aktif.
            ->where('is_active', true)

            // Eksekusi query.
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

    private function getProducts(
        int   $storeId,
        array $items
    ): Collection
    {
        /*
         * Ambil semua product_id dari cart.
         *
         * Karena items sudah dinormalisasi,
         * sebenarnya product_id sudah unique.
         */
        $productIds = collect($items)
            ->pluck('product_id')
            ->values();

        return Product::query()
            /*
             * Ambil productStocks khusus toko aktif.
             *
             * Sekaligus eager load discount yang
             * terhubung ke product_stock.
             */
            ->with([
                'productStocks' => function ($query) use ($storeId) {
                    $query
                        ->where('store_id', $storeId)
                        ->with('discount');
                },
            ])

            // Hanya ambil produk yang ada di cart.
            ->whereIn('id', $productIds)

            // Produk harus masih aktif.
            ->where('is_active', true)
            /*
             * Pastikan produk memang tersedia
             * pada toko aktif.
             */
            ->whereHas('productStocks', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })

            // Eksekusi query.
            ->get()
            /*
             * Jadikan product_id sebagai key.
             *
             * Supaya nanti bisa:
             *
             * $products->get($productId)
             */
            ->keyBy('id');
    }

    private function calculatePreview(
        Collection $products,
        array      $items,
        ?Customer  $customer,
        bool       $isMember
    ): array
    {
        $resultItems = [];

        $totalBeforeDiscount = 0;
        $totalDiscount = 0;
        $totalAfterDiscount = 0;

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $quantity = (int)$item['quantity'];

            /*
             * Ambil produk dari collection.
             */
            $product = $products->get($productId);

            /*
             * Jika tidak ditemukan berarti:
             *
             * - produk tidak aktif
             * - produk tidak tersedia di toko
             */
            if (!$product) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Produk ID {$productId} tidak tersedia pada toko ini.",
                    ],
                ]);
            }

            /*
             * Karena productStocks sebelumnya sudah
             * difilter berdasarkan store_id,
             * cukup ambil data pertama.
             */
            $productStock = $product->productStocks->first();

            /*
             * Pastikan stock terbaru mencukupi.
             */
            if ($productStock->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Stok {$product->name} tidak mencukupi. "
                        . "Stok tersedia: {$productStock->stock}.",
                    ],
                ]);
            }

            /*
             * Tentukan harga.
             *
             * Guest:
             * selling_price_normal
             *
             * Member:
             * selling_price_grocier
             */
            $unitPrice = $this->getUnitPrice(
                product: $product,
                isMember: $isMember,
            );

            /*
             * Total harga sebelum diskon.
             *
             * Contoh:
             *
             * harga = 10.000
             * qty   = 3
             *
             * subtotal = 30.000
             */
            $lineSubtotal = $unitPrice * $quantity;

            /*
             * Hitung discount yang berlaku.
             *
             * Discount hanya diterapkan pada
             * SATU UNIT pertama.
             */
            $discountData = $this->calculateDiscount(
                productStock: $productStock,
                isMember: $isMember,
                unitPrice: $unitPrice,
            );

            $discountAmount = $discountData['amount'];

            /*
             * Harga satu unit pertama setelah diskon.
             *
             * max(0, ...)
             * memastikan harga tidak pernah minus.
             */
            $discountedUnitPrice = max(
                0,
                $unitPrice - $discountAmount
            );

            /*
             * Sisa quantity tidak mendapatkan diskon.
             *
             * Contoh:
             *
             * qty = 3
             *
             * unit pertama → diskon
             * unit kedua   → normal
             * unit ketiga  → normal
             */
            $remainingQuantity = max(
                0,
                $quantity - 1
            );

            /*
             * Total setelah diskon.
             *
             * Contoh:
             *
             * Harga     = 10.000
             * Discount  = 2.000
             * Qty       = 3
             *
             * unit 1 = 8.000
             * unit 2 = 10.000
             * unit 3 = 10.000
             *
             * total = 28.000
             */
            $lineAfterDiscount =
                $discountedUnitPrice
                + ($unitPrice * $remainingQuantity);

            /*
             * Tambahkan ke total seluruh cart.
             */
            $totalBeforeDiscount += $lineSubtotal;

            $totalDiscount += $discountAmount;

            $totalAfterDiscount += $lineAfterDiscount;

            /*
             * Data yang dikirim ke mobile.
             *
             * Total keseluruhan sengaja tidak
             * dimasukkan ke masing-masing item.
             */
            $resultItems[] = [
                'product_id' => $product->id,

                'name' => $product->name,

                'quantity' => $quantity,

                'unit_price' => $unitPrice,

                'discount' => $discountData['discount'],

                // Total harga produk setelah diskon.
                'subtotal_after_discount' => $lineAfterDiscount,
            ];
        }

        return [
            /*
             * Customer hanya dikirim jika Member.
             */
            'customer' => $customer
                ? [
                    'id' => $customer->id,

                    'customer_code' =>
                        $customer->customer_code,

                    'name' => $customer->name,

                    'phone' => $customer->phone,
                ]
                : null,

            /*
             * Supaya mobile tidak perlu menentukan
             * sendiri tipe customer.
             */
            'customer_type' => $isMember
                ? 'member'
                : 'guest',

            /*
             * Detail produk.
             */
            'items' => $resultItems,

            /*
             * Total seluruh cart sebelum diskon.
             */
            'total_before_discount' =>
                $totalBeforeDiscount,

            /*
             * Total discount seluruh cart.
             *
             * Karena satu produk hanya mendapat
             * satu kali discount, quantity tidak
             * mengalikan discount.
             */
            'total_discount' =>
                $totalDiscount,

            /*
             * Total akhir pembayaran.
             */
            'total_after_discount' =>
                $totalAfterDiscount,
        ];
    }

    private function getUnitPrice(
        Product $product,
        bool    $isMember
    ): int
    {
        /*
         * Guest selalu menggunakan harga normal.
         */
        if (!$isMember) {
            return (int)$product->selling_price_normal;
        }

        /*
         * Member menggunakan harga grosir.
         *
         * Jika selling_price_grocier null,
         * fallback ke harga normal.
         *
         * Ini untuk mencegah harga menjadi Rp0
         * karena casting null ke integer.
         */
        return (int)(
            $product->selling_price_grocier
            ?? $product->selling_price_normal
        );
    }

    private function calculateDiscount(
        $productStock,
        bool $isMember,
        int $unitPrice
    ): array
    {
        $discount = $productStock->discount;

        /*
         * Produk tidak memiliki discount.
         */
        if (!$discount) {
            return $this->emptyDiscount();
        }

        /*
         * Discount tidak aktif.
         */
        if (!$discount->is_active) {
            return $this->emptyDiscount();
        }

        /*
         * Periksa periode discount.
         *
         * Menggunakan:
         *
         * starts_date
         * ends_date
         */
        $withinDate = now()
            ->startOfDay()
            ->between(
                $discount->starts_date,
                $discount->ends_date
            );

        if (!$withinDate) {
            return $this->emptyDiscount();
        }

        /*
         * Periksa customer_scope.
         *
         * all:
         * Guest + Member
         *
         * guest:
         * hanya Guest
         *
         * member:
         * hanya Member
         */
        $scopeAllowed = match ($discount->customer_scope) {
            'all' => true,

            'member' => $isMember,

            'guest' => !$isMember,

            default => false,
        };

        if (!$scopeAllowed) {
            return $this->emptyDiscount();
        }

        /*
         * Discount hanya berlaku untuk SATU unit.
         *
         * Jika harga produk:
         *
         * Rp5.000
         *
         * tetapi discount:
         *
         * Rp10.000
         *
         * maka discount efektif hanya Rp5.000.
         *
         * Sehingga harga produk menjadi Rp0,
         * bukan minus.
         */
        $discountAmount = min(
            (int)$discount->discount_value,
            $unitPrice
        );

        return [
            /*
             * Dipakai internal backend
             * untuk perhitungan.
             */
            'amount' => $discountAmount,

            /*
             * Data yang dikirim ke mobile.
             */
            'discount' => [
                'id' => $discount->id,

                'name' => $discount->name,

                'value' => $discountAmount,
            ],
        ];
    }

    private function emptyDiscount(): array
    {
        return [
            'amount' => 0,

            'discount' => null,
        ];
    }
}
