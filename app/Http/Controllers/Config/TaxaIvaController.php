<?php

declare(strict_types=1);

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\TaxaIvaRequest;
use App\Models\TaxaIva;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TaxaIvaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('configuracoes/TaxasIva', [
            'taxas' => TaxaIva::orderBy('nome')->get(),
        ]);
    }

    public function store(TaxaIvaRequest $request): RedirectResponse
    {
        TaxaIva::create($request->validated());

        return back()->with('success', 'Taxa de IVA criada com sucesso.');
    }

    public function update(TaxaIvaRequest $request, TaxaIva $taxaIva): RedirectResponse
    {
        $taxaIva->update($request->validated());

        return back()->with('success', 'Taxa de IVA atualizada com sucesso.');
    }

    public function destroy(TaxaIva $taxaIva): RedirectResponse
    {
        $taxaIva->delete();

        return back()->with('success', 'Taxa de IVA eliminada com sucesso.');
    }
}
