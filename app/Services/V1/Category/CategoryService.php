<?php

namespace App\Services\V1\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function getList(): Collection
    {
        return Category::query()
            // Hanya kategori yang masih aktif.
            ->where('is_active', true)

            // Urutkan berdasarkan nama supaya button di mobile konsisten.
            ->orderBy('name')

            // Eksekusi query dan ambil semua hasil.
            ->get();
    }
}
