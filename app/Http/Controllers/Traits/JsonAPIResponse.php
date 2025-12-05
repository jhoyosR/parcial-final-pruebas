<?php
namespace App\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

/**
 * Manejador de respuestas JSON para las peticiones del API
 */
trait JsonAPIResponse {

    /**
     * Envia mensaje exitoso
     */
    public function successResponse($message, $result = null): JsonResponse {
        return Response::json([
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ], 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'charset' => 'utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Envia mensaje de error con datos
     */
    public function errorResponse($errorMsg, $result = null, $code = 404): JsonResponse {
        return Response::json([
            'success' => false,
            'data'    => $result,
            'message' => $errorMsg,
        ], $code, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'charset' => 'utf-8'
        ], JSON_UNESCAPED_UNICODE);
    }

}