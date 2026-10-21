<?php

namespace Database\Factories\MasterData;

use App\Models\MasterData\Item;
use App\Models\MasterData\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{6}'),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'spec' => $this->faker->word,
            'category_id' => null,
            'unit_id' => Unit::factory(),
            'standard_cost' => $this->faker->randomFloat(2, 1, 1000),
            'safety_stock' => $this->faker->numberBetween(0, 100),
            'batch_management' => $this->faker->boolean,
            'serial_management' => $this->faker->boolean,
            'active' => true,
        ];
    }
}
