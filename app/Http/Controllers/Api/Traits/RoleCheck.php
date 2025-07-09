<?php

namespace App\Http\Controllers\Api\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

trait RoleCheck
{
    /**
     * Lanza 403 si el usuario no tiene exactamente ese $role.
     */
    protected function authorizeRole(string $role): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== $role) {
            throw new HttpResponseException(
                response()->json(['message' => 'Unauthorized'], 403)
            );
        }
    }

    /**
     * Lanza 403 si el usuario no está en alguno de los $roles.
     */
    protected function authorizeAnyRole(array $roles): void
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, $roles, true)) {
            throw new HttpResponseException(
                response()->json(['message' => 'Unauthorized'], 403)
            );
        }
    }
}
