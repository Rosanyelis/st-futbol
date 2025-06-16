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
        CategorySupplier::factory()->count(10)->create();
    }
}
