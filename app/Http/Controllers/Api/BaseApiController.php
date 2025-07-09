<?php
namespace App\Http\Controllers\API;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BaseApiController extends BaseController
{
    protected function success($data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        Log::info("API Success: {$message}", ['data'=> $data]);
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function error(string $message = 'Error', int $status = 400, $errors = null): JsonResponse
    {
        Log::error("API Error: {$message}", ['status'=>$status,'errors'=>$errors]);
        $payload = ['status'=>'error','message'=>$message];
        if ($errors) $payload['errors'] = $errors;
        return response()->json($payload, $status);
    }
}
