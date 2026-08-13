<?php

namespace App\Services\V1\Checkout;

use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private readonly checkoutPreparationService $dataService,
        private readonly CheckoutCalculator         $calculator,
        private readonly CheckoutPersistenceService $persistenceService,
    )
    {
    }

    public function preview(
        int   $storeId,
        ?int  $customerId,
        array $items,
        int   $userId,
    ): Sale
    {
        $items = $this->normalizeItems($items);
        return DB::transaction(function () use (
            $storeId,
            $userId,
            $customerId,
            $items,
        ): Sale {

            // kunci user yang sedang preview transaksi
            User::query()
                ->where('id', $userId)
                ->where('store_id', $storeId)
                ->lockForUpdate()
                ->firstOrFail();

            // kunci transaksi preview
            $pendingSale = Sale::query()
                ->where('store_id', $storeId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->with(['customer', 'items'])
                ->first();

            // jika transaksi preview sudah ada, gunakan transaksi tersebut
            if ($pendingSale) {
                return $pendingSale;
            }

            $customer = null;
            $customerScope = 'guest';
            $priceType = 'normal';

            if ($customerId !== null) {
                $customer = $this->dataService->getCustomer(
                    storeId: $storeId,
                    customerId: $customerId,
                );
                $customerScope = 'member';
                $priceType = 'normal';
            }

            $products = $this->dataService->getProducts(
                storeId: $storeId,
                items: $items,
                customerScope: $customerScope,
            );

            $productIds = collect($items)
                ->pluck('product_id')
                ->sort()
                ->values();

            $stocks = ProductStock::query()
                ->where('store_id', $storeId)
                ->whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $calculated = $this->calculator->calculate(
                customer: $customer,
                customerType: $customerScope,
                priceType: $priceType,
                items: $items,
                products: $products,
            );

            $sale = $this->persistenceService->createPendingSale(
                storeId: $storeId,
                userId: $userId,
                customer: $customer,
                calculated: $calculated,
            );

            foreach ($items as $item) {
                $stock = $stocks->get(
                    (int)$item['product_id']
                );

                $stock->decrement(
                    'stock',
                    (int)$item['quantity']
                );
            }

            return $sale->load([
                'customer',
                'items',
            ]);
        }, 3);
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)
            ->groupBy('product_id')
            ->map(fn($group, $productId) => [
                'product_id' => (int)$productId,
                'quantity' => (int)$group->sum('quantity'),
            ])
            ->values()
            ->all();
    }
}
