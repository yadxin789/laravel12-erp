<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Item;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\Production\BomHeader;
use App\Models\Production\RoutingHeader;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function items()
    {
        $items = Item::paginate(20);
        return view('admin.items.index', compact('items'));
    }

    public function customers()
    {
        $customers = Customer::paginate(20);
        return view('admin.customers.index', compact('customers'));
    }

    public function suppliers()
    {
        $suppliers = Supplier::paginate(20);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function warehouses()
    {
        $warehouses = Warehouse::paginate(20);
        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function boms()
    {
        $boms = BomHeader::paginate(20);
        return view('admin.boms.index', compact('boms'));
    }

    public function routings()
    {
        $routings = RoutingHeader::paginate(20);
        return view('admin.routings.index', compact('routings'));
    }

    public function routingShow(RoutingHeader $routing)
    {
        return view('admin.routings.show', compact('routing'));
    }
    
}
