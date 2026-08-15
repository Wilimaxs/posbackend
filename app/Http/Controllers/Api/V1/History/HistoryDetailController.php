<?php

namespace App\Http\Controllers\Api\V1\History;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\History\HistoryDetailResource;
use App\Services\V1\History\HistoryDetailService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HistoryDetailController extends Controller
{
    public function __construct(
        private readonly HistoryDetailService $detailService
    ) {
    }

    public function show(
        string $invoiceNumber
    ): JsonResponse {
        $storeId = 1;

        $sale = $this->detailService->getDetail(
            storeId: $storeId,
            invoiceNumber: $invoiceNumber,
        );

        return ApiResponse::success(
            data: new HistoryDetailResource($sale),
            message: 'Detail transaksi berhasil diambil.',
        );
    }
}
