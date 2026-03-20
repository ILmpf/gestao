<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\EncomendaFornecedor;
use App\Models\LinhaEncomendaFornecedor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class EncomendaFornecedorController extends Controller
{
    public function index(): Response
    {
        $encomendas = EncomendaFornecedor::with('entidade')
            ->orderByDesc('id')
            ->get()
            ->map(fn (EncomendaFornecedor $e) => [
                'id' => $e->id,
                'numero' => $e->numero,
                'data_encomenda' => $e->data_encomenda?->format('d/m/Y'),
                'entidade_nome' => $e->entidade?->nome,
                'valor_total' => $e->valor_total,
                'estado' => $e->estado,
            ]);

        return Inertia::render('encomendas/fornecedores/Index', [
            'encomendas' => $encomendas,
        ]);
    }

    public function show(EncomendaFornecedor $encomendaFornecedor): Response
    {
        $encomendaFornecedor->load(['entidade', 'linhas.artigo', 'encomendaCliente']);

        return Inertia::render('encomendas/fornecedores/Show', [
            'encomenda' => [
                'id' => $encomendaFornecedor->id,
                'numero' => $encomendaFornecedor->numero,
                'data_encomenda' => $encomendaFornecedor->data_encomenda?->format('d/m/Y'),
                'entidade_nome' => $encomendaFornecedor->entidade?->nome,
                'encomenda_cliente_num' => $encomendaFornecedor->encomendaCliente?->numero,
                'valor_total' => $encomendaFornecedor->valor_total,
                'estado' => $encomendaFornecedor->estado,
                'linhas' => $encomendaFornecedor->linhas->map(fn (LinhaEncomendaFornecedor $l) => [
                    'artigo_referencia' => $l->artigo?->referencia,
                    'artigo_nome' => $l->artigo?->nome,
                    'quantidade' => $l->quantidade,
                    'preco_unitario' => $l->preco_unitario,
                    'subtotal' => $l->subtotal,
                ]),
            ],
        ]);
    }

    public function destroy(EncomendaFornecedor $encomendaFornecedor): RedirectResponse
    {
        $encomendaFornecedor->delete();

        return redirect()->route('encomendas.fornecedores.index')
            ->with('success', 'Encomenda eliminada com sucesso.');
    }

    public function pdf(EncomendaFornecedor $encomendaFornecedor): HttpResponse
    {
        $encomendaFornecedor->load(['entidade.pais', 'linhas.artigo']);
        $empresa = Empresa::first();

        $pdf = Pdf::loadView('pdf.encomenda_fornecedor', [
            'encomenda' => $encomendaFornecedor,
            'empresa' => $empresa,
        ])->setPaper('a4');

        return $pdf->download("EncomendaFornecedor-{$encomendaFornecedor->numero}.pdf");
    }
}
