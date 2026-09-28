<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MovimentacaoService;
use DomainException;
use Illuminate\Http\Request;

class MovimentacaoController extends Controller
{
    public function __construct(protected MovimentacaoService $service) {}

    public function extrato(Request $request)
    {
        $conta = $request->user()->conta;

        if (! $conta) {
            return response()->json(['message' => 'Conta não encontrada.'], 404);
        }

        if ($conta->status === 'bloqueada') {
            return response()->json(['message' => 'Conta bloqueada, não é possível gerar extrato.'], 422);
        }

        $data = $request->validate([
            'inicio' => ['nullable', 'date'],
            'fim' => ['nullable', 'date', 'after_or_equal:inicio'],
        ]);

        return response()->json($this->service->extrato(
            $conta->id,
            $data['inicio'] ?? null,
            $data['fim'] ?? null
        ));
    }

    public function pix(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'descricao' => ['nullable', 'string', 'max:150'],
        ]);

        $conta = $request->user()->conta;
        if (! $conta) {
            return response()->json(['message' => 'Conta não encontrada.'], 404);
        }

        try {
            $this->service->pix($conta, $data['email'], (float) $data['valor'], $data['descricao'] ?? '');
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'PIX realizado com sucesso.']);
    }

    public function aplicar(Request $request)
    {
        $data = $request->validate([
            'tipo' => ['required', 'in:cdb,cdi,poupanca'],
            'valor' => ['required', 'numeric', 'min:0.01'],
        ]);

        $conta = $request->user()->conta;
        if (! $conta) {
            return response()->json(['message' => 'Conta não encontrada.'], 404);
        }

        try {
            $this->service->aplicar($conta, $data['tipo'], (float) $data['valor']);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Aplicação realizada com sucesso.']);
    }

    public function resgatar(Request $request)
    {
        $data = $request->validate([
            'tipo' => ['required', 'in:cdb,cdi,poupanca'],
            'valor' => ['required', 'numeric', 'min:0.01'],
        ]);

        $conta = $request->user()->conta;
        if (! $conta) {
            return response()->json(['message' => 'Conta não encontrada.'], 404);
        }

        try {
            $this->service->resgatar($conta, $data['tipo'], (float) $data['valor']);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Resgate realizado com sucesso.']);
    }
}
