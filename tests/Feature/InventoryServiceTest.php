<?php

namespace Tests\Feature;

use App\Models\Inventory\InventoryBalance;
use App\Models\MasterData\Item;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;
    protected $item;
    protected $warehouse;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->create();
        
        $this->item = Item::factory()->create([
            'code' => 'TEST001',
            'name' => 'Test Item',
            'safety_stock' => 10,
        ]);
        
        $this->warehouse = Warehouse::factory()->create([
            'code' => 'WH001',
            'name' => 'Test Warehouse',
        ]);
    }

    public function test_receive_inventory()
    {
        $result = $this->service->receive([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
            'batch_number' => 'B20240101',
            'transaction_no' => 'TRN20240101001',
            'reference_no' => 'PO001',
        ]);

        $this->assertNotNull($result);
        
        $balance = InventoryBalance::where('item_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        
        $this->assertNotNull($balance);
        $this->assertEquals(100, $balance->quantity);
        $this->assertEquals('B20240101', $balance->batch_number);
    }

    public function test_issue_inventory()
    {
        $balance = InventoryBalance::factory()->create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
            'batch_number' => 'B20240101',
        ]);

        $result = $this->service->issue([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 50,
            'batch_number' => 'B20240101',
            'transaction_no' => 'TRN20240101002',
            'reference_no' => 'SO001',
        ]);

        $this->assertTrue($result);
        
        $balance = InventoryBalance::where('item_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        
        $this->assertEquals(50, $balance->quantity);
    }

    public function test_issue_insufficient_inventory()
    {
        InventoryBalance::factory()->create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 10,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('可用库存不足');

        $this->service->issue([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
            'transaction_no' => 'TRN20240101003',
            'reference_no' => 'SO001',
        ]);
    }

    public function test_transfer_inventory()
    {
        $warehouse2 = Warehouse::factory()->create([
            'code' => 'WH002',
            'name' => 'Warehouse 2',
        ]);

        InventoryBalance::factory()->create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $result = $this->service->transfer([
            'item_id' => $this->item->id,
            'from_warehouse_id' => $this->warehouse->id,
            'to_warehouse_id' => $warehouse2->id,
            'quantity' => 30,
            'transaction_no' => 'TRN20240101004',
        ]);

        $this->assertTrue($result);
        
        $balance1 = InventoryBalance::where('item_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $balance2 = InventoryBalance::where('item_id', $this->item->id)
            ->where('warehouse_id', $warehouse2->id)
            ->first();
        
        $this->assertEquals(70, $balance1->quantity);
        $this->assertEquals(30, $balance2->quantity);
    }

    public function test_adjust_inventory()
    {
        $balance = InventoryBalance::factory()->create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $result = $this->service->adjust([
            'balance_id' => $balance->id,
            'adjustment_quantity' => 10,
            'transaction_no' => 'TRN20240101005',
            'reason' => '盘点差异',
        ]);

        $this->assertNotNull($result);
        
        $balance = InventoryBalance::where('item_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        
        $this->assertEquals(110, $balance->quantity);
    }

    public function test_freeze_and_unfreeze_inventory()
    {
        $balance = InventoryBalance::factory()->create([
            'item_id' => $this->item->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ]);

        $result = $this->service->freeze([
            'balance_id' => $balance->id,
            'quantity' => 20,
            'transaction_no' => 'TRN20240101006',
            'reason' => '质量问题',
        ]);

        $this->assertNotNull($result);
        
        $balance = InventoryBalance::where('item_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(20, $balance->frozen_quantity);
        $this->assertEquals(80, $balance->available_quantity);

        $result = $this->service->unfreeze([
            'balance_id' => $balance->id,
            'quantity' => 10,
            'transaction_no' => 'TRN20240101007',
            'reason' => '质量问题已解决',
        ]);

        $this->assertNotNull($result);
        
        $balance = InventoryBalance::where('item_id', $this->item->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(10, $balance->frozen_quantity);
        $this->assertEquals(90, $balance->available_quantity);
    }

    public function test_get_low_stock_items()
    {
        Item::factory()->create([
            'code' => 'LOW001',
            'name' => 'Low Stock Item',
            'safety_stock' => 50,
        ]);

        Item::factory()->create([
            'code' => 'SAFE001',
            'name' => 'Safe Stock Item',
            'safety_stock' => 10,
        ]);

        $lowStockItems = $this->service->getLowStockItems();

        $this->assertNotNull($lowStockItems);
    }
}
