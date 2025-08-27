<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash; 
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseApiController
{
    public function __construct(private AuthService $authService)
    {
        // Protege todo, excepto login/registro/refresh
        $this->middleware('auth:api')->except(['login','register','refresh']);
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

    public function changePassword(Request $request)
    {
        // 1) Validación
        $validator = Validator::make($request->all(), [
            'current_password'      => 'required|string',
            'password'              => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.different'    => 'La nueva contraseña debe ser diferente a la actual.',
        ]);

        if ($validator->fails()) {
            return $this->error('Datos inválidos', 422, $validator->errors());
        }

        try {
            // 2) Usuario autenticado
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            // 3) Verifica contraseña actual
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return $this->error('La contraseña actual no es correcta', 422);
            }

            // 4) Actualiza contraseña
            $user->password = Hash::make($request->input('password'));
            $user->save();

            // 5) Invalida el token actual y emite uno nuevo
            // (requiere 'blacklist_enabled' => true en config/jwt.php para que invalidate funcione)
            $newToken = null;
            try {
                $currentToken = JWTAuth::getToken();
                if ($currentToken) {
                    JWTAuth::invalidate($currentToken); // revoca el actual
                }
                $newToken = JWTAuth::fromUser($user);   // genera uno nuevo
            } catch (JWTException $e) {
                // Si no se puede invalidar/emitir, devolvemos éxito sin token nuevo
            }

            // 6) Respuesta
            if ($newToken) {
                return response()->json([
                    'message'      => 'Contraseña actualizada correctamente',
                    'access_token' => $newToken,
                    'token_type'   => 'bearer',
                    'expires_in'   => JWTAuth::factory()->getTTL() * 60,
                ], 200);
            }

            return response()->json([
                'message' => 'Contraseña actualizada correctamente (vuelve a iniciar sesión si es necesario)',
            ], 200);

        } catch (\Throwable $e) {
            return $this->error('No se pudo cambiar la contraseña', 500, $e->getMessage());
        }
    }
}

