<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['country_id' => 1, 'name' => 'Buenos Aires'],
            ['country_id' => 1, 'name' => 'Rio Negro'],
            ['country_id' => 1, 'name' => 'Santa Cruz'],
            ['country_id' => 1, 'name' => 'Chubut'],
            ['country_id' => 1, 'name' => 'Cordoba'],
            ['country_id' => 1, 'name' => 'Salta'],
            ['country_id' => 1, 'name' => 'Misiones'],
            ['country_id' => 1, 'name' => 'La Pampa'],
            ['country_id' => 2, 'name' => 'Santa Catarina'],
            ['country_id' => 2, 'name' => 'Rio Grande do Sul'],
        ];

        Province::insert($data);
    }
}
