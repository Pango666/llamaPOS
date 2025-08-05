<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SaleController;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('login',    [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register']);
Route::post('refresh', [AuthController::class, 'refresh']);

// Rutas protegidas por JWT (guard api en config/auth.php)
Route::middleware('auth:api')->group(function () {
    // Información de usuario y logout
    Route::get('user',      [AuthController::class, 'me']);
    Route::get('auth/user', [AuthController::class, 'me']);
    Route::post('logout',   [AuthController::class, 'logout']);

    // Demo de roles
    Route::get('demo', [AuthController::class, 'getRoles']);

    // Ventas (tanto owner como seller pueden index y show)
    Route::get('sales',      [SaleController::class, 'index']);
    Route::get('sales/{id}', [SaleController::class, 'show']);
    // Crear venta para roles owner o seller
    Route::post('sales', [SaleController::class, 'store'])
         ->middleware('role:owner|seller');

    // Rutas de administración (solo owner)
    Route::middleware('role:owner')->group(function () {
        // Sucursales CRUD
        Route::get('branches',          [BranchController::class, 'index']);
        Route::post('branches',         [BranchController::class, 'store']);
        Route::get('branches/{id}',     [BranchController::class, 'show']);
        Route::put('branches/{id}',     [BranchController::class, 'update']);
        Route::delete('branches/{id}',  [BranchController::class, 'destroy']);

        // Categorías CRUD
        Route::get('categories',         [CategoryController::class, 'index']);
        Route::post('categories',        [CategoryController::class, 'store']);
        Route::get('categories/{id}',    [CategoryController::class, 'show']);
        Route::put('categories/{id}',    [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

        // Productos CRUD
        Route::get('products',         [ProductController::class, 'index']);
        Route::get('products/{id}',    [ProductController::class, 'show']);
        Route::post('products',        [ProductController::class, 'store']);
        Route::put('products/{id}',    [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);

        // Variantes CRUD
        Route::get('variants',            [ProductVariantController::class, 'index']);
        Route::post('variants',           [ProductVariantController::class, 'store']);
        Route::put('variants/{id}',       [ProductVariantController::class, 'update']);
        Route::delete('variants/{id}',    [ProductVariantController::class, 'destroy']);

        // Reportes
        Route::get('reports/daily',           [ReportController::class, 'daily']);
        Route::get('reports/top-products',    [ReportController::class, 'topProducts']);
        Route::get('reports/daily-sales',     [ReportController::class, 'dailySales']);
        Route::get('reports/top-product-branch', [ReportController::class, 'topProductByBranch']);
    });
});