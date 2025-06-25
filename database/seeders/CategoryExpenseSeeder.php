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
            ['name' => 'Monotributo'],
            ['name' => 'Impuestos'],
            ['name' => 'Contador'],
            ['name' => 'Sueldos'],
        ];
        CategoryExpense::insert($data);
    }
}
