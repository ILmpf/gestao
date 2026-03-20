<?php

declare(strict_types=1);

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\PaisRequest;
use App\Models\Pais;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaisController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('configuracoes/Paises', [
            'paises' => Pais::orderBy('nome')->get(),
        ]);
    }

    public function store(PaisRequest $request): RedirectResponse
    {
        Pais::create($request->validated());

        return back()->with('success', 'País criado com sucesso.');
    }

    public function update(PaisRequest $request, Pais $pais): RedirectResponse
    {
        $pais->update($request->validated());

        return back()->with('success', 'País atualizado com sucesso.');
    }

    public function destroy(Pais $pais): RedirectResponse
    {
        $pais->delete();

        return back()->with('success', 'País eliminado com sucesso.');
    }
}
