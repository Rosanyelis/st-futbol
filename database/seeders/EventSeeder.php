<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'STC Floripa', 'url_images' => 'events/1750112058_6850973a68bd3.png', 'start_date' => '2026-04-05', 'end_date' => '2026-04-11', 'year' => '2026'],
            ['name' => 'Santa Teresita Cup', 'url_images' => 'events/1750112126_6850977e4ac39.png', 'start_date' => '2025-12-15', 'end_date' => '2025-12-21', 'year' => '2025'],
            ['name' => 'STC Buenos Aires', 'url_images' => 'events/1750112186_685097ba0dcba.png', 'start_date' => '2025-07-20', 'end_date' => '2025-07-26', 'year' => '2025'],
        ];

        foreach ($data as $item) {
            Event::updateOrCreate($item);
        }

    }
}
