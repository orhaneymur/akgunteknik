<?php

namespace Modules\Core\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Standardized API responses for all modules.
 */
trait ApiResponse
{
    protected function respond(bool $success, mixed $data = null, ?string $message = null, mixed $errors = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected function respondSuccess(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return $this->respond(true, $data, $message, null, $status);
    }

    protected function respondError(mixed $errors = null, ?string $message = null, int $status = 400): JsonResponse
    {
        return $this->respond(false, null, $message, $errors, $status);
    }
}

