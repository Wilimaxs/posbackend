<?php

namespace App\Http\Controllers\Api\V1\History;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\History\HistoryListRequest;
use App\Http\Resources\Api\V1\History\HistoryListResource;
use App\Services\V1\History\HistoryListService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HistoryListController extends Controller
{
    public function __construct(
        private readonly HistoryListService $listService
    )
    {
    }

    public function index(
        HistoryListRequest $request
    ): JsonResponse
    {
        $storeId = 1;

        $history = $this->listService->getList(
            storeId: $storeId,
            filters: $request->validated(),
        );

        return ApiResponse::success(
            data: HistoryListResource::collection($history->items()),
            message: 'Riwayat transaksi berhasil diambil.',
            meta: [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        );
    }
}
