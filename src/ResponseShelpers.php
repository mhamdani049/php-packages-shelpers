<?php
namespace Mhamdani049\Shelpers;

use Illuminate\Http\JsonResponse;

class ResponseShelpers {
    public static function generate($data = [], $message = "", $code = "00", $status = "success", $metadata = null): JsonResponse
    {
        $responseCode = ($status == "success") ? 200 : 400;
        $responseData = [
            'status' => $status,
            'code' => (string) $code,
            'message' => $message,
            'data' => $data
        ];
        if ($metadata) $responseData["metadata"] = $metadata;
        return response()->json($responseData, $responseCode);
    }
}
