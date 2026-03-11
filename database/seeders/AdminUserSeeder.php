<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        if (! $adminRole) {
            return;
        }

        $email = env('ADMIN_EMAIL', 'admin@example.com');

        if (User::where('email', $email)->exists()) {
            return;
        }

        User::create([
            'id_role' => $adminRole->id,
            'name' => env('ADMIN_NAME', 'Admin'),
            'cpf' => env('ADMIN_CPF', '000.000.000-00'),
            'email' => $email,
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
        ]);
    }
}

