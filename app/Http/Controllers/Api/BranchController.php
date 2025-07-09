<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Api\BaseApiController;    // ← extendemos BaseApiController
use App\Http\Controllers\Api\Traits\RoleCheck;
use App\Services\BranchService;
use App\Http\Requests\StoreBranchRequest;          // si usas FormRequests
use App\Http\Requests\UpdateBranchRequest;
use Illuminate\Http\Request;

class BranchController extends BaseApiController   // ← aquí el cambio clave
{
    use RoleCheck;

    public function __construct(private BranchService $service) {}

    public function index()
    {
        try {
            $this->authorizeRole('owner');
            $branches = $this->service->all();
            return $this->success($branches);
        } catch (\Exception $e) {
            return $this->error('Error al obtener sucursales', 500);
        }
    }

    public function store(StoreBranchRequest $request)
    {
        try {
            // StoreBranchRequest::authorize() ya chequeará el role
            $branch = $this->service->create($request->validated());
            return $this->success($branch, 'Sucursal creada', 201);
        } catch (\Exception $e) {
            return $this->error('No se pudo crear sucursal', 500);
        }
    }

    public function show($id)
    {
        try {
            $this->authorizeRole('owner');
            $branch = $this->service->find($id);
            return $this->success($branch);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Sucursal no encontrada', 404);
        }
    }

    public function update(UpdateBranchRequest $request, $id)
    {
        try {
            $branch = $this->service->update($id, $request->validated());
            return $this->success($branch, 'Sucursal actualizada');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Sucursal no encontrada', 404);
        } catch (\Exception $e) {
            return $this->error('Error al actualizar sucursal', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->success(null, 'Sucursal eliminada', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Sucursal no encontrada', 404);
        }
    }
}
