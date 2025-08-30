<?php

namespace Modules\Api\Support;

use Illuminate\Support\Facades\Response;

final class ApiResponse
{
    public static function boot(): void
    {
        Response::macro('api', static function (mixed $data = null, int $status = 200, array $meta = []) {
            return response()->json([
                'data' => $data,
                'meta' => $meta,
            ], $status);
        });

        Response::macro('apiError', static function (string $message, int $status = 400, array $errors = []) {
            return response()->json([
                'error' => [
                    'message' => $message,
                    'errors' => $errors,
                ],
            ], $status);
        });
    }
}
