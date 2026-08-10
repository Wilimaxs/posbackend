<?php

namespace App\Services\V1\Payment;

use App\Models\Customer;
use App\Models\ProductStock;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/*
 * Service untuk melakukan validasi data yang dikirim dari mobile.
 */

class PaymentValidationService
{
    public function getCustomer(
        int  $storeId,
        ?int $customerId
    ): ?Customer
    {
        if ($customerId === null) {
            return null;
        }

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

    public function validateItems(
        Collection $stocks,
        array      $items,
        bool       $isMember
    ): void
    {
        $expectedPriceType =
            $isMember
                ? 'grocier'
                : 'normal';

        foreach ($items as $item) {
            $productId =
                (int)$item['product_id'];

            $quantity =
                (int)$item['quantity'];

            $productStock =
                $stocks->get($productId);

            /** @var ProductStock $productStock */
            if ($productStock->stock < $quantity) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Stok {$item['product_name']} tidak mencukupi. "
                        . "Stok tersedia: $productStock->stock.",
                    ],
                ]);
            }
            if (
                $item['price_type']
                !== $expectedPriceType
            ) {
                throw ValidationException::withMessages([
                    'items' => [
                        "price_type untuk {$item['product_name']} "
                        . 'tidak sesuai tipe customer.',
                    ],
                ]);
            }
        }
    }

    public function validatePayment(
        bool  $isMember,
        array $data
    ): void
    {
        $paymentStatus =
            $data['payment_status'];

        $remainingBalance =
            (int)$data['remaining_balance'];

        /*
         * Guest wajib lunas.
         */
        if (
            !$isMember
            && (
                $paymentStatus !== 'paid'
                || $remainingBalance > 0
            )
        ) {
            throw ValidationException::withMessages([
                'payment_status' => [
                    'Guest harus melakukan pembayaran penuh.',
                ],
            ]);
        }

        /*
         * Jika ada piutang, due_date wajib tersedia.
         */
        if (
            $remainingBalance > 0
            && empty($data['due_date'])
        ) {
            throw ValidationException::withMessages([
                'due_date' => [
                    'Tanggal jatuh tempo wajib diisi untuk piutang.',
                ],
            ]);
        }

        /*
         * Status paid harus memiliki
         * remaining_balance = 0.
         *
         * Ini validasi konsistensi,
         * bukan menghitung nominal.
         */
        if (
            $paymentStatus === 'paid'
            && $remainingBalance !== 0
        ) {
            throw ValidationException::withMessages([
                'remaining_balance' => [
                    'remaining_balance harus 0 jika payment_status paid.',
                ],
            ]);
        }

        /*
         * Partial / unpaid harus mempunyai sisa tagihan.
         */
        if (
            in_array(
                $paymentStatus,
                ['partial', 'unpaid'],
                true
            )
            && $remainingBalance === 0
        ) {
            throw ValidationException::withMessages([
                'remaining_balance' => [
                    'Transaksi partial/unpaid harus memiliki sisa tagihan.',
                ],
            ]);
        }

        /*
         * Jika tidak ada sisa tagihan,
         * status harus paid.
         */
        if (
            $remainingBalance === 0
            && $paymentStatus !== 'paid'
        ) {
            throw ValidationException::withMessages([
                'payment_status' => [
                    'payment_status harus paid jika tidak ada sisa tagihan.',
                ],
            ]);
        }
    }
}
