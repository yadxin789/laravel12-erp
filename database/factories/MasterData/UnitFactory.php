<?php

namespace Database\Factories\MasterData;

use App\Models\MasterData\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z]{2}'),
            'name' => $this->faker->word,
            'short_name' => $this->faker->word,
            'active' => true,
        ];
    }
}
