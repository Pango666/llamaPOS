<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientController extends BaseApiController
{
    public function __construct(private ClientService $service)
    {
        $this->middleware(['auth:api','role:owner']);
    }

    public function index()
    {
        try {
            $clients = $this->service->all();
            return $this->success($clients);
        } catch (\Exception $e) {
            Log::error('ClientController@index error', ['msg' => $e->getMessage()]);
            return $this->error('No se pudieron obtener clientes', 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'tax_id'  => 'nullable|string|max:100|unique:clients,tax_id',
            'email'   => 'nullable|email|max:255|unique:clients,email',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            $client = $this->service->create($data);
            return $this->success($client, 'Cliente creado', 201);
        } catch (\Exception $e) {
            Log::error('ClientController@store error', ['msg' => $e->getMessage()]);
            return $this->error('Error al crear cliente', 500);
        }
    }

    public function show(int $id)
    {
        try {
            $client = $this->service->find($id);
            return $this->success($client);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Cliente no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ClientController@show error', ['msg' => $e->getMessage()]);
            return $this->error('Error al obtener cliente', 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'tax_id'  => 'sometimes|nullable|string|max:100|unique:clients,tax_id,' . $id,
            'email'   => 'sometimes|nullable|email|max:255|unique:clients,email,' . $id,
            'phone'   => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string|max:255',
        ]);

        try {
            $client = $this->service->update($id, $data);
            return $this->success($client, 'Cliente actualizado');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Cliente no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ClientController@update error', ['msg' => $e->getMessage()]);
            return $this->error('Error al actualizar cliente', 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);
            return $this->success(null, 'Cliente eliminado', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Cliente no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ClientController@destroy error', ['msg' => $e->getMessage()]);
            return $this->error('Error al eliminar cliente', 500);
        }
    }
}
