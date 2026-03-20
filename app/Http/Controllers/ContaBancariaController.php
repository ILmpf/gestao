<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContaBancariaRequest;
use App\Models\ContaBancaria;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContaBancariaController extends Controller
{
    public function index(): Response
    {
        $contas = ContaBancaria::orderBy('nome')->get()->map(fn (ContaBancaria $c) => [
            'id' => $c->id,
            'nome' => $c->nome,
            'iban' => $c->iban,
            'bic' => $c->bic,
            'ativa' => $c->ativa,
        ]);

        return Inertia::render('financeiro/contas-bancarias/Index', [
            'contas' => $contas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('financeiro/contas-bancarias/Form');
    }

    public function store(ContaBancariaRequest $request): RedirectResponse
    {
        ContaBancaria::create($request->validated());

        return redirect()->route('financeiro.contas-bancarias.index')
            ->with('success', 'Conta bancária criada com sucesso.');
    }

    public function edit(ContaBancaria $contaBancaria): Response
    {
        return Inertia::render('financeiro/contas-bancarias/Form', [
            'conta' => [
                'id' => $contaBancaria->id,
                'nome' => $contaBancaria->nome,
                'iban' => $contaBancaria->iban,
                'bic' => $contaBancaria->bic,
                'ativa' => $contaBancaria->ativa,
            ],
        ]);
    }

    public function update(ContaBancariaRequest $request, ContaBancaria $contaBancaria): RedirectResponse
    {
        $contaBancaria->update($request->validated());

        return redirect()->route('financeiro.contas-bancarias.index')
            ->with('success', 'Conta bancária atualizada com sucesso.');
    }

    public function destroy(ContaBancaria $contaBancaria): RedirectResponse
    {
        $contaBancaria->delete();

        return redirect()->route('financeiro.contas-bancarias.index')
            ->with('success', 'Conta bancária eliminada com sucesso.');
    }
}
