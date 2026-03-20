<?php

declare(strict_types=1);

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\FuncaoContatoRequest;
use App\Models\FuncaoContato;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FuncaoContatoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('configuracoes/FuncoesContato', [
            'funcoes' => FuncaoContato::orderBy('nome')->get(),
        ]);
    }

    public function store(FuncaoContatoRequest $request): RedirectResponse
    {
        FuncaoContato::create($request->validated());

        return back()->with('success', 'Função criada com sucesso.');
    }

    public function update(FuncaoContatoRequest $request, FuncaoContato $funcaoContato): RedirectResponse
    {
        $funcaoContato->update($request->validated());

        return back()->with('success', 'Função atualizada com sucesso.');
    }

    public function destroy(FuncaoContato $funcaoContato): RedirectResponse
    {
        $funcaoContato->delete();

        return back()->with('success', 'Função eliminada com sucesso.');
    }
}
