<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Kembalikan response sukses standar.
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Berhasil.',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Kembalikan response error standar.
     */
    protected function errorResponse(
        string $message = 'Terjadi kesalahan.',
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }

    /**
     * Kembalikan response 404 Not Found.
     */
    protected function notFoundResponse(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    /**
     * Kembalikan response 403 Forbidden.
     */
    protected function forbiddenResponse(string $message = 'Akses ditolak.'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }
}
