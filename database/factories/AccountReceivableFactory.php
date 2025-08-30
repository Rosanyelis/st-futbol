<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AccountReceivable;
use App\Models\Club;
use App\Models\Event;
use App\Models\Currency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountReceivable>
 */
class AccountReceivableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountReceivable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'event_id' => Event::factory(),
            'currency_id' => Currency::factory(),
            'date' => $this->faker->date(),
            'has_accommodation' => $this->faker->boolean(),
            'players_quantity' => $this->faker->numberBetween(1, 50),
            'player_price' => $this->faker->randomFloat(2, 10, 1000),
            'total_players' => $this->faker->numberBetween(0, 50000),
            'teachers_quantity' => $this->faker->numberBetween(0, 10),
            'teacher_price' => $this->faker->randomFloat(2, 50, 2000),
            'total_teachers' => $this->faker->numberBetween(0, 20000),
            'companions_quantity' => $this->faker->numberBetween(0, 20),
            'companion_price' => $this->faker->randomFloat(2, 20, 500),
            'total_companions' => $this->faker->numberBetween(0, 10000),
            'drivers_quantity' => $this->faker->numberBetween(0, 5),
            'driver_price' => $this->faker->randomFloat(2, 30, 800),
            'total_drivers' => $this->faker->numberBetween(0, 4000),
            'liberated_quantity' => $this->faker->numberBetween(0, 10),
            'total_people' => $this->faker->numberBetween(1, 100),
            'total_amount' => $this->faker->randomFloat(2, 100, 100000),
            'description' => $this->faker->sentence(),
            'status' => 'Pendiente',
        ];
    }
}
