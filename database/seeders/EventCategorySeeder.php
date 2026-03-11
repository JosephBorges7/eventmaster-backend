<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Concert'],
            ['name' => 'Conference'],
            ['name' => 'Workshop'],
            ['name' => 'Festival'],
            ['name' => 'Sports'],
            ['name' => 'Theater'],
            ['name' => 'Exhibition'],
            ['name' => 'Meetup'],
        ];

        EventCategory::insert($categories);
    }
}
