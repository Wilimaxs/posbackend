<?php

namespace App\Services\V1\Payment;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PaymentNormalizerService $normalizerService,
        private readonly PaymentStockService      $stockService,
        private readonly PaymentValidationService $validationService,
    )
    {
    }

    /**
     * @param int $storeId
     * @param int $userId
     * @param array $data
     * @return Sale
     * @throws Throwable
     */
    public function createPayment(
        int   $storeId,
        int   $userId,
        array $data
    ): Sale
    {

        return DB::transaction(function () use (
            $storeId,
            $userId,
            $data
        ) {
            // 1. Tentukan Guest / Member.
            $customer = $this->validationService->getCustomer(
                storeId: $storeId,
                customerId: $data['customer_id'] ?? null,
            );

            $isMember =
                $customer !== null;

            // 2. Handle duplicate product.
            $items = $this->normalizerService->normalize(
                items: $data['items'],
            );

            // 3. Lock stock database.
            $stocks = $this->stockService->lockStocks(
                storeId: $storeId,
                items: $items,
            );

            // 4. Validasi stock dan tipe harga.
            $this->validationService->validateItems(
                stocks: $stocks,
                items: $items,
                isMember: $isMember,
            );

            // 5. Validasi status pembayaran/piutang.
            $this->validationService->validatePayment(
                isMember: $isMember,
                data: $data,
            );

            // 6. Simpan header transaksi.
            $sale = $this->createSale(
                storeId: $storeId,
                userId: $userId,
                customer: $customer,
                data: $data,
            );

            // 7. Simpan semua detail produk.
            $this->createSaleItems(
                sale: $sale,
                items: $items,
            );

            // 8. Kurangi stock.
            $this->stockService->decreaseStocks(
                stocks: $stocks,
                items: $items,
            );

            // 9. Return transaksi lengkap.
            return $sale->load([
                'items',
                'customer',
                'user',
                'store',
            ]);
        }, 3);
    }

    /**
     * @param int $storeId
     * @param int $userId
     * @param Customer|null $customer
     * @param array $data
     * @return Sale
     * @throws Throwable
     */
    private function createSale(
        int       $storeId,
        int       $userId,
        ?Customer $customer,
        array     $data
    ): Sale
    {
        $isMember =
            $customer !== null;

        $changeAmount = max(
            0,
            (int)$data['paid_amount'] - (int)$data['total_after_discount']
        );

        return Sale::create([
            'store_id' =>
                $storeId,

            'user_id' =>
                $userId,

            'customer_id' =>
                $customer?->id,

            'invoice_number' =>
                $this->generateInvoiceNumber(),

            'customer_type' =>
                $isMember
                    ? 'member'
                    : 'guest',

            'total_before_discount' =>
                $data['total_before_discount'],

            'total_discount' =>
                $data['total_discount'],

            'total_after_discount' =>
                $data['total_after_discount'],

            'paid_amount' =>
                $data['paid_amount'],

            'change_amount' =>
                $changeAmount,

            'remaining_balance' =>
                $data['remaining_balance'],

            'payment_status' =>
                $data['payment_status'],

            'due_date' =>
                $data['due_date'] ?? null,

            'status' =>
                'completed',

            'paid_at' =>
                $data['payment_status'] === 'paid'
                    ? now()
                    : null,

            'notes' =>
                $data['notes'] ?? null,
        ]);
    }

    /**
     * @param Sale $sale
     * @param array $items
     * @return void
     * @throws Throwable
     */
    private function createSaleItems(
        Sale  $sale,
        array $items
    ): void
    {
        foreach ($items as $item) {
            SaleItem::create([
                'sale_id' =>
                    $sale->id,

                'product_id' =>
                    $item['product_id'],

                'product_name' =>
                    $item['product_name'],

                'sku' =>
                    $item['sku'],

                'barcode' =>
                    $item['barcode'] ?? null,

                'unit' =>
                    $item['unit'],

                'quantity' =>
                    $item['quantity'],

                'cost_price' =>
                    $item['cost_price'],

                'unit_price' =>
                    $item['unit_price'],

                'price_type' =>
                    $item['price_type'],

                'subtotal' =>
                    $item['subtotal'],

                'discount_id' =>
                    $item['discount']['id'] ?? null,

                'discount_name' =>
                    $item['discount']['name'] ?? null,

                'discount_value' =>
                    $item['discount']['value'] ?? 0,

                'subtotal_after_discount' =>
                    $item['subtotal_after_discount'],
            ]);
        }
    }

    /**
     * @return string
     * @throws Throwable
     */
    private function generateInvoiceNumber(): string
    {
        return 'INV-'
            . now()->format('Ymd')
            . '-'
            . Str::upper(
                (string)Str::ulid()
            );
    }
}
