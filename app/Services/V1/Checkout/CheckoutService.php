<?php

namespace App\Services\V1\Checkout;

class CheckoutService
{
    public function __construct(
        private readonly preparationService $dataService,
        private readonly CheckoutCalculator $calculator,
    )
    {
    }

    public function preview(
        int   $storeId,
        ?int  $customerId,
        array $items
    ): array
    {
        $items = $this->normalizeItems($items);

        $customer = null;
        $customerScope = 'guest';
        $priceColumn = 'selling_price_normal';

        if ($customerId !== null) {
            $customer = $this->dataService->getCustomer(
                storeId: $storeId,
                customerId: $customerId,
            );
            $customerScope = 'member';
            $priceColumn = 'selling_price_grocier';
        }

        $products = $this->dataService->prepareProducts(
            storeId: $storeId,
            items: $items,
            customerScope: $customerScope,
        );

        return $this->calculator->calculate(
            customer: $customer,
            customerType: $customerScope,
            priceColumn: $priceColumn,
            items: $items,
            products: $products,
        );
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
