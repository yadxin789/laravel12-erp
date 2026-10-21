<?php

namespace Tests\Feature;

use App\Models\MasterData\Item;
use App\Models\Production\BomHeader;
use App\Models\User;
use App\Services\ProductionOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;
    protected $item;
    protected $bomHeader;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(ProductionOrderService::class);
        $this->user = User::factory()->create();
        
        $this->item = Item::factory()->create([
            'code' => 'FG001',
            'name' => 'Finished Good',
        ]);

        $this->bomHeader = BomHeader::factory()->create([
            'item_id' => $this->item->id,
            'version' => 'default',
            'status' => 'approved',
        ]);
    }

    public function test_create_production_order()
    {
        $order = $this->service->create([
            'item_id' => $this->item->id,
            'bom_version' => 'default',
            'quantity' => 100,
            'plan_start_date' => now()->format('Y-m-d'),
            'plan_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $this->assertNotNull($order);
        $this->assertEquals('draft', $order->status->value);
        $this->assertEquals(100, $order->quantity);
    }

    public function test_release_production_order()
    {
        $order = $this->service->create([
            'item_id' => $this->item->id,
            'bom_version' => 'default',
            'quantity' => 100,
            'plan_start_date' => now()->format('Y-m-d'),
            'plan_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $updatedOrder = $this->service->release($order);

        $this->assertEquals('ready', $updatedOrder->status->value);
    }

    public function test_start_production_order()
    {
        $order = $this->service->create([
            'item_id' => $this->item->id,
            'bom_version' => 'default',
            'quantity' => 100,
            'plan_start_date' => now()->format('Y-m-d'),
            'plan_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $order = $this->service->release($order);
        $updatedOrder = $this->service->start($order);

        $this->assertEquals('in_progress', $updatedOrder->status->value);
        $this->assertNotNull($updatedOrder->actual_start_date);
    }

    public function test_report_production_order()
    {
        $order = $this->service->create([
            'item_id' => $this->item->id,
            'bom_version' => 'default',
            'quantity' => 100,
            'plan_start_date' => now()->format('Y-m-d'),
            'plan_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $order = $this->service->release($order);
        $order = $this->service->start($order);

        $updatedOrder = $this->service->report($order, [
            'completed_quantity' => 50,
            'scrap_quantity' => 2,
        ]);

        $this->assertEquals(50, $updatedOrder->completed_quantity);
        $this->assertEquals(2, $updatedOrder->scrap_quantity);
    }

    public function test_complete_production_order()
    {
        $order = $this->service->create([
            'item_id' => $this->item->id,
            'bom_version' => 'default',
            'quantity' => 100,
            'plan_start_date' => now()->format('Y-m-d'),
            'plan_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $order = $this->service->release($order);
        $order = $this->service->start($order);

        $order = $this->service->report($order, [
            'completed_quantity' => 90,
            'scrap_quantity' => 0,
        ]);

        $updatedOrder = $this->service->complete($order);

        $this->assertEquals('completed', $updatedOrder->status->value);
        $this->assertNotNull($updatedOrder->actual_end_date);
    }

    public function test_close_production_order()
    {
        $order = $this->service->create([
            'item_id' => $this->item->id,
            'bom_version' => 'default',
            'quantity' => 100,
            'plan_start_date' => now()->format('Y-m-d'),
            'plan_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $order = $this->service->release($order);
        $order = $this->service->start($order);
        $order = $this->service->report($order, [
            'completed_quantity' => 90,
            'scrap_quantity' => 0,
        ]);
        $order = $this->service->complete($order);

        $updatedOrder = $this->service->close($order);

        $this->assertEquals('closed', $updatedOrder->status->value);
    }

    public function test_cancel_production_order()
    {
        $order = $this->service->create([
            'item_id' => $this->item->id,
            'bom_version' => 'default',
            'quantity' => 100,
            'plan_start_date' => now()->format('Y-m-d'),
            'plan_end_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $updatedOrder = $this->service->cancel($order);

        $this->assertEquals('cancelled', $updatedOrder->status->value);
    }
}
