<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\CategoryExpense;
use App\Models\SubcategoryExpense;

class SubcategoryExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubcategoryExpense::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'category_expense_id' => CategoryExpense::factory(),
            'name' => fake()->name(),
        ];
    }
}
