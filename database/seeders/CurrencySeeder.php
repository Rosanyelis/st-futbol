<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Pesos', 'symbol' => '$'],
            ['name' => 'Dolares', 'symbol' => 'u$s'],
            ['name' => 'Reales', 'symbol' => 'R$'],
            ['name' => 'Cripto', 'symbol' => 'USDT'],
        ];
        Currency::insert($data);
        
    }
}
