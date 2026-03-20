<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EncomendaClienteRequest;
use App\Models\Artigo;
use App\Models\ContaCorrenteCliente;
use App\Models\Empresa;
use App\Models\EncomendaCliente;
use App\Models\EncomendaFornecedor;
use App\Models\Entidade;
use App\Models\FaturaCliente;
use App\Models\LinhaEncomendaCliente;
use App\Models\LinhaEncomendaFornecedor;
use App\Models\NotaCreditoCliente;
use App\Models\TaxaIva;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EncomendaClienteController extends Controller
{
    /** @return array<string, mixed> */
    private function mapEncomenda(EncomendaCliente $e): array
    {
        return [
            'id' => $e->id,
            'numero' => $e->numero,
            'data_encomenda' => $e->data_encomenda?->format('Y-m-d'),
            'entidade_id' => $e->entidade_id,
            'entidade_nome' => $e->entidade?->nome,
            'proposta_id' => $e->proposta_id,
            'proposta_numero' => $e->proposta?->numero,
            'valor_total' => $e->valor_total,
            'estado' => $e->estado,
            'linhas' => $e->linhas->map(fn (LinhaEncomendaCliente $l) => [
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
                'subtotal' => $l->subtotal,
            ]),
        ];
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'clientes' => Entidade::clientes()
                ->get(['id', 'nome'])
                ->sortBy('nome')->values()
                ->map(fn (Entidade $e) => ['id' => $e->id, 'nome' => $e->nome]),
            'fornecedores' => Entidade::fornecedores()
                ->get(['id', 'nome'])
                ->sortBy('nome')->values()
                ->map(fn (Entidade $e) => ['id' => $e->id, 'nome' => $e->nome]),
            'artigos' => Artigo::where('estado', 'ativo')
                ->orderBy('referencia')
                ->get(['id', 'referencia', 'nome', 'preco', 'taxa_iva_id']),
            'taxas' => TaxaIva::where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome', 'taxa']),
        ];
    }

    public function index(): Response
    {
        $encomendas = EncomendaCliente::with(['entidade', 'linhas.taxaIva'])
            ->withExists([
                'movimentosCC as tem_fatura' => fn ($q) => $q->where('tipo', 'encomenda'),
                'movimentosCC as tem_nota_credito' => fn ($q) => $q->where('tipo', 'nota_credito'),
            ])
            ->orderByDesc('id')
            ->get()
            ->map(fn (EncomendaCliente $e) => [
                'id' => $e->id,
                'numero' => $e->numero,
                'data_encomenda' => $e->data_encomenda?->format('d/m/Y'),
                'entidade_nome' => $e->entidade?->nome,
                'valor_total' => $e->valor_total,
                'estado' => $e->estado,
                'tem_fatura' => (bool) $e->tem_fatura,
                'tem_nota_credito' => (bool) $e->tem_nota_credito,
            ]);

        return Inertia::render('encomendas/clientes/Index', [
            'encomendas' => $encomendas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('encomendas/clientes/Form', $this->formData());
    }

    public function store(EncomendaClienteRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $numero = 'EC-'.str_pad((string) ((EncomendaCliente::max('id') ?? 0) + 1), 5, '0', STR_PAD_LEFT);

            $enc = EncomendaCliente::create(array_merge(
                $request->safe()->except('linhas'),
                ['numero' => $numero],
            ));

            $this->syncLinhas($enc, $request->validated()['linhas']);

            if ($enc->estado === 'concluida') {
                $this->registarDebitoCC($enc);
                $this->emitirFaturaCliente($enc);
            }
        });

        return redirect()->route('encomendas.clientes.index')
            ->with('success', 'Encomenda criada com sucesso.');
    }

    public function edit(EncomendaCliente $encomendaCliente): Response
    {
        $encomendaCliente->load(['linhas.artigo', 'linhas.fornecedor', 'linhas.taxaIva']);

        return Inertia::render('encomendas/clientes/Form', array_merge(
            $this->formData(),
            ['encomenda' => $this->mapEncomenda($encomendaCliente)],
        ));
    }

    public function update(EncomendaClienteRequest $request, EncomendaCliente $encomendaCliente): RedirectResponse
    {
        $estadoAnterior = $encomendaCliente->estado;

        DB::transaction(function () use ($request, $encomendaCliente, $estadoAnterior): void {
            $encomendaCliente->update($request->safe()->except('linhas'));
            $encomendaCliente->linhas()->delete();
            $this->syncLinhas($encomendaCliente, $request->validated()['linhas']);

            $encomendaCliente->load('linhas.taxaIva');
            $estadoAtual = $encomendaCliente->estado;

            if ($estadoAnterior !== 'concluida' && $estadoAtual === 'concluida') {
                if (! ContaCorrenteCliente::where('encomenda_cliente_id', $encomendaCliente->id)
                    ->where('tipo', 'encomenda')->exists()) {
                    $this->registarDebitoCC($encomendaCliente);
                }
                $this->emitirFaturaCliente($encomendaCliente);
            }

            if ($estadoAnterior !== 'cancelada' && $estadoAtual === 'cancelada') {
                if (ContaCorrenteCliente::where('encomenda_cliente_id', $encomendaCliente->id)
                    ->where('tipo', 'encomenda')->exists()) {
                    $this->registarNotaCreditoCC($encomendaCliente);
                }
                $this->emitirNotaCreditoCliente($encomendaCliente);
            }
        });

        return redirect()->route('encomendas.clientes.index')
            ->with('success', 'Encomenda atualizada com sucesso.');
    }

    public function destroy(EncomendaCliente $encomendaCliente): RedirectResponse
    {
        $encomendaCliente->delete();

        return redirect()->route('encomendas.clientes.index')
            ->with('success', 'Encomenda eliminada com sucesso.');
    }

    public function pdf(EncomendaCliente $encomendaCliente): HttpResponse
    {
        $encomendaCliente->load(['entidade.pais', 'linhas.artigo', 'linhas.taxaIva']);
        $empresa = Empresa::first();

        $pdf = Pdf::loadView('pdf.encomenda_cliente', [
            'encomenda' => $encomendaCliente,
            'empresa' => $empresa,
        ])->setPaper('a4');

        return $pdf->download("Encomenda-{$encomendaCliente->numero}.pdf");
    }

    public function fatura(EncomendaCliente $encomendaCliente): HttpResponse
    {
        $encomendaCliente->load(['entidade.pais', 'linhas.artigo', 'linhas.taxaIva']);
        $empresa = Empresa::first();
        $ano = $encomendaCliente->data_encomenda?->year ?? now()->year;
        $numeroFT = 'FT '.str_pad((string) $encomendaCliente->id, 3, '0', STR_PAD_LEFT).'/'.$ano;

        $pdf = Pdf::loadView('pdf.fatura_cliente', [
            'encomenda' => $encomendaCliente,
            'empresa' => $empresa,
            'numero_ft' => $numeroFT,
        ])->setPaper('a4');

        return $pdf->download(str_replace('/', '-', $numeroFT).'.pdf');
    }

    public function notaCredito(EncomendaCliente $encomendaCliente): HttpResponse
    {
        $encomendaCliente->load(['entidade.pais', 'linhas.artigo', 'linhas.taxaIva', 'faturas']);
        $empresa = Empresa::first();
        $ano = now()->year;
        $numeroNC = 'NC '.str_pad((string) $encomendaCliente->id, 3, '0', STR_PAD_LEFT).'/'.$ano;
        $numeroFatura = $encomendaCliente->faturas->first()?->numero;

        $pdf = Pdf::loadView('pdf.nota_credito_cliente', [
            'encomenda' => $encomendaCliente,
            'empresa' => $empresa,
            'numero_nc' => $numeroNC,
            'numero_fatura' => $numeroFatura,
        ])->setPaper('a4');

        return $pdf->download(str_replace('/', '-', $numeroNC).'.pdf');
    }

    public function converterParaFornecedor(EncomendaCliente $encomendaCliente): RedirectResponse
    {
        abort_if($encomendaCliente->estado !== 'concluida', 422, 'Apenas encomendas concluídas podem ser convertidas.');

        DB::transaction(function () use ($encomendaCliente): void {
            $porFornecedor = $encomendaCliente->linhas
                ->filter(fn (LinhaEncomendaCliente $l) => $l->entidade_fornecedor_id !== null)
                ->groupBy('entidade_fornecedor_id');

            foreach ($porFornecedor as $fornecedorId => $linhas) {
                $ef = EncomendaFornecedor::create([
                    'numero' => 'EF-'.uniqid(),
                    'entidade_id' => $fornecedorId,
                    'encomenda_cliente_id' => $encomendaCliente->id,
                    'estado' => 'em_progresso',
                ]);
                $ef->numero = 'EF-'.str_pad((string) $ef->id, 5, '0', STR_PAD_LEFT);
                $ef->saveQuietly();

                foreach ($linhas as $linha) {
                    LinhaEncomendaFornecedor::create([
                        'encomenda_fornecedor_id' => $ef->id,
                        'artigo_id' => $linha->artigo_id,
                        'quantidade' => $linha->quantidade,
                        'preco_unitario' => $linha->preco_unitario,
                        'subtotal' => $linha->subtotal,
                    ]);
                }
            }
        });

        return redirect()->route('encomendas.fornecedores.index')
            ->with('success', 'Encomendas de fornecedor criadas com sucesso.');
    }

    /** @param array<int, array<string, mixed>> $linhas */
    private function syncLinhas(EncomendaCliente $enc, array $linhas): void
    {
        foreach ($linhas as $linha) {
            $qty = (float) $linha['quantidade'];
            $price = (float) $linha['preco_unitario'];

            $enc->linhas()->create(array_merge($linha, [
                'subtotal' => round($qty * $price, 2),
            ]));
        }
    }

    private function registarDebitoCC(EncomendaCliente $enc): void
    {
        $valor = $enc->valor_total;

        ContaCorrenteCliente::create([
            'entidade_id' => $enc->entidade_id,
            'encomenda_cliente_id' => $enc->id,
            'descricao' => "Encomenda {$enc->numero}",
            'tipo' => 'encomenda',
            'debito' => $valor,
            'credito' => 0,
            'saldo' => ContaCorrenteCliente::proximoSaldo((int) $enc->entidade_id, $valor, 0),
            'data' => $enc->data_encomenda ?? now()->toDateString(),
        ]);
    }

    private function registarNotaCreditoCC(EncomendaCliente $enc): void
    {
        $valor = $enc->valor_total;

        ContaCorrenteCliente::create([
            'entidade_id' => $enc->entidade_id,
            'encomenda_cliente_id' => $enc->id,
            'descricao' => "Nota de Crédito — Encomenda {$enc->numero}",
            'tipo' => 'nota_credito',
            'debito' => 0,
            'credito' => $valor,
            'saldo' => ContaCorrenteCliente::proximoSaldo((int) $enc->entidade_id, 0, $valor),
            'data' => now()->toDateString(),
        ]);
    }

    private function emitirFaturaCliente(EncomendaCliente $enc): void
    {
        if (FaturaCliente::where('encomenda_cliente_id', $enc->id)->exists()) {
            return;
        }

        $ano = now()->year;
        $enc->loadMissing('entidade');
        $dias = $enc->entidade?->prazo_pagamento_dias;

        FaturaCliente::create([
            'numero' => 'FT '.str_pad((string) $enc->id, 3, '0', STR_PAD_LEFT).'/'.$ano,
            'data_fatura' => now()->toDateString(),
            'data_vencimento' => $dias ? now()->addDays($dias)->toDateString() : null,
            'entidade_id' => $enc->entidade_id,
            'encomenda_cliente_id' => $enc->id,
            'valor_total' => $enc->valor_total,
            'estado' => 'pendente',
        ]);
    }

    private function emitirNotaCreditoCliente(EncomendaCliente $enc): void
    {
        $fatura = FaturaCliente::where('encomenda_cliente_id', $enc->id)->first();

        if (! $fatura) {
            return;
        }

        if (NotaCreditoCliente::where('encomenda_cliente_id', $enc->id)->exists()) {
            return;
        }

        $ano = now()->year;

        NotaCreditoCliente::create([
            'numero' => 'NC '.str_pad((string) $enc->id, 3, '0', STR_PAD_LEFT).'/'.$ano,
            'data_nota_credito' => now()->toDateString(),
            'entidade_id' => $enc->entidade_id,
            'encomenda_cliente_id' => $enc->id,
            'fatura_cliente_id' => $fatura->id,
            'valor_total' => $enc->valor_total,
            'motivo' => "Cancelamento da Encomenda {$enc->numero}",
            'estado' => 'pendente',
        ]);
    }
}
