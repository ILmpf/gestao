<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PropostaRequest;
use App\Models\Artigo;
use App\Models\Empresa;
use App\Models\EncomendaCliente;
use App\Models\Entidade;
use App\Models\LinhaProposta;
use App\Models\Proposta;
use App\Models\TaxaIva;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PropostaController extends Controller
{
    /** @return array<string, mixed> */
    private function mapProposta(Proposta $p): array
    {
        return [
            'id' => $p->id,
            'numero' => $p->numero,
            'data_proposta' => $p->data_proposta?->format('Y-m-d'),
            'validade' => $p->validade?->format('Y-m-d'),
            'entidade_id' => $p->entidade_id,
            'entidade_nome' => $p->entidade?->nome,
            'valor_total' => $p->valor_total,
            'estado' => $p->estado,
            'linhas' => $p->linhas->map(fn (LinhaProposta $l) => [
                'id' => $l->id,
                'artigo_id' => $l->artigo_id,
                'artigo_referencia' => $l->artigo?->referencia,
                'artigo_nome' => $l->artigo?->nome,
                'entidade_fornecedor_id' => $l->entidade_fornecedor_id,
                'fornecedor_nome' => $l->fornecedor?->nome,
                'taxa_iva_id' => $l->taxa_iva_id,
                'taxa_iva_valor' => $l->taxaIva?->taxa,
                'quantidade' => $l->quantidade,
                'preco_unitario' => $l->preco_unitario,
                'preco_custo' => $l->preco_custo,
                'subtotal' => $l->subtotal,
            ]),
        ];
    }

    public function index(): Response
    {
        $propostas = Proposta::with('entidade')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Proposta $p) => [
                'id' => $p->id,
                'numero' => $p->numero,
                'data_proposta' => $p->data_proposta?->format('d/m/Y'),
                'validade' => $p->validade?->format('d/m/Y'),
                'entidade_nome' => $p->entidade?->nome,
                'valor_total' => $p->valor_total,
                'estado' => $p->estado,
            ]);

        return Inertia::render('propostas/Index', [
            'propostas' => $propostas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('propostas/Form', $this->formData());
    }

    public function store(PropostaRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $numero = 'PR-'.str_pad((string) ((Proposta::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);
            $proposta = Proposta::create(array_merge(
                $request->safe()->except('linhas'),
                ['numero' => $numero],
            ));

            $this->syncLinhas($proposta, $request->validated()['linhas']);
        });

        return redirect()->route('propostas.index')
            ->with('success', 'Proposta criada com sucesso.');
    }

    public function edit(Proposta $proposta): Response
    {
        $proposta->load(['linhas.artigo', 'linhas.fornecedor', 'linhas.taxaIva']);

        return Inertia::render('propostas/Form', array_merge(
            $this->formData(),
            ['proposta' => $this->mapProposta($proposta)],
        ));
    }

    public function update(PropostaRequest $request, Proposta $proposta): RedirectResponse
    {
        DB::transaction(function () use ($request, $proposta): void {
            $proposta->update($request->safe()->except('linhas'));
            $proposta->linhas()->delete();
            $this->syncLinhas($proposta, $request->validated()['linhas']);
        });

        return redirect()->route('propostas.index')
            ->with('success', 'Proposta atualizada com sucesso.');
    }

    public function destroy(Proposta $proposta): RedirectResponse
    {
        $proposta->delete();

        return redirect()->route('propostas.index')
            ->with('success', 'Proposta eliminada com sucesso.');
    }

    public function pdf(Proposta $proposta): HttpResponse
    {
        $proposta->load(['entidade.pais', 'linhas.artigo', 'linhas.taxaIva']);
        $empresa = Empresa::first();

        $pdf = Pdf::loadView('pdf.proposta', [
            'proposta' => $proposta,
            'empresa' => $empresa,
        ])->setPaper('a4');

        return $pdf->download("Proposta-{$proposta->numero}.pdf");
    }

    public function converter(Proposta $proposta): RedirectResponse
    {
        abort_if($proposta->estado !== 'concluida', 422, 'Apenas propostas concluídas podem ser convertidas.');

        $encomenda = DB::transaction(function () use ($proposta) {
            $numero = 'EC-'.str_pad(
                (string) (EncomendaCliente::max('id') ?? 0) + 1,
                5, '0', STR_PAD_LEFT
            );

            $enc = EncomendaCliente::create([
                'numero' => $numero,
                'entidade_id' => $proposta->entidade_id,
                'proposta_id' => $proposta->id,
                'estado' => 'em_progresso',
            ]);

            foreach ($proposta->linhas as $linha) {
                $enc->linhas()->create([
                    'artigo_id' => $linha->artigo_id,
                    'entidade_fornecedor_id' => $linha->entidade_fornecedor_id,
                    'taxa_iva_id' => $linha->taxa_iva_id,
                    'quantidade' => $linha->quantidade,
                    'preco_unitario' => $linha->preco_unitario,
                    'subtotal' => $linha->subtotal,
                ]);
            }

            return $enc;
        });

        return redirect()->route('encomendas.clientes.index', $encomenda)
            ->with('success', 'Proposta convertida em encomenda com sucesso.');
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'clientes' => Entidade::clientes()
                ->get(['id', 'nome'])
                ->sortBy('nome')
                ->values()
                ->map(fn (Entidade $e) => ['id' => $e->id, 'nome' => $e->nome]),
            'fornecedores' => Entidade::fornecedores()
                ->get(['id', 'nome'])
                ->sortBy('nome')
                ->values()
                ->map(fn (Entidade $e) => ['id' => $e->id, 'nome' => $e->nome]),
            'artigos' => Artigo::where('estado', 'ativo')
                ->orderBy('referencia')
                ->get(['id', 'referencia', 'nome', 'preco', 'taxa_iva_id']),
            'taxas' => TaxaIva::where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome', 'taxa']),
        ];
    }

    /** @param array<int, array<string, mixed>> $linhas */
    private function syncLinhas(Proposta $proposta, array $linhas): void
    {
        foreach ($linhas as $linha) {
            $qty = (float) $linha['quantidade'];
            $price = (float) $linha['preco_unitario'];

            $proposta->linhas()->create(array_merge($linha, [
                'subtotal' => round($qty * $price, 2),
            ]));
        }
    }
}
