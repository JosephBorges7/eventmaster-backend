<?php

namespace Database\Seeders;

use App\Models\Local;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locals = [
            [
                'name' => 'Convention Center',
                'street' => 'Av. Principal',
                'number_street' => '1000',
                'neighborhood' => 'Centro',
                'max_people' => 5000,
            ],
            [
                'name' => 'City Hall Auditorium',
                'street' => 'Rua das Flores',
                'number_street' => '250',
                'neighborhood' => 'Jardins',
                'max_people' => 800,
            ],
            [
                'name' => 'Central Park Stage',
                'street' => 'Parque Municipal',
                'number_street' => 'S/N',
                'neighborhood' => 'Área Verde',
                'max_people' => 10000,
            ],
            [
                'name' => 'Tech Hub',
                'street' => 'Av. Inovação',
                'number_street' => '42',
                'neighborhood' => 'Tech District',
                'max_people' => 200,
            ],
            [
                'name' => 'Sports Arena',
                'street' => 'Estádio Municipal',
                'number_street' => '1',
                'neighborhood' => 'Zona Norte',
                'max_people' => 15000,
            ],
        ];

        Local::insert($locals);
    }
}
