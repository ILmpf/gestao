<?php

declare(strict_types=1);

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\AcaoCalendarioRequest;
use App\Models\AcaoCalendario;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AcaoCalendarioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('configuracoes/AcoesCalendario', [
            'accoes' => AcaoCalendario::orderBy('nome')->get(),
        ]);
    }

    public function store(AcaoCalendarioRequest $request): RedirectResponse
    {
        AcaoCalendario::create($request->validated());

        return back()->with('success', 'Ação criada com sucesso.');
    }

    public function update(AcaoCalendarioRequest $request, AcaoCalendario $acaoCalendario): RedirectResponse
    {
        $acaoCalendario->update($request->validated());

        return back()->with('success', 'Ação atualizada com sucesso.');
    }

    public function destroy(AcaoCalendario $acaoCalendario): RedirectResponse
    {
        $acaoCalendario->delete();

        return back()->with('success', 'Ação eliminada com sucesso.');
    }
}
