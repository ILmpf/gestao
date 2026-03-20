<?php

declare(strict_types=1);

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Http\Requests\Config\ArtigoRequest;
use App\Models\Artigo;
use App\Models\TaxaIva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ArtigoController extends Controller
{
    private function artigoImagemUrl(?string $path): ?string
    {
        return $path ? url('files/'.$path) : null;
    }

    /** @return array<string, mixed> */
    private function mapArtigo(Artigo $a): array
    {
        return [
            'id' => $a->id,
            'referencia' => $a->referencia,
            'nome' => $a->nome,
            'descricao' => $a->descricao,
            'preco' => $a->preco,
            'taxa_iva_id' => $a->taxa_iva_id,
            'taxa_iva_nome' => $a->taxaIva?->nome,
            'imagem_artigo' => $a->imagem_artigo,
            'imagem_url' => $this->artigoImagemUrl($a->imagem_artigo),
            'notas' => $a->notas,
            'estado' => $a->estado,
        ];
    }

    public function index(): Response
    {
        $artigos = Artigo::with('taxaIva')
            ->orderBy('referencia')
            ->get()
            ->map(fn (Artigo $a) => $this->mapArtigo($a));

        return Inertia::render('configuracoes/Artigos', [
            'artigos' => $artigos,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('configuracoes/ArtigoForm', [
            'taxas' => TaxaIva::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'taxa']),
        ]);
    }

    public function store(ArtigoRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('imagem_artigo');

        if ($request->hasFile('imagem_artigo')) {
            $file = $request->file('imagem_artigo');
            $filename = $file->hashName();
            $file->storeAs('private/artigos', $filename);
            $data['imagem_artigo'] = 'artigos/'.$filename;
        }

        Artigo::create($data);

        return redirect()->route('configuracoes.artigos.index')
            ->with('success', 'Artigo criado com sucesso.');
    }

    public function edit(Artigo $artigo): Response
    {
        return Inertia::render('configuracoes/ArtigoForm', [
            'artigo' => $this->mapArtigo($artigo),
            'taxas' => TaxaIva::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'taxa']),
        ]);
    }

    public function update(ArtigoRequest $request, Artigo $artigo): RedirectResponse
    {
        $data = $request->safe()->except('imagem_artigo');

        if ($request->hasFile('imagem_artigo')) {
            if ($artigo->imagem_artigo) {
                Storage::delete('private/'.$artigo->imagem_artigo);
            }
            $file = $request->file('imagem_artigo');
            $filename = $file->hashName();
            $file->storeAs('private/artigos', $filename);
            $data['imagem_artigo'] = 'artigos/'.$filename;
        }

        $artigo->update($data);

        return redirect()->route('configuracoes.artigos.index')
            ->with('success', 'Artigo atualizado com sucesso.');
    }

    public function destroy(Artigo $artigo): RedirectResponse
    {
        if ($artigo->imagem_artigo) {
            Storage::delete('private/'.$artigo->imagem_artigo);
        }
        $artigo->delete();

        return redirect()->route('configuracoes.artigos.index')
            ->with('success', 'Artigo eliminado com sucesso.');
    }
}
