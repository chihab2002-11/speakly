<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['name' => 'A101', 'capacity' => 30, 'location' => 'Main Building'],
            ['name' => 'B203', 'capacity' => 25, 'location' => 'Main Building'],
            ['name' => 'Lab 2', 'capacity' => 20, 'location' => 'Annex'],
            ['name' => 'C301', 'capacity' => 35, 'location' => 'North Wing'],
        ];

        foreach ($rooms as $room) {
            Room::query()->updateOrCreate(
                ['name' => $room['name']],
                [
                    'capacity' => $room['capacity'],
                    'location' => $room['location'],
                ]
            );
        }
    }
}
