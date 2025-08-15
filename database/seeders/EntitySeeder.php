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
            ['name' => 'Banco Frances Sebastian', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Banco Galicia Sebastian', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mercado Pago', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'UALA', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Naranja X', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NuBank', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pesos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dolares', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transf. de Terceros', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Banco Frances Favy', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($data as $item) {
            Entity::updateOrCreate($item);
        }
    }
}
 