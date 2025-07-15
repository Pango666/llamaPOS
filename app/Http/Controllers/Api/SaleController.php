<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Services\SaleService;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SaleController extends BaseApiController
{
    public function __construct(private SaleService $service)
    {
        $this->middleware('auth:api');
        // Owner y Seller pueden indexar, ver y crear ventas
        $this->middleware('role:owner|seller');
    }

    public function index(Request $request)
    {
        try {
            // Filtrado opcional por sucursal, fecha y cliente
            $filters = $request->only('branch_id', 'date', 'client_id');
            $perPage = (int) $request->query('per_page', 15);
            $data = $this->service->all($filters, $perPage);

            return $this->success($data, 'Ventas obtenidas');
        } catch (\Exception $e) {
            Log::error('SaleController@index error', ['msg' => $e->getMessage()]);
            return $this->error('No se pudieron obtener ventas', 500);
        }
    }

    public function show($id)
    {
        try {
            $data = $this->service->find($id);
            return $this->success($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Venta no encontrada', 404);
        } catch (\Exception $e) {
            Log::error('SaleController@show error', ['msg' => $e->getMessage()]);
            return $this->error('Error al obtener venta', 500);
        }
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            $data = $request->validated();
            // Cliente opcional
            if ($request->filled('client_id')) {
                $data['client_id'] = $request->input('client_id');
            }

            $sale = $this->service->create($data);
            return $this->success($sale, 'Venta registrada', 201);
        } catch (\Exception $e) {
            Log::error('SaleController@store error', ['msg' => $e->getMessage()]);
            return $this->error('Error al registrar venta', 500);
        }
    }
}