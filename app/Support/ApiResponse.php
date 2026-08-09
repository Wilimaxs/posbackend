<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed  $data = null,
        string $message = 'Request berhasil',
        int    $status = 200,
        mixed  $meta = null
    ): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'errors' => null,
        ], $status);
    }

    public static function error(
        string $message = 'Terjadi kesalahan',
        int    $status = 400,
        mixed  $errors = null,
    ): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => null,
            'errors' => $errors,
        ], $status);
    }
}
