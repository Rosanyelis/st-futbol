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
        SubcategorySupplier::factory()->count(10)->create();
    }
}
