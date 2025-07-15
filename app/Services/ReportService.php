<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    public function daily(array $params)
    {
        return DB::table('sales')
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->when($params['branch_id'] ?? null, fn($q, $b) => $q->where('branch_id', $b))
            ->when($params['date'] ?? null, fn($q, $d) => $q->whereDate('created_at', $d))
            ->groupByRaw('DATE(created_at)')
            ->get();
    }

    public function topProducts(array $params)
    {
        return DB::table('sale_items as si')
            ->join('sales as s', 'si.sale_id', '=', 's.id')
            ->join('product_variants as pv', 'si.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->selectRaw(
                'p.id         as product_id,
             p.name       as product_name,
             SUM(si.quantity) as total_quantity,
             SUM(si.total)    as total_revenue'
            )
            ->when($params['branch_id'] ?? null, fn($q, $b) => $q->where('s.branch_id', $b))
            ->when($params['date']      ?? null, fn($q, $d) => $q->whereDate('s.created_at', $d))
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_quantity')
            ->get();
    }


    public function dailySales(array $params = [])
    {
        return DB::table('sales')
            ->selectRaw('branch_id, DATE(created_at) as date, SUM(total) as total')
            ->when($params['branch_id'] ?? null, fn($q, $b) => $q->where('branch_id', $b))
            ->when($params['date'] ?? null, fn($q, $d) => $q->whereDate('created_at', $d))
            ->groupBy('branch_id', 'date')
            ->orderBy('date', 'desc')
            ->get();
    }

    // Producto más vendido por sucursal
    public function topProductsByBranch(int $branchId, ?string $date = null, int $limit = 10)
    {
        return DB::table('sale_items as si')
            ->join('sales as s', 'si.sale_id', '=', 's.id')
            ->join('product_variants as pv', 'si.product_variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->selectRaw(
                'p.id as product_id,
                 p.name as product_name,
                 SUM(si.quantity) as total_quantity,
                 SUM(si.total)    as total_revenue'
            )
            ->where('s.branch_id', $branchId)
            ->when($date, fn($q, $d) => $q->whereDate('s.created_at', $d))
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }
}
