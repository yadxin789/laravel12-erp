<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Production\ProductionOrder;
use App\Models\Inventory\InventoryBalance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $salesOrders = SalesOrder::count();
        $purchaseOrders = PurchaseOrder::count();
        $productionOrders = ProductionOrder::count();
        
        $inventoryValue = InventoryBalance::sum('quantity') * 100;

        $recentSalesOrders = SalesOrder::latest()->take(5)->get();
        $recentPurchaseOrders = PurchaseOrder::latest()->take(5)->get();
        $recentProductionOrders = ProductionOrder::latest()->take(5)->get();

        return view('app.dashboard', compact(
            'salesOrders',
            'purchaseOrders',
            'productionOrders',
            'inventoryValue',
            'recentSalesOrders',
            'recentPurchaseOrders',
            'recentProductionOrders'
        ));
    }
}
