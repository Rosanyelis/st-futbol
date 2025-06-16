<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryMethodPayment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoryMethodPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryMethodPayment::factory()->count(10)->create();
    }
}
