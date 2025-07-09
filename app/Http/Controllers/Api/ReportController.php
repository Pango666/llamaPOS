<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Controllers\API\Traits\RoleCheck;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends BaseApiController
{
    use RoleCheck;

    public function __construct(private ReportService $service) {}

    public function daily(Request $request)
    {
        try {
            $this->authorizeRole('owner');
            $data = $this->service->daily($request->only('branch_id','date'));
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error('Error al generar reporte diario', 500);
        }
    }

    public function topProducts(Request $request)
    {
        try {
            $this->authorizeRole('owner');
            $data = $this->service->topProducts($request->only('branch_id','date'));
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->error('Error al generar reporte top productos', 500);
        }
    }

    public function dailySales(Request $request)
{
    try {
        $this->authorizeRole('owner');
        $params = $request->only('branch_id','date');
        $data = $this->service->dailySales($params);
        return $this->success($data);
    } catch (\Exception $e) {
        return $this->error('Error al generar ventas diarias', 500);
    }
}

// GET /api/reports/top-product-branch
public function topProductByBranch(Request $request)
{
    try {
        $this->authorizeRole('owner');
        $branch = (int)$request->query('branch_id');
        $date   = $request->query('date');
        $data = $this->service->topProductByBranch($branch,$date);
        return $this->success($data);
    } catch (\Exception $e) {
        return $this->error('Error al generar top producto', 500);
    }
}
}
