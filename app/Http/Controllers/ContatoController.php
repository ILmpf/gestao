<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContatoRequest;
use App\Models\Contato;
use App\Models\Entidade;
use App\Models\FuncaoContato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ContatoController extends Controller
{
    /** @return array<string, mixed> */
    private function mapContato(Contato $c): array
    {
        return [
            'id' => $c->id,
            'numero' => $c->numero,
            'entidade_id' => $c->entidade_id,
            'entidade_nome' => $c->entidade?->nome,
            'primeiro_nome' => $c->primeiro_nome,
            'apelido' => $c->apelido,
            'funcao_contacto_id' => $c->funcao_contacto_id,
            'funcao_nome' => $c->funcaoContacto?->nome,
            'telefone' => $c->telefone,
            'telemovel' => $c->telemovel,
            'email' => $c->email,
            'notas' => $c->notas,
            'estado' => $c->estado,
        ];
    }

    private function entidadesParaSelect(): Collection
    {
        return Entidade::get(['id', 'nome'])
            ->sortBy('nome')
            ->values()
            ->map(fn (Entidade $e) => ['id' => $e->id, 'nome' => $e->nome]);
    }

    public function index(): Response
    {
        $contatos = Contato::with(['entidade', 'funcaoContacto'])
            ->orderBy('id')
            ->get()
            ->map(fn (Contato $c) => $this->mapContato($c));

        return Inertia::render('contatos/Index', [
            'contatos' => $contatos,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('contatos/Form', [
            'entidades' => $this->entidadesParaSelect(),
            'funcoes' => FuncaoContato::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(ContatoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['numero'] = (Contato::max('numero') ?? 0) + 1;

        Contato::create($data);

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto criado com sucesso.');
    }

    public function edit(Contato $contato): Response
    {
        return Inertia::render('contatos/Form', [
            'contato' => $this->mapContato($contato->load(['entidade', 'funcaoContacto'])),
            'entidades' => $this->entidadesParaSelect(),
            'funcoes' => FuncaoContato::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function update(ContatoRequest $request, Contato $contato): RedirectResponse
    {
        $contato->update($request->validated());

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto atualizado com sucesso.');
    }

    public function destroy(Contato $contato): RedirectResponse
    {
        $contato->delete();

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto eliminado com sucesso.');
    }
}
