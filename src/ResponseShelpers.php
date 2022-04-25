<?php
namespace Myhamdani\Shelpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class ResponseShelpers {
    public static function generate($data = [], $message = "", $code = "00", $status = "success", $metadata = null): JsonResponse
    {
        $responseCode = ($status == "success") ? 200 : 400;
        if ($status == "unauthorized") $responseCode = 401;
        $responseData = [
            'STATUS' => $status,
            'CODE' => (string) $code,
            'MESSAGE' => $message,
            'DATA' => $data
        ];
        if ($metadata) $responseData["METADATA"] = $metadata;

        return response()->json($responseData, $responseCode);
    }

    public static function grsp($exec = null, $paramsExec = [], $rawResult)
    {
        $response = null;

        if (count($rawResult) > 1) {
            $response = $rawResult;

            Log::info("SP : " . $exec . ' - Params: ' . json_encode($paramsExec) . ' - Response: success - ' . json_encode($response));
            return $response;
        }

        foreach($rawResult as $x) {
            if (is_object($x)) {
                if (isset($x->STATUS) || isset($x->USERNAME)) {
                    $response = $x;
                }
                else {
                    foreach($x as $b) {
                        try {
                            $response = json_decode($b)[0];
                        } catch (\Throwable $th) {
                            $response = $rawResult;
                        }
                    }
                }
            } else {
                $response = $x;
            }
        }

        if (!$response) {
            $responseJson = response()->json([
                'STATUS' => 'FALSE',
                'CODE' => '10',
                'MESSAGE' => Lang::get("global.somethingWentWrong"),
                'DATA' => null
            ], 400);

            Log::info("SP : " . $exec . ' - Params: ' . json_encode($paramsExec) . ' - Response: error - ' . json_encode($responseJson));
            return $responseJson;
        }

        Log::info("SP : " . $exec . ' - Params: ' . json_encode($paramsExec) . ' - Response: success - ' . json_encode($response));
        return $response;
    }
}
