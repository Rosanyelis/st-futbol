<?php

namespace Database\Seeders;

use App\Models\CategoryEgress;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryEgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Gastos'],
            ['name' => 'Proveedores'],
        ];

        foreach ($data as $item) {
            CategoryEgress::updateOrCreate($item);
        }
    }
}
