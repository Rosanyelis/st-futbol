<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\SubcategoryExpense;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Primero crear las subcategorías
        $subcategoryData = [
            ['category_expense_id' => 4, 'name' => 'Eliana'],
            ['category_expense_id' => 4, 'name' => 'Faviola'],
            ['category_expense_id' => 4, 'name' => 'Community Manager'],
            ['category_expense_id' => 4, 'name' => 'Natalia'],
        ];
        
        foreach ($subcategoryData as $subcategory) {
            $subcategoryExpense = SubcategoryExpense::create($subcategory);
            
            // Crear el gasto correspondiente
            Expense::create([
                'category_egress_id' => 1, // ID 1 = "Gastos"
                'category_expense_id' => $subcategory['category_expense_id'],
                'subcategory_expense_id' => $subcategoryExpense->id,
                'name' => $subcategory['name'],
                'description' => 'Gasto de ' . $subcategory['name'],
            ]);
        }
    }
}
