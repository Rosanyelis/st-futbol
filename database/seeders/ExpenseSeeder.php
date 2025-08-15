<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['category_expense_id' => 4, 'name' => 'Eliana'],
            ['category_expense_id' => 4, 'name' => 'Faviola'],
            ['category_expense_id' => 4, 'name' => 'Community Manager'],
            ['category_expense_id' => 4, 'name' => 'Natalia'],
        ];
        SubcategoryExpense::insert($data);
    }
}
