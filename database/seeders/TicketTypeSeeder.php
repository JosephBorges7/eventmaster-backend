<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Inteira',
            'Meia',
            'VIP',
        ];

        foreach ($types as $name) {
            TicketType::firstOrCreate(['name' => $name]);
        }
    }
}

