<?php

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(function (
            Request $request,
        ): bool {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (
            ValidationException $e,
            Request             $request
        ) {
            if (!$request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                message: 'Data yang diberikan tidak valid.',
                status: 422,
                errors: $e->errors()
            );
        });

        $exceptions->render(function (
            AuthenticationException $e,
            Request                 $request
        ) {
            if (!$request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                message: 'Autentikasi diperlukan.',
                status: 401,
            );
        });

        $exceptions->render(function (
            ModelNotFoundException $e,
            Request                $request
        ) {
            if (!$request->is('api/*')) {
                return null;
            }

            return ApiResponse::error(
                message: 'Data tidak ditemukan.',
                status: 404,
            );
        });

        $exceptions->render(function (
            Throwable $e,
            Request   $request
        ) {
            if (!$request->is('api/*')) {
                return null;
            }
            if ($e instanceof HttpExceptionInterface) {
                return ApiResponse::error(
                    message: match ($e->getStatusCode()) {
                        404 => 'Endpoint tidak ditemukan.',
                        405 => 'Metode HTTP tidak diizinkan.',
                        429 => 'Terlalu banyak request.',
                        default => 'Request tidak dapat diproses.',
                    }, status: $e->getStatusCode(),
                );
            }
            return ApiResponse::error(
                message: config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan.', status: 500,
            );
        });

    })->create();
