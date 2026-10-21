<?php

namespace Database\Factories\MasterData;

use App\Models\MasterData\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'name' => $this->faker->company,
            'warehouse_type' => $this->faker->randomElement(['normal', 'raw_material', 'work_in_progress', 'finished_goods', 'waste']),
            'address' => $this->faker->address,
            'active' => true,
        ];
    }
}
