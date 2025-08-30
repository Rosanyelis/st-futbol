<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AccountPayable;
use App\Models\Supplier;
use App\Models\Event;
use App\Models\Currency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountPayable>
 */
class AccountPayableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountPayable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'event_id' => Event::factory(),
            'currency_id' => Currency::factory(),
            'date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(2, 100, 100000),
            'description' => $this->faker->sentence(),
            'status' => 'Pendiente',
        ];
    }
}
