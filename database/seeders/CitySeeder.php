<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['province_id' => 1, 'name' => 'La Plata', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Ramos Mejia', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 3, 'name' => 'Rio Gallegos', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 2, 'name' => 'Bariloche', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Veronica', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Magdalena', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 5, 'name' => 'Corral de Bustos', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Chascomus', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Gonzalez Catan', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Ituzaingo', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => '25 de Mayo', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 7, 'name' => 'Posadas', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 2, 'name' => 'El Calafate', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 11, 'name' => 'Neuquen', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 4, 'name' => 'Comodoro Rivadavia', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Paso del Rey', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Gral Rodriguez', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Villa Celina', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Merlo', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Beccar', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Vicente Lopez', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 12, 'name' => 'Ciudad Autonoma de Buenos Aires', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 13, 'name' => 'Concepción', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Victoria', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Moreno', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Don Torcuato', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Florida', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Ezeiza', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 14, 'name' => 'Valdivia', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Santa Teresita', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 6, 'name' => 'Salta', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Francisco Alvarez', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Grand Bourg', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 4, 'name' => 'Rada Tilly', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Munro', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Benavidez', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Gral Pacheco', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Pilar', 'created_at' => now(), 'updated_at' => now()],
            ['province_id' => 1, 'name' => 'Mercedes', 'created_at' => now(), 'updated_at' => now()],
        ];

        City::insert($data);
    }
}
