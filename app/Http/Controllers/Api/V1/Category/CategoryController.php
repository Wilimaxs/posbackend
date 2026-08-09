<?php

namespace App\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Category\CategoryResource;
use App\Services\V1\Category\CategoryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {
    }

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getList();

        return ApiResponse::success(
            data: CategoryResource::collection($categories),
            message: 'Daftar kategori berhasil diambil.',
        );
    }
}
