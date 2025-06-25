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
            ['name' => 'Hotel'],
            ['name' => 'Estructura/Sonido'],
            ['name' => 'Apertura'],
            ['name' => 'Almuerzos'],
            ['name' => 'Carpas'],
            ['name' => 'Varios'],
            ['name' => 'CafAccess'],
            ['name' => 'Predio'],
            ['name' => 'Cenas'],
            ['name' => 'Viajes'],
            ['name' => 'Seguros'],
            ['name' => 'Merchandising'],
            ['name' => 'Remeras de Regalo'],
            ['name' => 'Trofeos'],
            ['name' => 'Fotografía'],
            ['name' => 'Traslados Internos'],
        ];
        CategorySupplier::insert($data);
    }
}
