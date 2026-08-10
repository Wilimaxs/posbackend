<?php

namespace App\Services\V1\Payment;

use Illuminate\Validation\ValidationException;

class PaymentNormalizerService
{
    /**
     *
     * Normalisasi data yang dikirim dari mobile.
     *
     * @param array $items
     * @return array
     */
    public function normalize(array $items): array
    {
        return collect($items)
            ->groupBy('product_id')
            ->map(function ($group, $productId) {
                $first = $group->first();

                if ($group->count() === 1) {
                    return $first;
                }

                foreach ($group as $item) {
                    $this->validateSameSnapshot(
                        productId: (int)$productId,
                        first: $first,
                        current: $item,
                    );
                }

                return [
                    'product_id' => (int)$productId,

                    'product_name' =>
                        $first['product_name'],

                    'sku' =>
                        $first['sku'],

                    'barcode' =>
                        $first['barcode'] ?? null,

                    'unit' =>
                        $first['unit'],

                    'quantity' =>
                        (int)$group->sum('quantity'),

                    'cost_price' =>
                        (int)$first['cost_price'],

                    'unit_price' =>
                        (int)$first['unit_price'],

                    'price_type' =>
                        $first['price_type'],

                    'subtotal' =>
                        (int)$group->sum('subtotal'),

                    'discount' =>
                        $first['discount'] ?? null,

                    'subtotal_after_discount' =>
                        (int)$group->sum(
                            'subtotal_after_discount'
                        ),
                ];
            })
            ->values()
            ->all();
    }

    /**
     *
     * Memvalidasi apakah snapshot dari product yang sama sama dengan snapshot yang dikirim dari mobile.
     *
     * @param int $productId
     * @param array $first
     * @param array $current
     * @return void
     */
    private function validateSameSnapshot(
        int   $productId,
        array $first,
        array $current
    ): void
    {
        if (
            (int)$current['cost_price']
            !== (int)$first['cost_price']
        ) {
            throw ValidationException::withMessages([
                'items' => [
                    "Produk ID $productId dikirim lebih dari sekali "
                    . 'dengan cost_price berbeda.',
                ],
            ]);
        }

        if (
            (int)$current['unit_price']
            !== (int)$first['unit_price']
        ) {
            throw ValidationException::withMessages([
                'items' => [
                    "Produk ID $productId dikirim lebih dari sekali "
                    . 'dengan unit_price berbeda.',
                ],
            ]);
        }

        if (
            $current['price_type']
            !== $first['price_type']
        ) {
            throw ValidationException::withMessages([
                'items' => [
                    "Produk ID $productId dikirim lebih dari sekali "
                    . 'dengan price_type berbeda.',
                ],
            ]);
        }

        $firstDiscountId =
            $first['discount']['id'] ?? null;

        $currentDiscountId =
            $current['discount']['id'] ?? null;

        if ($firstDiscountId !== $currentDiscountId) {
            throw ValidationException::withMessages([
                'items' => [
                    "Produk ID $productId dikirim lebih dari sekali "
                    . 'dengan discount berbeda.',
                ],
            ]);
        }
    }
}
