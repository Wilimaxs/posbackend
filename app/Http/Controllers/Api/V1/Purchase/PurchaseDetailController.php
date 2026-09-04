<?php

namespace App\Http\Controllers\Api\V1\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Purchase\PurchaseDetailResource;
use App\Services\V1\Purchase\PurchaseDetailService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PurchaseDetailController extends Controller
{
    public function __construct(
        private readonly PurchaseDetailService $detailService
    ) {
    }

    public function show(
        int $purchaseId
    ): JsonResponse {
        $storeId = 1;

        $purchase = $this->detailService->getDetail(
            storeId: $storeId,
            purchaseId: $purchaseId,
        );

        return ApiResponse::success(
            data: new PurchaseDetailResource(
                $purchase
            ),
            message: 'Detail pembelian berhasil diambil.',
        );
    }
}
