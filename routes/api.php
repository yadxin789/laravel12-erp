<?php

use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\ItemController;
use App\Http\Controllers\MasterData\SupplierController;
use App\Http\Controllers\Production\ProductionOrderController;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Sales\SalesOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('items', ItemController::class)->only(['index', 'show']);
    Route::get('items/by-barcode/{barcode}', [ItemController::class, 'getByBarcode']);
    Route::apiResource('customers', CustomerController::class)->only(['index', 'show']);
    Route::apiResource('suppliers', SupplierController::class)->only(['index', 'show']);

    Route::get('inventory/balances', [InventoryController::class, 'index']);
    Route::get('inventory/transactions', [InventoryController::class, 'transactions']);
    Route::get('inventory/available', [InventoryController::class, 'available']);

    // 多段 URI 的资源路由必须显式命名：apiResource 自动命名只取最后一段，
    // 三个 orders 会同时命名为 orders.index，route:cache 时报重名冲突
    Route::apiResource('sales/orders', SalesOrderController::class)
        ->only(['index', 'show', 'store', 'update'])
        ->names('api.sales.orders');
    Route::post('sales/orders/{salesOrder}/submit', [SalesOrderController::class, 'submit']);
    Route::post('sales/orders/{salesOrder}/approve', [SalesOrderController::class, 'approve']);
    Route::post('sales/orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel']);

    Route::apiResource('purchasing/orders', PurchaseOrderController::class)
        ->only(['index', 'show', 'store', 'update'])
        ->names('api.purchasing.orders');
    Route::post('purchasing/orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit']);
    Route::post('purchasing/orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
    Route::post('purchasing/orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);

    Route::apiResource('production/orders', ProductionOrderController::class)
        ->only(['index', 'show', 'store'])
        ->names('api.production.orders');
    Route::post('production/orders/{productionOrder}/release', [ProductionOrderController::class, 'release']);
    Route::post('production/orders/{productionOrder}/start', [ProductionOrderController::class, 'start']);
    Route::post('production/orders/{productionOrder}/report', [ProductionOrderController::class, 'report']);
    Route::post('production/orders/{productionOrder}/complete', [ProductionOrderController::class, 'complete']);
    Route::post('production/orders/{productionOrder}/close', [ProductionOrderController::class, 'close']);
    Route::post('production/orders/{productionOrder}/cancel', [ProductionOrderController::class, 'cancel']);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
});
