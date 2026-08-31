<?php

namespace App\Services\V1\Vendor;

use App\Models\Vendor;
use Illuminate\Validation\ValidationException;

class VendorSaveService
{
    public function save(
        int $storeId,
        array $data
    ): void {
        $vendorId = $data['id'] ?? null;
        $phone = $data['phone'];

        $vendors = Vendor::query()
            ->where('store_id', $storeId)
            ->where(function ($query) use ($vendorId, $phone) {
                if ($vendorId !== null) {
                    $query
                        ->where('id', $vendorId)
                        ->orWhere('phone', $phone);

                    return;
                }

                $query->where('phone', $phone);
            })
            ->get();

        unset($data['id']);

        /*
         * UPDATE
         */
        if ($vendorId !== null) {
            $vendor = $vendors->firstWhere(
                'id',
                $vendorId
            );

            if (!$vendor) {
                throw ValidationException::withMessages([
                    'id' => [
                        'Vendor tidak ditemukan pada toko ini.',
                    ],
                ]);
            }

            $phoneUsedByOtherVendor = $vendors->contains(
                fn (Vendor $item) =>
                    $item->id !== $vendor->id
                    && $item->phone === $phone
            );

            if ($phoneUsedByOtherVendor) {
                throw ValidationException::withMessages([
                    'phone' => [
                        'Nomor telepon sudah digunakan vendor lain.',
                    ],
                ]);
            }

            $vendor->update($data);

            return;
        }

        /*
         * CREATE
         */
        if ($vendors->isNotEmpty()) {
            throw ValidationException::withMessages([
                'phone' => [
                    'Nomor telepon sudah digunakan vendor lain.',
                ],
            ]);
        }

        Vendor::create([
            'store_id' => $storeId,
            ...$data,
        ]);
    }
}
