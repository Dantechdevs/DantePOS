<?php
/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Customer;
use App\Models\CustomerOpeningBalance;
use Faker\Generator as Faker;

$factory->define(CustomerOpeningBalance::class, function (Faker $faker) {
    return [
        'invoice_no' => $faker->unique()->numberBetween(1, 10000), // Fixed the faker usage
        'date' => $faker->dateTimeBetween('-1 year', 'now'),
        'customer_id' => Customer::inRandomOrder()->first()->id, // Ensure there are customers
        'type' => $faker->randomElement(['debit', 'credit']),
        'description' => $faker->sentence,
        'amount' => $faker->randomFloat(2, 50, 1000), // Random amount between 50 and 1000
        'createdBy' => 1, // Assuming '1' is the admin user ID
    ];
});
