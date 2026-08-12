<?php

namespace App\Http\Controllers\Api\V1\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CheckoutRequest;
use App\Http\Resources\Api\V1\Checkout\CheckoutResource;
use App\Services\V1\Checkout\CheckoutService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {
    }

    public function preview(
        CheckoutRequest $request
    ): JsonResponse {
        $storeId = 1;

        $preview = $this->checkoutService->preview(
            storeId: $storeId,
            customerId: $request->validated('customer_id'),
            items: $request->validated('items'),
        );

        return ApiResponse::success(
            data: new CheckoutResource($preview),
            message: 'Rincian pembayaran berhasil dihitung.',
        );
    }
}
