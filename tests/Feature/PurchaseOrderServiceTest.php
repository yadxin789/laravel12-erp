<?php

namespace Tests\Feature;

use App\Models\MasterData\Item;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;
    protected $supplier;
    protected $item;
    protected $warehouse;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(PurchaseOrderService::class);
        $this->user = User::factory()->create();
        
        $this->supplier = Supplier::factory()->create([
            'code' => 'SUPP001',
            'name' => 'Test Supplier',
        ]);
        
        $this->item = Item::factory()->create([
            'code' => 'ITEM001',
            'name' => 'Test Item',
        ]);

        $this->warehouse = Warehouse::factory()->create([
            'code' => 'WH001',
            'name' => 'Test Warehouse',
        ]);
    }

    public function test_create_purchase_order()
    {
        $order = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 50,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $this->assertNotNull($order);
        $this->assertEquals('draft', $order->status->value);
        $this->assertEquals(2500.00, $order->total_amount);
        $this->assertCount(1, $order->lines);
    }

    public function test_submit_purchase_order()
    {
        $order = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 50,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $updatedOrder = $this->service->submit($order);

        $this->assertEquals('submitted', $updatedOrder->status->value);
    }

    public function test_approve_purchase_order()
    {
        $order = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 50,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $order = $this->service->submit($order);
        $updatedOrder = $this->service->approve($order);

        $this->assertEquals('approved', $updatedOrder->status->value);
    }

    public function test_cancel_purchase_order()
    {
        $order = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 50,
                    'unit_price' => 50.00,
                ],
            ],
        ]);

        $updatedOrder = $this->service->cancel($order);

        $this->assertEquals('cancelled', $updatedOrder->status->value);
    }

    public function test_receive_purchase_order()
    {
        $order = $this->service->create([
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 50,
                    'unit_price' => 50.00,
                    'warehouse_id' => $this->warehouse->id,
                ],
            ],
        ]);

        $order = $this->service->submit($order);
        $order = $this->service->approve($order);

        $updatedOrder = $this->service->receive($order, [
            'lines' => [
                ['item_id' => $this->item->id, 'quantity' => 30, 'batch_number' => 'B20240101'],
            ],
        ]);

        $this->assertEquals('partially_received', $updatedOrder->status->value);
        $this->assertEquals(30, $updatedOrder->received_quantity);

        $updatedOrder = $this->service->receive($order, [
            'lines' => [
                ['item_id' => $this->item->id, 'quantity' => 20, 'batch_number' => 'B20240102'],
            ],
        ]);

        $this->assertEquals('fully_received', $updatedOrder->status->value);
        $this->assertEquals(50, $updatedOrder->received_quantity);
    }
}
