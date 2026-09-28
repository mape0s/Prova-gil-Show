<?php

namespace App\Services;

use App\Mail\CredenciaisAcessoMail;
use App\Repositories\ClienteRepository;
use App\Repositories\ContaRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ClienteService extends BaseService
{
    public function __construct(
        protected ClienteRepository $repository,
        protected ContaRepository $contaRepository
    ) {}

    protected function getRepository(): mixed
    {
        return $this->repository;
    }

    public function allForGerente(int $gerenteId)
    {
        return $this->contaRepository
            ->clientesDoGerente($gerenteId)
            ->map(fn ($conta) => $conta->cliente)
            ->filter();
    }

    public function findForGerente(int|string $id, int $gerenteId)
    {
        $conta = $this->contaRepository
            ->clientesDoGerente($gerenteId)
            ->firstWhere('user_id', (int) $id);

        return $conta?->cliente;
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $cliente = $this->repository->store([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => 3,
                'email_verified_at' => now(),
            ]);

            $this->contaRepository->store([
                'user_id' => $cliente->id,
                'numero' => $this->proximoNumeroConta(),
                'gerente_conta_id' => Auth::id(),
                'saldo' => max(0, (float) ($data['saldo'] ?? 0)),
                'limite' => max(0, (float) ($data['limite'] ?? 0)),
            ]);

            DB::afterCommit(function () use ($cliente, $data): void {
                Mail::to($cliente->email)->send(
                    new CredenciaisAcessoMail($cliente, $data['password'], 'Cliente')
                );
            });

            return $cliente;
        });
    }

    private function proximoNumeroConta(): string
    {
        do {
            $numero = str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (\App\Models\Conta::where('numero', $numero)->exists());

        return $numero;
    }

    public function update(array $data, int|string $id)
    {
        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        return $this->repository->update($update, $id);
    }

    public function remove(int|string $id)
    {
        return DB::transaction(function () use ($id) {
            $cliente = $this->repository->find($id);
            if (! $cliente) {
                abort(404);
            }

            \App\Models\Conta::where('user_id', $cliente->id)->delete();
            return $this->repository->remove($id);
        });
    }
}
