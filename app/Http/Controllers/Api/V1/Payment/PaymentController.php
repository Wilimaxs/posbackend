<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\PaymentRequest;
use App\Http\Resources\Api\V1\Payment\PaymentResource;
use App\Services\V1\Payment\PaymentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    )
    {
    }

    public function store(
        PaymentRequest $request
    ): JsonResponse
    {
        /*
         * Sementara login belum dipakai.
         *
         * Nanti:
         * storeId dan userId berasal dari user login.
         */
        $storeId = 1;
        $userId = 1;

        $sale = $this->paymentService->createPayment(
            storeId: $storeId,
            userId: $userId,
            data: $request->validated(),
        );

        return ApiResponse::success(
            data: PaymentResource::make($sale)->resolve($request),
            message: 'Pembayaran berhasil diproses.',
            status: 201,
        );
    }
}
