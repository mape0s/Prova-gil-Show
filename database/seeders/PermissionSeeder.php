<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = DB::table('roles')->pluck('id', 'name');
        $resources = DB::table('resources')->pluck('id', 'name');

        $matrix = [
            'GERENTE GERAL' => ['gerente-conta.manter', 'limite.aprovar', 'auditoria.visualizar'],
            'GERENTE CONTA' => ['cliente.manter', 'extrato-cliente.visualizar', 'conta.bloquear'],
            'CLIENTE' => ['saldo.visualizar', 'extrato.visualizar', 'movimentacao.efetuar'],
        ];

        foreach ($matrix as $role => $items) {
            foreach ($items as $resource) {
                DB::table('permissions')->updateOrInsert(
                    ['role_id' => $roles[$role], 'resource_id' => $resources[$resource]],
                    ['permission' => true]
                );
            }
        }
    }
}
