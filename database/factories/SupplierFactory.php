<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\CategorySupplier;
use App\Models\Currency;
use App\Models\SubcategorySupplier;
use App\Models\Supplier;

class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'category_supplier_id' => CategorySupplier::factory(),
            'subcategory_supplier_id' => SubcategorySupplier::factory(),
            'currency_id' => Currency::factory(),
            'name' => fake()->name(),
            'representant' => fake()->word(),
            'phone' => fake()->phoneNumber(),
            'amount' => fake()->randomFloat(0, 0, 9999999999.),
            'description' => fake()->text(),
        ];
    }
}
