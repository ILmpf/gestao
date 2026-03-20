<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EncomendaCliente;
use App\Models\Entidade;
use App\Models\FaturaCliente;
use App\Models\FaturaFornecedor;
use App\Models\Proposta;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $now = now();

        $stats = [
            'clientes_ativos'                     => Entidade::clientes()->where('estado', 'ativo')->count(),
            'fornecedores_ativos'                 => Entidade::fornecedores()->where('estado', 'ativo')->count(),
            'encomendas_em_progresso'             => EncomendaCliente::where('estado', 'em_progresso')->count(),
            'encomendas_concluidas_mes'           => EncomendaCliente::where('estado', 'concluida')
                ->whereMonth('updated_at', $now->month)
                ->whereYear('updated_at', $now->year)
                ->count(),
            'faturas_clientes_pendentes'          => FaturaCliente::where('estado', 'pendente')->count(),
            'faturas_clientes_pendentes_valor'    => (float) FaturaCliente::where('estado', 'pendente')->sum('valor_total'),
            'faturas_clientes_mes_valor'          => (float) FaturaCliente::whereMonth('data_fatura', $now->month)
                ->whereYear('data_fatura', $now->year)
                ->sum('valor_total'),
            'faturas_fornecedores_pendentes'      => FaturaFornecedor::where('estado', 'pendente')->count(),
            'faturas_fornecedores_pendentes_valor' => (float) FaturaFornecedor::where('estado', 'pendente')->sum('valor_total'),
            'propostas_abertas'                   => Proposta::where('estado', 'apresentada')->count(),
        ];

        $ultimasEncomendas = EncomendaCliente::with('entidade')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (EncomendaCliente $e) => [
                'id'       => $e->id,
                'numero'   => $e->numero,
                'data'     => $e->data_encomenda->format('d/m/Y'),
                'entidade' => $e->entidade->nome,
                'estado'   => $e->estado,
            ]);

        $ultimasFaturas = FaturaCliente::with('entidade')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (FaturaCliente $f) => [
                'id'          => $f->id,
                'numero'      => $f->numero,
                'data'        => $f->data_fatura->format('d/m/Y'),
                'entidade'    => $f->entidade->nome,
                'valor_total' => (float) $f->valor_total,
                'estado'      => $f->estado,
            ]);

        return Inertia::render('Dashboard', [
            'stats'             => $stats,
            'ultimasEncomendas' => $ultimasEncomendas,
            'ultimasFaturas'    => $ultimasFaturas,
        ]);
    }
}
