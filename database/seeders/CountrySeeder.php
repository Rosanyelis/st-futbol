<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Argentina','code' => 'ARG', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Brasil','code' => 'BRA', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Uruguay','code' => 'URU', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paraguay','code' => 'PAR', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bolivia','code' => 'BOL', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chile','code' => 'CHI', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        Country::insert($data);
    }
}
