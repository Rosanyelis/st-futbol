<?php

namespace Database\Seeders;

use App\Models\Bussines;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BussinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'name' => 'STC Torneos',
            'email' => 'stc@gmail.com',
            'phone' => '1234567890',
            'address' => 'Calle 123',
            'logo' => 'stc.png',
            'cuit' => '1234567890',
        ];

        Bussines::updateOrCreate($data);
    }
}
