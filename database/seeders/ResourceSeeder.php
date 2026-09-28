<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'gerente-conta.manter', 'limite.aprovar', 'auditoria.visualizar',
            'cliente.manter', 'extrato-cliente.visualizar', 'conta.bloquear',
            'saldo.visualizar', 'extrato.visualizar', 'movimentacao.efetuar',
        ] as $name) {
            DB::table('resources')->updateOrInsert(['name' => $name], ['name' => $name, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
