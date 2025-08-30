<?php

namespace Modules\Api\Support\Exceptions\Handlers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\Api\Support\Exceptions\ApiException;
use Throwable;

final class ExceptionMapper
{
    public static function toResponse(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->apiError('Validation failed', 422, $e->errors());
        }
        if ($e instanceof AuthenticationException) {
            return response()->apiError('Unauthenticated', 401);
        }
        if ($e instanceof ApiException) {
            return response()->apiError($e->getMessage(), $e->status, $e->errors);
        }

        return response()->apiError('Server error', 500);
    }
}
