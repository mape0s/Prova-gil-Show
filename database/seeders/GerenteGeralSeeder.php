<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GerenteGeralSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'gerente.geral@banco.test'],
            ['name' => 'Gerente Geral', 'password' => Hash::make('senha123'), 'role_id' => 1, 'email_verified_at' => now()]
        );
    }
}
