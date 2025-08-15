<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategorySupplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Hotel', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Estructura/Sonido', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Apertura', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Almuerzos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Carpas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Varios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CafAccess', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Predio', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cenas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Viajes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Seguros', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Merchandising', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Remeras de Regalo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Trofeos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Fotografía', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Traslados Internos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Imprenta', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Arbitros', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salud', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Asitentes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Radio', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Limpieza', 'created_at' => now(), 'updated_at' => now()],
        ];
        CategorySupplier::insert($data);
    }
}
