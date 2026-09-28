<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['GERENTE GERAL', 'GERENTE CONTA', 'CLIENTE'] as $name) {
            DB::table('roles')->updateOrInsert(['name' => $name], ['name' => $name, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
