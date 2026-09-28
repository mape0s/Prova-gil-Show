<?php

namespace App\Services;

use App\Models\Conta;
use App\Models\User;
use App\Repositories\ContaRepository;
use App\Repositories\MovimentacaoRepository;
use DomainException;
use Illuminate\Support\Facades\DB;

class MovimentacaoService extends BaseService
{
    public function __construct(
        protected MovimentacaoRepository $repository,
        protected ContaRepository $contaRepository
    ) {}

    protected function getRepository(): mixed
    {
        return $this->repository;
    }

    public function extrato(int|string $contaId, ?string $inicio = null, ?string $fim = null)
    {
        return $this->repository->porConta($contaId, $inicio, $fim);
    }

    public function pix(Conta $conta, string $emailDestino, float $valor, string $descricao = ''): void
    {
        $this->validarContaMovimentavel($conta);

        if ($valor <= 0) {
            throw new DomainException('O valor do PIX deve ser maior que zero.');
        }

        $destinatario = User::query()
            ->where('email', $emailDestino)
            ->where('role_id', 3)
            ->first();

        if (! $destinatario || ! $destinatario->conta) {
            throw new DomainException('Não existe cliente com este e-mail.');
        }

        if ((int) $destinatario->conta->id === (int) $conta->id) {
            throw new DomainException('Não é possível enviar PIX para a própria conta.');
        }

        DB::transaction(function () use ($conta, $destinatario, $valor, $descricao): void {
            $origem = Conta::query()->lockForUpdate()->findOrFail($conta->id);
            $destino = Conta::query()->lockForUpdate()->findOrFail($destinatario->conta->id);

            if ($origem->status === 'bloqueada') {
                throw new DomainException('Conta bloqueada, não é possível movimentar.');
            }

            if ($destino->status === 'bloqueada') {
                throw new DomainException('A conta do destinatário está bloqueada.');
            }

            if ((float) $origem->saldo + (float) $origem->limite < $valor) {
                throw new DomainException('Saldo + limite insuficiente.');
            }

            $origem->saldo -= $valor;
            $destino->saldo += $valor;
            $origem->save();
            $destino->save();

            $this->repository->store([
                'conta_id' => $origem->id,
                'tipo' => 'pix',
                'valor' => $valor,
                'natureza' => 'saida',
                'descricao' => ($descricao !== '' ? $descricao : 'PIX enviado').' para '.$destinatario->email,
            ]);

            $this->repository->store([
                'conta_id' => $destino->id,
                'tipo' => 'pix',
                'valor' => $valor,
                'natureza' => 'entrada',
                'descricao' => 'PIX recebido de '.$origem->cliente->email,
            ]);
        });
    }

    public function aplicar(Conta $conta, string $tipo, float $valor): void
    {
        $this->validarContaMovimentavel($conta);
        $this->validarValor($valor);

        $campo = 'saldo_'.$tipo;

        DB::transaction(function () use ($conta, $tipo, $campo, $valor): void {
            $contaAtual = Conta::query()->lockForUpdate()->findOrFail($conta->id);

            // O limite de crédito não financia investimentos.
            if ((float) $contaAtual->saldo < $valor) {
                throw new DomainException('Saldo insuficiente para realizar a aplicação.');
            }

            $contaAtual->saldo -= $valor;
            $contaAtual->{$campo} += $valor;
            $contaAtual->save();

            $this->repository->store([
                'conta_id' => $contaAtual->id,
                'tipo' => 'aplicacao_'.$tipo,
                'valor' => $valor,
                'natureza' => 'saida',
                'descricao' => 'Aplicação em '.strtoupper($tipo),
            ]);
        });
    }

    public function resgatar(Conta $conta, string $tipo, float $valor): void
    {
        $this->validarContaMovimentavel($conta);
        $this->validarValor($valor);

        $campo = 'saldo_'.$tipo;

        DB::transaction(function () use ($conta, $campo, $tipo, $valor): void {
            $contaAtual = Conta::query()->lockForUpdate()->findOrFail($conta->id);

            if ((float) $contaAtual->{$campo} < $valor) {
                throw new DomainException('Saldo insuficiente na aplicação.');
            }

            $contaAtual->saldo += $valor;
            $contaAtual->{$campo} -= $valor;
            $contaAtual->save();

            $this->repository->store([
                'conta_id' => $contaAtual->id,
                'tipo' => 'resgate_'.$tipo,
                'valor' => $valor,
                'natureza' => 'entrada',
                'descricao' => 'Resgate de '.strtoupper($tipo),
            ]);
        });
    }

    private function validarContaMovimentavel(Conta $conta): void
    {
        if ($conta->status === 'bloqueada') {
            throw new DomainException('Conta bloqueada, não é possível movimentar.');
        }
    }

    private function validarValor(float $valor): void
    {
        if ($valor <= 0) {
            throw new DomainException('O valor deve ser maior que zero.');
        }
    }
}
