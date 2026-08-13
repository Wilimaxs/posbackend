<?php

namespace App\Http\Controllers\Api\V1\Receivable;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Receivable\ReceivableSummaryResource;
use App\Services\V1\Receivable\ReceivableSummaryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReceivableSummaryController extends Controller
{
    public function __construct(
        private readonly ReceivableSummaryService $summaryService,
    ) {
    }

    public function summary(): JsonResponse
    {
        $storeId = 1;

        $summary = $this->summaryService->getSummary(
            storeId: $storeId,
        );

        return ApiResponse::success(
            data: new ReceivableSummaryResource($summary),
            message: 'Ringkasan piutang berhasil diambil.',
        );
    }
}
