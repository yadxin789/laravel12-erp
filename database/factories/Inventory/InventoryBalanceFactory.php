<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryBalance;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryBalanceFactory extends Factory
{
    protected $model = InventoryBalance::class;

    public function definition()
    {
        return [
            'item_id' => null,
            'warehouse_id' => null,
            'location_id' => null,
            'batch_number' => $this->faker->regexify('[A-Z][0-9]{8}'),
            'quantity' => $this->faker->numberBetween(1, 1000),
            'reserved_quantity' => 0,
            'frozen_quantity' => 0,
            'quality_status' => 'qualified',
            'supplier_id' => null,
            'production_order_id' => null,
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
