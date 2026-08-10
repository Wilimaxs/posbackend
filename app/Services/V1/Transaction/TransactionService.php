<?php

namespace App\Services\V1\Transaction;

use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /*
     * ============================
     * SUMMARY HARI INI
     * ============================
     */
    public function getTodaySummary(
        int $storeId
    ): array {
        /*
         * Summary SELALU hari ini.
         *
         * Tidak mengikuti filter list.
         */
        $query = Sale::query()
            ->where('store_id', $storeId)

            /*
             * Cancelled tidak masuk penjualan.
             */
            ->where('status', 'completed')

            ->whereDate(
                'created_at',
                today()
            );

        /*
         * Clone query supaya masing-masing aggregate
         * tidak merusak query yang lain.
         */
        $totalTransactions = (clone $query)
            ->count();

        $totalSales = (int) (
        (clone $query)
            ->sum('total_after_discount')
        );

        /*
         * Untuk cash, yang dianggap masuk adalah
         * nilai tagihan yang memang sudah dibayar.
         *
         * Karena flow QRIS belum selesai, sementara
         * kita gunakan total transaksi cash completed.
         */
        $cashPayment = (int) (
        (clone $query)
            ->where('payment_method', 'cash')
            ->sum('paid_amount')
        );

        return [
            'total_transactions' =>
                $totalTransactions,

            'total_sales' =>
                $totalSales,

            'cash_payment' =>
                $cashPayment,

            /*
             * QRIS sengaja 0 dahulu.
             */
            'qris_payment' =>
                0,
        ];
    }

    /*
     * ============================
     * LIST HISTORY
     * ============================
     */
    public function getTransactions(
        int $storeId,
        array $filters
    ): LengthAwarePaginator {
        $query = Sale::query()

            /*
             * Data list membutuhkan customer dan user,
             * sehingga eager load agar tidak N+1 query.
             */
            ->with([
                'customer:id,name',
                'user:id,name',
            ])

            ->where(
                'store_id',
                $storeId
            );

        /*
         * Search.
         */
        $this->applySearch(
            query: $query,
            search: $filters['search'] ?? null,
        );

        /*
         * Filter tanggal.
         *
         * Default:
         * all
         */
        $this->applyDateFilter(
            query: $query,
            filters: $filters,
        );

        /*
         * Filter Guest / Member.
         */
        if (! empty($filters['customer_type'])) {
            $query->where(
                'customer_type',
                $filters['customer_type']
            );
        }

        /*
         * Filter Cash / QRIS.
         */
        if (! empty($filters['payment_method'])) {
            $query->where(
                'payment_method',
                $filters['payment_method']
            );
        }

        /*
         * Filter status pembayaran.
         */
        if (! empty($filters['payment_status'])) {
            $query->where(
                'payment_status',
                $filters['payment_status']
            );
        }

        /*
         * Transaksi terbaru berada paling atas.
         */
        $query->orderByDesc('created_at')
            ->orderByDesc('id');

        /*
         * Default pagination = 20.
         */
        $perPage = (int) (
            $filters['per_page'] ?? 20
        );

        return $query->paginate($perPage);
    }

    /*
     * ============================
     * DETAIL
     * ============================
     */
    public function getTransactionDetail(
        int $storeId,
        string $invoiceNumber
    ): Sale {
        $sale = Sale::query()
            ->with([
                'items',
                'customer',
                'user',
                'store',
            ])

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'invoice_number',
                $invoiceNumber
            )

            ->first();

        if (! $sale) {
            throw ValidationException::withMessages([
                'invoice_number' => [
                    'Transaksi tidak ditemukan.',
                ],
            ]);
        }

        return $sale;
    }

    private function applySearch(
        Builder $query,
        ?string $search
    ): void {
        if (
            $search === null
            || trim($search) === ''
        ) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $query) use ($search) {
            /*
             * Nomor invoice.
             */
            $query->where(
                'invoice_number',
                'like',
                "%{$search}%"
            )

                /*
                 * Nama customer.
                 */
                ->orWhereHas(
                    'customer',
                    function (Builder $customerQuery) use ($search) {
                        $customerQuery->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    }
                )

                /*
                 * Nama produk dari snapshot sale_items.
                 */
                ->orWhereHas(
                    'items',
                    function (Builder $itemQuery) use ($search) {
                        $itemQuery->where(
                            'product_name',
                            'like',
                            "%{$search}%"
                        );
                    }
                );
        });
    }

    private function applyDateFilter(
        Builder $query,
        array $filters
    ): void {
        $dateFilter =
            $filters['date_filter'] ?? 'all';

        switch ($dateFilter) {
            /*
             * Default pertama masuk menu history.
             *
             * Tidak ada where tanggal.
             */
            case 'all':
                break;

            case 'today':
                $query->whereDate(
                    'created_at',
                    today()
                );
                break;

            case 'yesterday':
                $query->whereDate(
                    'created_at',
                    today()->subDay()
                );
                break;

            case 'last_7_days':
                $query->whereBetween(
                    'created_at',
                    [
                        today()
                            ->subDays(6)
                            ->startOfDay(),

                        now()
                            ->endOfDay(),
                    ]
                );
                break;

            case 'this_month':
                $query
                    ->whereYear(
                        'created_at',
                        now()->year
                    )
                    ->whereMonth(
                        'created_at',
                        now()->month
                    );
                break;

            case 'custom':
                $startDate = Carbon::parse(
                    $filters['start_date']
                )->startOfDay();

                $endDate = Carbon::parse(
                    $filters['end_date']
                )->endOfDay();

                $query->whereBetween(
                    'created_at',
                    [
                        $startDate,
                        $endDate,
                    ]
                );
                break;
        }
    }
}
