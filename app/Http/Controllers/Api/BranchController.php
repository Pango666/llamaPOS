<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\BranchService;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use Illuminate\Http\Request;

class BranchController extends BaseApiController
{
    public function __construct(private BranchService $service)
    {
        // Valida token y rol owner en todas las rutas de este controlador
        $this->middleware(['auth:api', 'role:owner']);
    }

    public function index()
    {
        try {
            $branches = $this->service->all();
            return $this->success($branches);
        } catch (\Exception $e) {
            \Log::error('BranchController@index error', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error($e->getMessage(), 500);
        }
    }

    public function store(StoreBranchRequest $request)
    {
        try {
            $branch = $this->service->create($request->validated());
            return $this->success($branch, 'Sucursal creada', 201);
        } catch (\Exception $e) {
            \Log::error('BranchController@store error', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('No se pudo crear sucursal', 500);
        }
    }

    public function show($id)
    {
        try {
            $branch = $this->service->find($id);
            return $this->success($branch);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Sucursal no encontrada', 404);
        } catch (\Exception $e) {
            \Log::error('BranchController@show error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al obtener sucursal', 500);
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
            \Log::error('BranchController@update error', ['msg'=>$e->getMessage()]);
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
        } catch (\Exception $e) {
            \Log::error('BranchController@destroy error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al eliminar sucursal', 500);
        }
    }
}
