<?php

namespace App\Http\Controllers\Api\V1\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\TransactionFilterRequest;
use App\Http\Resources\Api\V1\Payment\PaymentResource;
use App\Http\Resources\Api\V1\Transaction\TransactionResource;
use App\Http\Resources\Api\V1\Transaction\TransactionSummaryResource;
use App\Services\V1\Transaction\TransactionService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {
    }

    /*
     * GET /api/v1/transactions/summary
     *
     * Summary selalu hari ini.
     */
    public function summary(
        Request $request
    ): JsonResponse {
        // Sementara belum login.
        $storeId = 1;

        $summary =
            $this->transactionService->getTodaySummary(
                storeId: $storeId
            );

        return ApiResponse::success(
            data: TransactionSummaryResource::make(
                $summary
            )->resolve($request),

            message:
            'Ringkasan transaksi hari ini berhasil diambil.',
        );
    }

    /*
     * GET /api/v1/transactions
     */
    public function index(
        TransactionFilterRequest $request
    ): JsonResponse {
        // Sementara belum login.
        $storeId = 1;

        $transactions =
            $this->transactionService->getTransactions(
                storeId: $storeId,
                filters: $request->validated(),
            );

        /*
         * Hanya transform data pagination.
         */
        $data = TransactionResource::collection(
            $transactions->getCollection()
        )->resolve($request);

        return ApiResponse::success(
            data: $data,

            message:
            'Riwayat transaksi berhasil diambil.',

            meta: [
                'current_page' =>
                    $transactions->currentPage(),

                'last_page' =>
                    $transactions->lastPage(),

                'per_page' =>
                    $transactions->perPage(),

                'total' =>
                    $transactions->total(),
            ],
        );
    }

    public function show(
        Request $request,
        string $invoiceNumber
    ): JsonResponse {
        // Sementara belum login.
        $storeId = 1;

        $sale =
            $this->transactionService
                ->getTransactionDetail(
                    storeId: $storeId,
                    invoiceNumber: $invoiceNumber,
                );

        return ApiResponse::success(
            data: PaymentResource::make(
                $sale
            )->resolve($request),

            message:
            'Detail transaksi berhasil diambil.',
        );
    }
}
