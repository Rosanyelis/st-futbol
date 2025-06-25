<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Argentina','code' => 'ARG'  ],
            ['name' => 'Brasil','code' => 'BRA'  ],
            ['name' => 'Uruguay','code' => 'URU'  ],
            ['name' => 'Paraguay','code' => 'PAR'  ],
            ['name' => 'Bolivia','code' => 'BOL'  ],
            ['name' => 'Chile','code' => 'CHI'  ],
        ];
        
        Country::insert($data);
    }
}
