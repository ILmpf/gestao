<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\NotaCreditoFornecedorRequest;
use App\Models\EncomendaFornecedor;
use App\Models\Entidade;
use App\Models\FaturaFornecedor;
use App\Models\NotaCreditoFornecedor;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotaCreditoFornecedorController extends Controller
{
    /** @return array<string, mixed> */
    private function mapNota(NotaCreditoFornecedor $n): array
    {
        return [
            'id' => $n->id,
            'numero' => $n->numero,
            'data_nota_credito' => $n->data_nota_credito?->format('Y-m-d'),
            'entidade_id' => $n->entidade_id,
            'entidade_nome' => $n->entidade?->nome,
            'encomenda_fornecedor_id' => $n->encomenda_fornecedor_id,
            'encomenda_numero' => $n->encomendaFornecedor?->numero,
            'fatura_fornecedor_id' => $n->fatura_fornecedor_id,
            'fatura_numero' => $n->faturaFornecedor?->numero,
            'valor_total' => $n->valor_total,
            'motivo' => $n->motivo,
            'estado' => $n->estado,
        ];
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'fornecedores' => Entidade::fornecedores()
                ->get(['id', 'nome'])
                ->sortBy('nome')->values()
                ->map(fn (Entidade $e) => ['id' => $e->id, 'nome' => $e->nome]),
            'encomendas_fornecedor' => EncomendaFornecedor::orderByDesc('id')
                ->get(['id', 'numero', 'entidade_id'])
                ->map(fn (EncomendaFornecedor $ef) => [
                    'id' => $ef->id,
                    'numero' => $ef->numero,
                    'entidade_id' => $ef->entidade_id,
                ]),
            'faturas_fornecedor' => FaturaFornecedor::orderByDesc('id')
                ->get(['id', 'numero', 'entidade_id'])
                ->map(fn (FaturaFornecedor $ff) => [
                    'id' => $ff->id,
                    'numero' => $ff->numero,
                    'entidade_id' => $ff->entidade_id,
                ]),
        ];
    }

    public function index(): Response
    {
        $notas = NotaCreditoFornecedor::with(['entidade', 'encomendaFornecedor', 'faturaFornecedor'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (NotaCreditoFornecedor $n) => [
                'id' => $n->id,
                'numero' => $n->numero,
                'data_nota_credito' => $n->data_nota_credito?->format('d/m/Y'),
                'entidade_nome' => $n->entidade?->nome,
                'fatura_numero' => $n->faturaFornecedor?->numero,
                'valor_total' => $n->valor_total,
                'estado' => $n->estado,
            ]);

        return Inertia::render('financeiro/notas-credito-fornecedores/Index', [
            'notas' => $notas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('financeiro/notas-credito-fornecedores/Form', $this->formData());
    }

    public function store(NotaCreditoFornecedorRequest $request): RedirectResponse
    {
        NotaCreditoFornecedor::create($request->validated());

        return redirect()->route('financeiro.notas-credito-fornecedores.index')
            ->with('success', 'Nota de crédito registada com sucesso.');
    }

    public function edit(NotaCreditoFornecedor $notaCreditoFornecedor): Response
    {
        $notaCreditoFornecedor->load(['entidade', 'encomendaFornecedor', 'faturaFornecedor']);

        return Inertia::render('financeiro/notas-credito-fornecedores/Form', array_merge(
            $this->formData(),
            ['nota' => $this->mapNota($notaCreditoFornecedor)],
        ));
    }

    public function update(NotaCreditoFornecedorRequest $request, NotaCreditoFornecedor $notaCreditoFornecedor): RedirectResponse
    {
        $notaCreditoFornecedor->update($request->validated());

        return redirect()->route('financeiro.notas-credito-fornecedores.index')
            ->with('success', 'Nota de crédito atualizada com sucesso.');
    }

    public function destroy(NotaCreditoFornecedor $notaCreditoFornecedor): RedirectResponse
    {
        $notaCreditoFornecedor->delete();

        return redirect()->route('financeiro.notas-credito-fornecedores.index')
            ->with('success', 'Nota de crédito eliminada com sucesso.');
    }
}
