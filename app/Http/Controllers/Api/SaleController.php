<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Controllers\API\Traits\RoleCheck;
use App\Services\SaleService;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;

class SaleController extends BaseApiController
{
    use RoleCheck;

    public function __construct(private SaleService $service) {}

    public function index(Request $request)
    {
        try {
            $this->authorizeAnyRole(['owner', 'seller']);
            $perPage = (int) $request->query('per_page', 15);
            $filters = $request->only('branch_id', 'date');
            $data = $this->service->all($filters, $perPage);        // para ventas
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error('No se pudieron obtener ventas', 500);
        }
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            // StoreSaleRequest::authorize() valida role=seller
            $sale = $this->service->create($request->validated());
            return $this->success($sale, 'Venta registrada', 201);
        } catch (\Exception $e) {
            return $this->error('Error al registrar venta', 500);
        }
    }

    public function show($id)
    {
        try {
            $this->authorizeAnyRole(['owner', 'seller']);
            $sale = $this->service->find($id);
            return $this->success($sale);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Venta no encontrada', 404);
        } catch (\Exception $e) {
            return $this->error('Error al obtener venta', 500);
        }
    }
}
