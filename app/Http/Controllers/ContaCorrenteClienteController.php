<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContaCorrenteClienteRequest;
use App\Models\ContaCorrenteCliente;
use App\Models\Entidade;
use App\Models\FaturaCliente;
use App\Models\NotaCreditoCliente;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContaCorrenteClienteController extends Controller
{
    public function index(): Response
    {
        $clientes = Entidade::clientes()
            ->get(['id', 'nome'])
            ->map(function (Entidade $e): array {
                $totais = ContaCorrenteCliente::where('entidade_id', $e->id)
                    ->selectRaw('SUM(debito) as total_debito, SUM(credito) as total_credito')
                    ->first();

                $totalDebito = (float) ($totais->total_debito ?? 0);
                $totalCredito = (float) ($totais->total_credito ?? 0);

                return [
                    'entidade_id' => $e->id,
                    'entidade_nome' => $e->nome,
                    'total_debito' => $totalDebito,
                    'total_credito' => $totalCredito,
                    'saldo_atual' => round($totalDebito - $totalCredito, 2),
                ];
            })
            ->sortBy('entidade_nome')
            ->values();

        return Inertia::render('financeiro/conta-corrente-clientes/Index', [
            'clientes' => $clientes,
        ]);
    }

    public function show(Entidade $entidade): Response
    {
        $movimentos = ContaCorrenteCliente::where('entidade_id', $entidade->id)
            ->orderBy('data')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $saldoCorrido = 0.0;

        $movimentos = $movimentos->map(function (ContaCorrenteCliente $m) use (&$saldoCorrido): array {
            $numero_documento = null;
            $data_documento = null;
            $data_vencimento = null;

            if ($m->encomenda_cliente_id !== null) {
                if ($m->tipo === 'encomenda') {
                    $fatura = FaturaCliente::where('encomenda_cliente_id', $m->encomenda_cliente_id)->first();
                    if ($fatura) {
                        $numero_documento = $fatura->numero;
                        $data_documento = $fatura->data_fatura?->format('d/m/Y');
                        $data_vencimento = $fatura->data_vencimento?->format('d/m/Y');
                    }
                } elseif ($m->tipo === 'nota_credito') {
                    $nota = NotaCreditoCliente::where('encomenda_cliente_id', $m->encomenda_cliente_id)->first();
                    if ($nota) {
                        $numero_documento = $nota->numero;
                        $data_documento = $nota->data_nota_credito?->format('d/m/Y');
                    }
                }
            }

            $saldoCorrido = round($saldoCorrido + (float) $m->debito - (float) $m->credito, 2);

            return [
                'id' => $m->id,
                'numero_documento' => $numero_documento,
                'data_documento' => $data_documento,
                'data_vencimento' => $data_vencimento,
                'data' => $m->data?->format('d/m/Y'),
                'descricao' => $m->descricao,
                'tipo' => $m->tipo ?? 'manual',
                'encomenda_cliente_id' => $m->encomenda_cliente_id,
                'debito' => $m->debito,
                'credito' => $m->credito,
                'saldo' => $saldoCorrido,
            ];
        });

        return Inertia::render('financeiro/conta-corrente-clientes/Show', [
            'entidade' => ['id' => $entidade->id, 'nome' => $entidade->nome],
            'movimentos' => $movimentos,
        ]);
    }

    public function create(Entidade $entidade): Response
    {
        return Inertia::render('financeiro/conta-corrente-clientes/Form', [
            'entidade' => ['id' => $entidade->id, 'nome' => $entidade->nome],
        ]);
    }

    public function store(ContaCorrenteClienteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        [$debito, $credito] = $this->resolveDebitoCredito($validated['tipo_lancamento'], (float) $validated['valor']);

        unset($validated['tipo_lancamento'], $validated['valor']);
        $validated['debito'] = $debito;
        $validated['credito'] = $credito;
        $validated['tipo'] = 'manual';
        $validated['saldo'] = ContaCorrenteCliente::proximoSaldo((int) $validated['entidade_id'], $debito, $credito);

        ContaCorrenteCliente::create($validated);

        return redirect()->route('financeiro.conta-corrente-clientes.show', $validated['entidade_id'])
            ->with('success', 'Movimento registado com sucesso.');
    }

    public function edit(ContaCorrenteCliente $contaCorrenteCliente): Response
    {
        $contaCorrenteCliente->load('entidade');

        $debito = (float) $contaCorrenteCliente->debito;
        $credito = (float) $contaCorrenteCliente->credito;

        return Inertia::render('financeiro/conta-corrente-clientes/Form', [
            'entidade' => [
                'id' => $contaCorrenteCliente->entidade->id,
                'nome' => $contaCorrenteCliente->entidade->nome,
            ],
            'movimento' => [
                'id' => $contaCorrenteCliente->id,
                'entidade_id' => $contaCorrenteCliente->entidade_id,
                'tipo_lancamento' => $debito > 0 ? 'fatura' : 'nota_credito',
                'valor' => $debito > 0 ? $debito : $credito,
                'descricao' => $contaCorrenteCliente->descricao,
                'data' => $contaCorrenteCliente->data?->format('Y-m-d'),
            ],
        ]);
    }

    public function update(ContaCorrenteClienteRequest $request, ContaCorrenteCliente $contaCorrenteCliente): RedirectResponse
    {
        $validated = $request->validated();

        [$debito, $credito] = $this->resolveDebitoCredito($validated['tipo_lancamento'], (float) $validated['valor']);

        unset($validated['tipo_lancamento'], $validated['valor']);
        $validated['debito'] = $debito;
        $validated['credito'] = $credito;

        $anterior = ContaCorrenteCliente::where('entidade_id', $validated['entidade_id'])
            ->where('id', '<', $contaCorrenteCliente->id)
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->value('saldo') ?? 0.0;

        $validated['saldo'] = round((float) $anterior + $debito - $credito, 2);

        $contaCorrenteCliente->update($validated);

        return redirect()->route('financeiro.conta-corrente-clientes.show', $contaCorrenteCliente->entidade_id)
            ->with('success', 'Movimento atualizado com sucesso.');
    }

    /** @return array{float, float} [debito, credito] */
    private function resolveDebitoCredito(string $tipo, float $valor): array
    {
        return $tipo === 'fatura'
            ? [$valor, 0.0]
            : [0.0, $valor];
    }

    public function destroy(ContaCorrenteCliente $contaCorrenteCliente): RedirectResponse
    {
        $entidadeId = $contaCorrenteCliente->entidade_id;
        $contaCorrenteCliente->delete();

        return redirect()->route('financeiro.conta-corrente-clientes.show', $entidadeId)
            ->with('success', 'Movimento eliminado com sucesso.');
    }
}
