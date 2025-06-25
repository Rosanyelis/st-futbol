<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['province_id' => 1, 'name' => 'La Plata'],
            ['province_id' => 1, 'name' => 'Ramos Mejia'],
            ['province_id' => 3, 'name' => 'Rio Gallegos'],
            ['province_id' => 2, 'name' => 'Bariloche'],
            ['province_id' => 1, 'name' => 'Veronica'],
            ['province_id' => 1, 'name' => 'Magdalena'],
            ['province_id' => 5, 'name' => 'Corral de Bustos'],
        ];

        City::insert($data);
    }
}
