<?php

namespace Database\Seeders;

use App\Models\CategoryIncome;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoryIncomesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Clubs'],
            ['name' => 'Venta de Merchandising'],
            ['name' => 'Venta de Entradas'],
        ];

        foreach ($data as $item) {
            CategoryIncome::updateOrCreate($item);
        }
    }
}
