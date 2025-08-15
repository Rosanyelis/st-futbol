<?php

namespace Database\Seeders;

use App\Models\CategoryExpense;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoryExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Monotributo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Impuestos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Contador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sueldos', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        CategoryExpense::insert($data);
    }
}
