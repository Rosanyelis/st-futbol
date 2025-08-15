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
            ['country_id' => 1, 'name' => 'Buenos Aires', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'Rio Negro', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'Santa Cruz', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'Chubut', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'Cordoba', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'Salta', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'Misiones', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'La Pampa', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 2, 'name' => 'Santa Catarina', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 2, 'name' => 'Rio Grande do Sul', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'Neuquen', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 1, 'name' => 'CABA', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 6, 'name' => 'Concepcion', 'created_at' => now(), 'updated_at' => now()],
            ['country_id' => 6, 'name' => 'Region de los rios', 'created_at' => now(), 'updated_at' => now()],  
        ];

        Province::insert($data);
    }
}
