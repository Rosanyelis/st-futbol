<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubcategoryExpense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubcategoryExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubcategoryExpense::factory()->count(10)->create();
    }
}
