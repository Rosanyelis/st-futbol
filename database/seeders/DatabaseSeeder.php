<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // $this->call([
        //     CategorySupplierSeeder::class,
        //     SubcategorySupplierSeeder::class,
        //     CategoryExpenseSeeder::class,
        //     SubcategoryExpenseSeeder::class,
        //     CategoryMethodPaymentSeeder::class,
        //     CurrencySeeder::class,
        //     CountrySeeder::class,
        //     ProvinceSeeder::class,
        //     CitySeeder::class,
        //     EntitySeeder::class,
        // ]);

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'administrador@gmail.com',
            'password' => Hash::make('admin'),
        ]);
    }
}
