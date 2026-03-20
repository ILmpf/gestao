<?php

declare(strict_types=1);

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\TipoCalendarioRequest;
use App\Models\TipoCalendario;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TipoCalendarioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('configuracoes/TiposCalendario', [
            'tipos' => TipoCalendario::orderBy('nome')->get(),
        ]);
    }

    public function store(TipoCalendarioRequest $request): RedirectResponse
    {
        TipoCalendario::create($request->validated());

        return back()->with('success', 'Tipo criado com sucesso.');
    }

    public function update(TipoCalendarioRequest $request, TipoCalendario $tipoCalendario): RedirectResponse
    {
        $tipoCalendario->update($request->validated());

        return back()->with('success', 'Tipo atualizado com sucesso.');
    }

    public function destroy(TipoCalendario $tipoCalendario): RedirectResponse
    {
        $tipoCalendario->delete();

        return back()->with('success', 'Tipo eliminado com sucesso.');
    }
}
