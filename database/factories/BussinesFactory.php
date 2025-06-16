<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Bussines;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;

class BussinesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bussines::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->word(),
            'country_id' => Country::factory(),
            'province_id' => Province::factory(),
            'city_id' => City::factory(),
            'logo' => fake()->word(),
            'cuit' => fake()->numberBetween(10000000000, 99999999999),
        ];
    }
}
