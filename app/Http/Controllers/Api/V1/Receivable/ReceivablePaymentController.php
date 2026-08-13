<?php

namespace App\Http\Controllers\Api\V1\Receivable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Receivable\ReceivablePaymentRequest;
use App\Http\Resources\Api\V1\Receivable\ReceivablePaymentResource;
use App\Services\V1\Receivable\ReceivablePaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReceivablePaymentController extends Controller
{
    public function __construct(
        private readonly ReceivablePaymentService $paymentService
    ) {
    }

    public function store(
        ReceivablePaymentRequest $request,
        int $saleId,
    ): JsonResponse {
        $storeId = 1;
        $userId = 1;

        $payment = $this->paymentService->create(
            storeId: $storeId,
            userId: $userId,
            saleId: $saleId,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: new ReceivablePaymentResource($payment),
            message: 'Pembayaran piutang berhasil diterima.',
        );
    }
}
