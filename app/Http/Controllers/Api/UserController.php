<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware(['auth:api','role:owner']);
    }

    protected function mapRole(?string $role): string
    {
        // Acepta roles del front y del back
        return match ($role) {
            'admin'  => 'owner',
            'vendor' => 'seller',
            'owner', 'seller' => $role,
            default  => 'seller',
        };
    }

    public function index()
    {
        // Si usas Spatie, trae roles; si no, lo deja vacío
        $users = User::with('roles')->latest()->get()->map(function ($u) {
            return [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'role'      => $u->roles->first()->name ?? 'seller',
                'branch_id' => $u->branch_id,
                'deleted_at'=> $u->deleted_at,
            ];
        });

        return $this->success($users);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required','string','max:255'],
            'email'     => ['required','email','max:255','unique:users,email'],
            'password'  => ['nullable','string','min:6'],
            'role'      => ['nullable','string','in:owner,seller,admin,vendor'],
            'branch_id' => ['nullable','exists:branches,id'],
        ]);

        $role = $this->mapRole($data['role'] ?? null);

        $user = new User();
        $user->name      = $data['name'];
        $user->email     = $data['email'];
        $user->password  = Hash::make($data['password'] ?? '12345678');
        $user->branch_id = $data['branch_id'] ?? null;
        $user->save();

        // Si tienes Spatie:
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($role);
        }

        return $this->success([
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $role,
            'branch_id' => $user->branch_id,
        ], 'Usuario creado', 201);
    }

    public function show($id)
    {
        $u = User::with('roles')->findOrFail($id);

        return $this->success([
            'id'        => $u->id,
            'name'      => $u->name,
            'email'     => $u->email,
            'role'      => $u->roles->first()->name ?? 'seller',
            'branch_id' => $u->branch_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $u = User::findOrFail($id);

        $data = $request->validate([
            'name'      => ['sometimes','required','string','max:255'],
            'email'     => ['sometimes','required','email','max:255', Rule::unique('users','email')->ignore($u->id)],
            'password'  => ['nullable','string','min:6'],
            'role'      => ['nullable','string','in:owner,seller,admin,vendor'],
            'branch_id' => ['nullable','exists:branches,id'],
        ]);

        if (array_key_exists('name', $data))      $u->name = $data['name'];
        if (array_key_exists('email', $data))     $u->email = $data['email'];
        if (!empty($data['password']))            $u->password = Hash::make($data['password']);
        if (array_key_exists('branch_id', $data)) $u->branch_id = $data['branch_id'];

        $u->save();

        $role = $u->roles->first()->name ?? 'seller';
        if (array_key_exists('role', $data)) {
            $role = $this->mapRole($data['role']);
            if (method_exists($u, 'syncRoles')) {
                $u->syncRoles([$role]);
            }
        }

        return $this->success([
            'id'        => $u->id,
            'name'      => $u->name,
            'email'     => $u->email,
            'role'      => $role,
            'branch_id' => $u->branch_id,
        ], 'Usuario actualizado');
    }

    public function destroy($id)
    {
        $u = User::findOrFail($id);
        $u->delete(); // soft delete
        return $this->success(null, 'Usuario eliminado', 204);
    }
}
