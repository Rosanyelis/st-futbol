<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Bussine;
use App\Models\CategoryEgress;
use App\Models\CategoryIncome;
use App\Models\Club;
use App\Models\Currency;
use App\Models\Event;
use App\Models\EventMovement;
use App\Models\Expense;
use App\Models\MethodPayment;
use App\Models\Supplier;

class EventMovementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventMovement::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'bussines_id' => Bussine::factory(),
            'event_id' => Event::factory(),
            'club_id' => Club::factory(),
            'method_payment_id' => MethodPayment::factory(),
            'category_income_id' => CategoryIncome::factory(),
            'category_egress_id' => CategoryEgress::factory(),
            'currency_id' => Currency::factory(),
            'supplier_id' => Supplier::factory(),
            'expense_id' => Expense::factory(),
            'amount' => fake()->randomFloat(0, 0, 9999999999.),
            'date' => fake()->date(),
            'description' => fake()->text(),
            'status' => fake()->randomElement(["Pendiente","Pagado","Cancelado"]),
            'type' => fake()->randomElement(["Ingreso","Egreso"]),
        ];
    }
}
