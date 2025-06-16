<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Club;
use App\Models\ClubItem;

class ClubItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClubItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'name' => fake()->name(),
            'quantity' => fake()->numberBetween(-10000, 10000),
            'price' => fake()->randomFloat(0, 0, 9999999999.),
            'total' => fake()->randomFloat(0, 0, 9999999999.),
        ];
    }
}
