<?php

namespace App\Services;

use App\Mail\CredenciaisAcessoMail;
use App\Repositories\GerenteContaRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Conta;

class GerenteContaService extends BaseService
{
    public function __construct(protected GerenteContaRepository $repository) {}

    protected function getRepository(): mixed
    {
        return $this->repository;
    }

    public function store(array $data)
    {
        $gerente = $this->repository->store([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => 2,
            'email_verified_at' => now(),
        ]);

        DB::afterCommit(function () use ($gerente, $data): void {
            Mail::to($gerente->email)->send(
                new CredenciaisAcessoMail(
                    $gerente,
                    $data['password'],
                    'Gerente de Conta'
                )
            );
        });

        return $gerente;
    }

    public function remove(int|string $id)
    {
        if (Conta::where('gerente_conta_id', $id)->exists()) {
            throw new \DomainException('Não é possível remover o gerente enquanto ele possuir clientes na carteira.');
        }

        return $this->repository->remove($id);
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
}
