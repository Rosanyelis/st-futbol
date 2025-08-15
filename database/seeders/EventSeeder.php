<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'STC Floripa 2026', 'url_images' => 'events/1750112058_6850973a68bd3.png', 'start_date' => '2026-04-05', 'end_date' => '2026-04-11', 'year' => '2026', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Santa Teresita Cup 2025', 'url_images' => 'events/1750112126_6850977e4ac39.png', 'start_date' => '2025-12-15', 'end_date' => '2025-12-21', 'year' => '2025', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'STC Buenos Aires 2025', 'url_images' => 'events/1750112186_685097ba0dcba.png', 'start_date' => '2025-07-20', 'end_date' => '2025-07-26', 'year' => '2025', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'STC Floripa 2025', 'url_images' => 'events/1751978303_686d113fba5e1.png', 'start_date' => '2025-04-13', 'end_date' => '2025-04-19', 'year' => '2025', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($data as $item) {
            Event::updateOrCreate($item);
        }

    }
}
