<?php

namespace App\Http\Controllers\Api\V1\Receivable;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Receivable\ReceivableDetailResource;
use App\Services\V1\Receivable\ReceivableDetailService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReceivableDetailController extends Controller
{
    public function __construct(
        private readonly ReceivableDetailService $detailService
    )
    {
    }

    public function show(int $saleId): JsonResponse
    {
        $storeId = 1;

        $sale = $this->detailService->getDetail(
            storeId: $storeId,
            saleId: $saleId,
        );

        return ApiResponse::success(
            data: new ReceivableDetailResource($sale),
            message: 'Detail piutang berhasil diambil.',
        );
    }
}
