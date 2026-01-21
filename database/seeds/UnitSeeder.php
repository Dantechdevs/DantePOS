<?php

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
            // Weight units
            ['name' => 'Thaila', 'short_name' => 'thaila', 'type' => Unit::TYPE_WEIGHT, 'is_system' => true],
            ['name' => 'Kilogram', 'short_name' => 'kg', 'type' => Unit::TYPE_WEIGHT, 'is_system' => true],
            ['name' => 'Gram', 'short_name' => 'g', 'type' => Unit::TYPE_WEIGHT, 'is_system' => true],

            // Count units
            ['name' => 'Box', 'short_name' => 'box', 'type' => Unit::TYPE_COUNT, 'is_system' => true],
            ['name' => 'Piece', 'short_name' => 'pc', 'type' => Unit::TYPE_COUNT, 'is_system' => true],

            // Volume units
            ['name' => 'Liter', 'short_name' => 'l', 'type' => Unit::TYPE_VOLUME, 'is_system' => true],
            ['name' => 'Milliliter', 'short_name' => 'ml', 'type' => Unit::TYPE_VOLUME, 'is_system' => true],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
