<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\CategoryMethodPayment;
use App\Models\Entity;
use App\Models\MethodPayment;

class MethodPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MethodPayment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'category_method_payment_id' => CategoryMethodPayment::factory(),
            'entity_id' => Entity::factory(),
            'name' => fake()->name(),
            'account_holder' => fake()->word(),
            'type_entity' => fake()->word(),
            'account_number' => fake()->word(),
            'cbu_cvu' => fake()->word(),
            'alias' => fake()->word(),
            'type_account' => fake()->randomElement(["Propia","Terceros"]),
        ];
    }
}
