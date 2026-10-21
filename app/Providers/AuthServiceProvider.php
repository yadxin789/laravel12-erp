<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Item;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\Inventory\InventoryBalance;
use App\Models\Production\BomHeader;
use App\Models\Production\ProductionOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Quality\QualityInspection;
use App\Models\Sales\SalesOrder;
use App\Policies\BomPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\ItemPolicy;
use App\Policies\ProductionOrderPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\QualityPolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\WarehousePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Item::class => ItemPolicy::class,
        Customer::class => CustomerPolicy::class,
        Supplier::class => SupplierPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        ProductionOrder::class => ProductionOrderPolicy::class,
        InventoryBalance::class => InventoryPolicy::class,
        QualityInspection::class => QualityPolicy::class,
        BomHeader::class => BomPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
