<?php

declare(strict_types=1);

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\EmpresaRequest;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EmpresaController extends Controller
{
    public function edit(): Response
    {
        $empresa = Empresa::firstOrCreate(['id' => 1]);

        return Inertia::render('configuracoes/Empresa', [
            'empresa' => array_merge($empresa->toArray(), [
                'logo_url' => $empresa->logo
                    ? Storage::disk('public')->url($empresa->logo)
                    : null,
            ]),
        ]);
    }

    public function update(EmpresaRequest $request): RedirectResponse
    {
        $empresa = Empresa::firstOrCreate(['id' => 1]);
        $data = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            if ($empresa->logo) {
                Storage::disk('public')->delete($empresa->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $empresa->update($data);

        cache()->forget('empresa_sidebar');

        return back()->with('success', 'Dados da empresa atualizados com sucesso.');
    }
}
