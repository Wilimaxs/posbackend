<?php

namespace App\Http\Controllers\Api\V1\History;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\History\HistorySummaryResource;
use App\Services\V1\History\HistorySummaryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HistorySummaryController extends Controller
{
    public function __construct(
        private readonly HistorySummaryService $summaryService
    ) {
    }

    public function index(): JsonResponse
    {
        $storeId = 1;

        $summary = $this->summaryService->getTodaySummary(
            storeId: $storeId,
        );

        return ApiResponse::success(
            data: new HistorySummaryResource($summary),
            message: 'Ringkasan transaksi hari ini berhasil diambil.',
        );
    }
}
