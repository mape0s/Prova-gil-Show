<?php

namespace Database\Seeders;

use App\Models\Conta;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DadosTesteSeeder extends Seeder
{
    public function run(): void
    {
        $gerente = User::updateOrCreate(
            ['email' => 'gerente.conta@banco.test'],
            ['name' => 'Gerente Teste', 'password' => Hash::make('senha123'), 'role_id' => 2, 'email_verified_at' => now()]
        );

        $cliente = User::updateOrCreate(
            ['email' => 'cliente@banco.test'],
            ['name' => 'Cliente Teste', 'password' => Hash::make('senha123'), 'role_id' => 3, 'email_verified_at' => now()]
        );

        Conta::updateOrCreate(
            ['user_id' => $cliente->id],
            ['gerente_conta_id' => $gerente->id, 'saldo' => 1000, 'limite' => 500, 'status' => 'ativa']
        );
    }
}
