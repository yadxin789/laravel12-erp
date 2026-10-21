<?php

namespace Database\Factories\Production;

use App\Models\MasterData\Item;
use App\Models\Production\BomHeader;
use Illuminate\Database\Eloquent\Factories\Factory;

class BomHeaderFactory extends Factory
{
    protected $model = BomHeader::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('BOM[0-9]{6}'),
            'item_id' => Item::factory(),
            'version' => 'default',
            'quantity' => 1,
            'status' => 'approved',
            'effective_date' => now(),
            'created_by' => 1,
            'approved_by' => 1,
            'approved_at' => now(),
        ];
    }
}
