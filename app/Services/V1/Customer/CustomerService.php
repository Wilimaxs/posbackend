<?php

namespace App\Services\V1\Customer;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function getList(
        int $storeId,
        ?string $search = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return Customer::query()

            // Ambil hanya customer/member dari toko aktif.
            ->where('store_id', $storeId)

            // Hanya member yang masih aktif.
            ->where('is_active', true)

            // Kalau ada pencarian, cari berdasarkan nama,
            // nomor telepon, atau kode member.
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('customer_code', 'like', "%$search%");
                });
            })

            // Urutkan berdasarkan nama.
            ->orderBy('name')

            // Eksekusi query sekaligus pagination.
            ->paginate($perPage);
    }
}
