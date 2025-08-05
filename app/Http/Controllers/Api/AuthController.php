<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseApiController;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseApiController
{
    public function __construct(private AuthService $authService)
    {
        // Para login y registro no aplicamos middleware
    }

    public function login(Request $request)
    {
        return $this->authService->login($request->only('email','password'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Datos inválidos', 422, $validator->errors());
        }

        return $this->authService->register($validator->validated());
    }

     public function refresh()
    {
        try {
            // Obtiene el token actual (o lanza si no lo encuentra)
            $currentToken = JWTAuth::getToken();
            // Refresca y obtiene uno nuevo
            $newToken = JWTAuth::refresh($currentToken);

            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                // TTL viene en minutos, lo convertimos a segundos
                'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se pudo refrescar el token'
            ], 401);
        }
    }
}

