<?php

namespace Database\Seeders;

use App\Models\Entity;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Banco Frances'],
            ['name' => 'Banco Galicia'],
            ['name' => 'Mercado Pago'],
            ['name' => 'UALA'],
            ['name' => 'Naranja X'],
            ['name' => 'NuBank'],
        ];

        foreach ($data as $item) {
            Entity::updateOrCreate($item);
        }
    }
}
