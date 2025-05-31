<?php

namespace App\Traits;

use App\DTOs\ResponseHeader;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{

    /**
     * Response Success dengan data.
     */
    protected function successResponse($data = null, string $message = 'Success', int $responseCode = 200000, int $httpStatus = 200): JsonResponse
    {

        $responseHeader = new ResponseHeader($responseCode, true, $message );
        $jsonData = [
            "response_header" => $responseHeader->toArray(),
        ];
        if($data) {
            $jsonData["data"] = $data;
        }
        return response()->json($jsonData, $httpStatus);
    }

    /**
     * Response error.
     */
    protected function errorResponse($additionalData = null, $message = 'internal server error', int $responseCode = 500000, $httpStatus = 500, $error = []): JsonResponse
    {
        $responseHeader = new ResponseHeader($responseCode, false, $message, $error );
        $jsonData = [
            "response_header" => $responseHeader->toArray(),
        ];
        if($additionalData) {
            $jsonData["data"] = $additionalData;
        }
        return response()->json($jsonData, $httpStatus);
    }
}
