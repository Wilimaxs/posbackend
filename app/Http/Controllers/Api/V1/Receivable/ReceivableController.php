<?php

namespace App\Http\Controllers\Api\V1\Receivable;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Receivable\ReceivableFilterRequest;
use App\Http\Resources\Api\V1\Receivable\ReceivableDetailResource;
use App\Http\Resources\Api\V1\Receivable\ReceivableResource;
use App\Http\Resources\Api\V1\Receivable\ReceivableSummaryResource;
use App\Services\V1\Receivable\ReceivableDetailService;
use App\Services\V1\Receivable\ReceivableListService;
use App\Services\V1\Receivable\ReceivableSummaryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceivableController extends Controller
{
    public function __construct(
        private readonly ReceivableDetailService $detailService,
    )
    {
    }

    public function show(
        Request $request,
        string  $invoiceNumber
    ): JsonResponse
    {
        $storeId = 1;

        $sale =
            $this->detailService->getDetail(
                storeId: $storeId,
                invoiceNumber: $invoiceNumber,
            );

        return ApiResponse::success(
            data: ReceivableDetailResource::make(
                $sale
            )->resolve($request),

            message: 'Detail piutang berhasil diambil.',
        );
    }
}
