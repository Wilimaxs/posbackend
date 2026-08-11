<?php

namespace App\Http\Controllers\Api\V1\Receivable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Receivable\ReceivablePaymentRequest;
use App\Http\Resources\Api\V1\Receivable\ReceivableDetailResource;
use App\Services\V1\Receivable\ReceivablePaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReceivablePaymentController extends Controller
{
    public function __construct(
        private readonly ReceivablePaymentService $paymentService
    )
    {
    }

    public function store(
        ReceivablePaymentRequest $request,
        string                   $invoiceNumber
    ): JsonResponse
    {
        $storeId = 1;
        $userId = 1;

        $validated =
            $request->validated();

        $sale =
            $this->paymentService->pay(
                storeId: $storeId,
                userId: $userId,
                invoiceNumber: $invoiceNumber,
                amount: (int)$validated['amount'],
                notes: $validated['notes'] ?? null,
            );
        return ApiResponse::success(
            data: ReceivableDetailResource::make(
                $sale
            )->resolve($request),

            message: 'Pembayaran piutang berhasil diproses.',

            status: 201,
        );
    }
}
