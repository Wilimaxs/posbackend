<?php

namespace App\Services\V1\Receivable;

use App\Models\Sale;
use Illuminate\Validation\ValidationException;

class ReceivableDetailService
{
    public function getDetail(
        int    $storeId,
        string $invoiceNumber
    ): Sale
    {
        $sale = Sale::query()
            ->with([
                'customer',
                'user',
                'store',
                'items',
                'receivablePayments' => function ($query) {
                    $query
                        ->with('user:id,name')
                        ->orderBy('paid_at');
                },
            ])
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'invoice_number',
                $invoiceNumber
            )
            ->whereNotNull('due_date')
            ->first();
        if (!$sale) {
            throw ValidationException::withMessages([
                'invoice_number' => [
                    'Data piutang tidak ditemukan.',
                ],
            ]);
        }
        return $sale;
    }
}
