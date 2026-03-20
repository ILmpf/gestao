<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EntidadeRequest;
use App\Models\Entidade;
use App\Models\Pais;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EntidadeController extends Controller
{
    private function getTipo(): ?string
    {
        $name = request()->route()?->getName() ?? '';

        if (str_starts_with($name, 'entidades')) {
            return null;
        }

        return str_starts_with($name, 'fornecedores') ? 'fornecedor' : 'cliente';
    }

    private function getPaises(): Collection
    {
        return Pais::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'codigo']);
    }

    /** @return array<string, mixed> */
    private function mapEntidade(Entidade $e): array
    {
        return [
            'id' => $e->id,
            'tipos' => $e->tipos,
            'numero_cliente' => $e->numero_cliente,
            'numero_fornecedor' => $e->numero_fornecedor,
            'nif' => $e->nif,
            'nome' => $e->nome,
            'morada' => $e->morada,
            'codigo_postal' => $e->codigo_postal,
            'cidade' => $e->cidade,
            'pais_id' => $e->pais_id,
            'pais_nome' => $e->pais?->nome,
            'telefone' => $e->telefone,
            'telemovel' => $e->telemovel,
            'website' => $e->website,
            'email' => $e->email,
            'notas' => $e->notas,
            'prazo_pagamento_dias' => $e->prazo_pagamento_dias,
            'estado' => $e->estado,
        ];
    }

    public function index(): Response
    {
        $tipo = $this->getTipo();

        $entidades = Entidade::with('pais')
            ->when($tipo === 'cliente', fn ($q) => $q->clientes())
            ->when($tipo === 'fornecedor', fn ($q) => $q->fornecedores())
            ->orderBy('id')
            ->get()
            ->map(fn (Entidade $e) => $this->mapEntidade($e));

        return Inertia::render('entidades/Index', [
            'tipo' => $tipo,
            'entidades' => $entidades,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('entidades/Form', [
            'tipo' => $this->getTipo(),
            'paises' => $this->getPaises(),
        ]);
    }

    public function store(EntidadeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tipo = $this->getTipo();

        if (! empty($data['nif'])) {
            $data['nif_hash'] = hash('sha256', (string) $data['nif']);
        }

        DB::transaction(function () use (&$data): void {
            if (in_array('cliente', (array) $data['tipos'], true)) {
                $data['numero_cliente'] = (Entidade::max('numero_cliente') ?? 0) + 1;
            }
            if (in_array('fornecedor', (array) $data['tipos'], true)) {
                $data['numero_fornecedor'] = (Entidade::max('numero_fornecedor') ?? 0) + 1;
            }
            Entidade::create($data);
        });

        $route = match ($tipo) {
            'fornecedor' => 'fornecedores.index',
            'cliente' => 'clientes.index',
            default => 'entidades.index',
        };

        return redirect()->route($route)->with('success', 'Entidade criada com sucesso.');
    }

    public function edit(Entidade $entidade): Response
    {
        return Inertia::render('entidades/Form', [
            'tipo' => $this->getTipo(),
            'entidade' => $this->mapEntidade($entidade),
            'paises' => $this->getPaises(),
        ]);
    }

    public function update(EntidadeRequest $request, Entidade $entidade): RedirectResponse
    {
        $data = $request->validated();

        if (array_key_exists('nif', $data)) {
            $data['nif_hash'] = empty($data['nif'])
                ? null
                : hash('sha256', (string) $data['nif']);
        }

        DB::transaction(function () use (&$data, $entidade): void {
            if (in_array('cliente', (array) $data['tipos'], true) && is_null($entidade->numero_cliente)) {
                $data['numero_cliente'] = (Entidade::max('numero_cliente') ?? 0) + 1;
            }
            if (in_array('fornecedor', (array) $data['tipos'], true) && is_null($entidade->numero_fornecedor)) {
                $data['numero_fornecedor'] = (Entidade::max('numero_fornecedor') ?? 0) + 1;
            }
            $entidade->update($data);
        });

        $tipo = $this->getTipo();
        $route = match ($tipo) {
            'fornecedor' => 'fornecedores.index',
            'cliente' => 'clientes.index',
            default => 'entidades.index',
        };

        return redirect()->route($route)->with('success', 'Entidade atualizada com sucesso.');
    }

    public function destroy(Entidade $entidade): RedirectResponse
    {
        $entidade->delete();

        $tipo = $this->getTipo();
        $route = match ($tipo) {
            'fornecedor' => 'fornecedores.index',
            'cliente' => 'clientes.index',
            default => 'entidades.index',
        };

        return redirect()->route($route)->with('success', 'Entidade eliminada com sucesso.');
    }
}
