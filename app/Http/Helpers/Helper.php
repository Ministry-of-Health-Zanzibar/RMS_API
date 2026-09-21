<?php

namespace App\Http\Helpers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Throwable;

class Helper
{
    public static function sendError($message, $errors = [])
    {
        $response = ['success' => false, 'message' => $message, 'statusCode' => 401];
        if (! empty($errors)) {
            $response['data'] = $errors;
        }

        throw new HttpResponseException(response()->json($response, 401));
    }

    public static function serverError(Throwable $exception, string $message = 'Internal Server Error'): JsonResponse
    {
        report($exception);

        return response()->json([
            'message' => $message,
            'error' => 'An unexpected error occurred.',
            'statusCode' => 500,
        ], 500);
    }
}
