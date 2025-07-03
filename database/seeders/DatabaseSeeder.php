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
        $this->call([
            CategorySupplierSeeder::class,
            CategoryExpenseSeeder::class,
            CategoryIncomesSeeder::class,
            CategoryEgressSeeder::class,
            CategoryMethodPaymentSeeder::class,
            CurrencySeeder::class,
            CountrySeeder::class,
            ProvinceSeeder::class,
            CitySeeder::class,
            EntitySeeder::class,
            EventSeeder::class,
            SubcategorySupplierSeeder::class,
            SubcategoryExpenseSeeder::class,
            BussinesSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Sebastian Torres',
            'email' => 'eventosdeportivos1977@hotmail.com',
            'password' => Hash::make('25865975'),
        ]);
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'administrador@gmail.com',
            'password' => Hash::make('admin'),
        ]);
         User::factory()->create([
            'name' => 'Eliana',
            'email' => 'stc.eliana@gmail.com',
            'password' => Hash::make('31674927'),
        ]);
    }
}
