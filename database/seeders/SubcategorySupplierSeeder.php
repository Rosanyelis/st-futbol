<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubcategorySupplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubcategorySupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "category_supplier_id" => 4,
                "name" => "Predio Martín",
            ],
            [
                "category_supplier_id" => 4,
                "name" => "Predio Marcela",
            ],
            [
                "category_supplier_id" => 4,
                "name" => "Predio Extranjeros",
            ],
            [
                "category_supplier_id" => 2,
                "name" => "Escenario",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Los Angeles",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Parque",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Luna Morena",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Espeleta",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Mónaco",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Riviera",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Don Roque",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Dommer",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Santa Teresita Beach",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "La Palmeras",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "17 de Noviembre",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Paucam",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Campos de Mar",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Mallak",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Alfonso",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Costa Marina",
            ],
            [
                "category_supplier_id" => 1,
                "name" => "Gran Lido",
            ],
            [
                "category_supplier_id" => 8,
                "name" => "Decoración Predio",
            ],
            [
                "category_supplier_id" => 16,
                "name" => "Libritos",
            ],
            [
                "category_supplier_id" => 16,
                "name" => "Sueldos",
            ],
            [
                "category_supplier_id" => 16,
                "name" => "Revelado",
            ],
        ];

        foreach ($data as $item) {
            SubcategorySupplier::updateOrCreate($item);
        }
    }
}
