<?php

namespace Tests\Feature;

use App\Models\Conta;
use App\Models\User;
use App\Services\MovimentacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimentacaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pix_consumes_balance_and_limit_until_available_credit_reaches_zero(): void
    {
        $this->seed();

        $gerente = User::factory()->create(['role_id' => 2]);
        $origem = User::factory()->create(['role_id' => 3]);
        $destino = User::factory()->create(['role_id' => 3]);

        $contaOrigem = Conta::create([
            'numero' => '90000001',
            'user_id' => $origem->id,
            'gerente_conta_id' => $gerente->id,
            'saldo' => 1000,
            'limite' => 500,
        ]);

        Conta::create([
            'numero' => '90000002',
            'user_id' => $destino->id,
            'gerente_conta_id' => $gerente->id,
            'saldo' => 0,
            'limite' => 0,
        ]);

        $service = app(MovimentacaoService::class);
        $service->pix($contaOrigem->fresh(), $destino->email, 1200);
        $this->assertEquals(
            -200.0,
            (float) $contaOrigem->fresh()->saldo
        );

        $service->pix($contaOrigem->fresh(), $destino->email, 300);
        $this->assertEquals(
            -500.0,
            (float) $contaOrigem->fresh()->saldo
        );

        $this->expectException(\DomainException::class);
        $service->pix($contaOrigem->fresh(), $destino->email, 1);
    }
}
