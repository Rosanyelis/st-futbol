<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\City;
use App\Models\Club;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Event;
use App\Models\Province;

class ClubFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Club::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'currency_id' => Currency::factory(),
            'name' => fake()->name(),
            'logo' => fake()->word(),
            'cuit' => fake()->numberBetween(10000000000, 99999999999),
            'responsible' => fake()->word(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'country_id' => Country::factory(),
            'province_id' => Province::factory(),
            'city_id' => City::factory(),
            'hosting' => fake()->boolean(),
            'total_amount' => fake()->randomFloat(0, 0, 9999999999.),
        ];
    }
}
