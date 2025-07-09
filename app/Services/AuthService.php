<?php

namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthService
{
    public function login(array $credentials): JsonResponse
    {
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo generar el token'], 500);
        }

        return response()->json($this->respondWithToken($token));
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Sesión cerrada con éxito']);
    }

    public function me(): JsonResponse
    {
        return response()->json(JWTAuth::user());
    }

    protected function respondWithToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            // Aquí usamos JWTAuth para obtener el TTL
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            'user'         => JWTAuth::user(),
        ];
    }
}
