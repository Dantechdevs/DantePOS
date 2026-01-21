<?php

use Illuminate\Database\Seeder;
use App\Models\CustomerOpeningBalance;

class CustomerOpeningBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Generate 50 dummy entries for a specific customer (e.g., customer_id = 1)
        factory(CustomerOpeningBalance::class, 100)->create([
            'customer_id' => 1, // Replace with the specific customer ID
        ]);

        // Optionally, generate dummy entries for multiple customers
        factory(CustomerOpeningBalance::class, 1000)->create();
    }
}
