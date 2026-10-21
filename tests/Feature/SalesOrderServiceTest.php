<?php

namespace Tests\Feature;

use App\Models\MasterData\Customer;
use App\Models\MasterData\Item;
use App\Models\User;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;
    protected $customer;
    protected $item;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(SalesOrderService::class);
        $this->user = User::factory()->create();
        
        $this->customer = Customer::factory()->create([
            'code' => 'CUST001',
            'name' => 'Test Customer',
        ]);
        
        $this->item = Item::factory()->create([
            'code' => 'ITEM001',
            'name' => 'Test Item',
        ]);
    }

    public function test_create_sales_order()
    {
        $order = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $this->assertNotNull($order);
        $this->assertEquals('draft', $order->status->value);
        $this->assertEquals(1000.00, $order->total_amount);
        $this->assertCount(1, $order->lines);
        $this->assertEquals(10, $order->lines[0]->quantity);
    }

    public function test_submit_sales_order()
    {
        $order = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $updatedOrder = $this->service->submit($order);

        $this->assertEquals('submitted', $updatedOrder->status->value);
    }

    public function test_approve_sales_order()
    {
        $order = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $order = $this->service->submit($order);
        $updatedOrder = $this->service->approve($order);

        $this->assertEquals('approved', $updatedOrder->status->value);
    }

    public function test_cancel_sales_order()
    {
        $order = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $updatedOrder = $this->service->cancel($order);

        $this->assertEquals('cancelled', $updatedOrder->status->value);
    }

    public function test_close_sales_order()
    {
        $order = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $order = $this->service->submit($order);
        $order = $this->service->approve($order);

        $order->status = \App\Enums\SalesOrderStatus::FullyDelivered;
        $order->save();

        $updatedOrder = $this->service->close($order);

        $this->assertEquals('closed', $updatedOrder->status->value);
    }

    public function test_update_sales_order()
    {
        $order = $this->service->create([
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(7)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
            ],
        ]);

        $updatedOrder = $this->service->update($order, [
            'customer_id' => $this->customer->id,
            'order_date' => now()->format('Y-m-d'),
            'delivery_date' => now()->addDays(14)->format('Y-m-d'),
            'lines' => [
                [
                    'item_id' => $this->item->id,
                    'quantity' => 20,
                    'unit_price' => 90.00,
                ],
            ],
        ]);

        $this->assertEquals(now()->addDays(14)->format('Y-m-d'), $updatedOrder->delivery_date);
        $this->assertEquals(1800.00, $updatedOrder->total_amount);
        $this->assertEquals(20, $updatedOrder->lines->first()->quantity);
    }
}
