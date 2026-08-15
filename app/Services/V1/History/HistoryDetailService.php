<?php

namespace App\Services\V1\History;

use App\Models\Sale;
use Illuminate\Validation\ValidationException;

class HistoryDetailService
{
    public function getDetail(
        int    $storeId,
        string $invoiceNumber
    ): Sale
    {
        $sale = Sale::query()
            ->with([
                'store:id,name,address,phone',
                'user:id,name',
                'customer:id,customer_code,name,phone,address',
                'items',
                'receivablePayments' => fn($query) => $query
                    ->with('user:id,name')
                    ->orderBy('created_at'),
            ])
            ->where('store_id', $storeId)
            ->where('invoice_number', $invoiceNumber)
            ->where('status', 'completed')
            ->first();

        if (!$sale) {
            throw ValidationException::withMessages([
                'invoice_number' => [
                    'Transaksi tidak ditemukan.',
                ],
            ]);
        }

        return $sale;
    }
}
