<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BranchController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductVariantController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\SaleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login',   [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']); // si quieres registro

// Rutas protegidas por Sanctum
Route::middleware('auth:api')->group(function () {
    // Dos alias para “me”:
    // GET /api/user
    Route::get('user', [AuthController::class, 'me']);
    // GET /api/auth/user
    Route::get('auth/user', [AuthController::class, 'me']);

    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // Owner-only
    Route::get('branches',       [BranchController::class, 'index']);
    Route::post('branches',       [BranchController::class, 'store']);
    Route::get('branches/{id}',  [BranchController::class, 'show']);
    Route::put('branches/{id}',  [BranchController::class, 'update']);
    Route::delete('branches/{id}',  [BranchController::class, 'destroy']);

    Route::get('categories',      [CategoryController::class, 'index']);
    Route::post('categories',      [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    Route::get('products',      [ProductController::class, 'index']);
    Route::post('products',      [ProductController::class, 'store']);
    Route::put('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);

    Route::get('variants',            [ProductVariantController::class, 'index']);
    Route::post('variants',            [ProductVariantController::class, 'store']);
    Route::put('variants/{variant}',  [ProductVariantController::class, 'update']);
    Route::delete('variants/{variant}',  [ProductVariantController::class, 'destroy']);

    // Ventas: index/show para todos, store solo seller
    Route::get('sales',      [SaleController::class, 'index']);
    Route::get('sales/{id}', [SaleController::class, 'show']);
    Route::post('sales',      [SaleController::class, 'store']);

    // Reportes (owner)
    Route::get('reports/daily',        [ReportController::class, 'daily']);
    Route::get('reports/top-products', [ReportController::class, 'topProducts']);
    Route::get('reports/daily-sales',         [ReportController::class, 'dailySales']);
    Route::get('reports/top-product-branch',  [ReportController::class, 'topProductByBranch']);
});
