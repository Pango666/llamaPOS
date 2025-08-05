<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseApiController;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends BaseApiController
{
    public function __construct(private ReportService $service)
    {
        $this->middleware(['auth:api','role:owner']);
    }

    public function daily(Request $request)
    {
        try {
            $data = $this->service->daily($request->only('branch_id','date'));
            return $this->success($data);
        } catch (\Exception $e) {
            Log::error('ReportController@daily error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al generar reporte diario', 500);
        }
    }

    public function topProducts(Request $request)
    {
        try {
            $data = $this->service->topProducts($request->only('branch_id','date'));
            return $this->success($data);
        } catch (\Exception $e) {
            Log::error('ReportController@topProducts error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al generar reporte top productos', 500);
        }
    }

    public function dailySales(Request $request)
    {
        try {
            $data = $this->service->dailySales($request->only('branch_id','date'));
            return $this->success($data);
        } catch (\Exception $e) {
            Log::error('ReportController@dailySales error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al generar ventas diarias', 500);
        }
    }

    public function topProductByBranch(Request $request)
    {
        try {
            $data = $this->service->topProductsByBranch(
                (int)$request->query('branch_id'),
                $request->query('date')
            );
            return $this->success($data);
        } catch (\Exception $e) {
            Log::error('ReportController@topProductByBranch error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al generar top producto por sucursal', 500);
        }
    }
}