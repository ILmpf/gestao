<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FaturaFornecedorRequest;
use App\Mail\ComprovativoFornecedorMail;
use App\Models\EncomendaFornecedor;
use App\Models\Entidade;
use App\Models\FaturaFornecedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FaturaFornecedorController extends Controller
{
    /** @return array<string, mixed> */
    private function mapFatura(FaturaFornecedor $f): array
    {
        return [
            'id' => $f->id,
            'numero' => $f->numero,
            'data_fatura' => $f->data_fatura?->format('Y-m-d'),
            'data_vencimento' => $f->data_vencimento?->format('Y-m-d'),
            'entidade_id' => $f->entidade_id,
            'entidade_nome' => $f->entidade?->nome,
            'encomenda_fornecedor_id' => $f->encomenda_fornecedor_id,
            'encomenda_numero' => $f->encomendaFornecedor?->numero,
            'valor_total' => $f->valor_total,
            'caminho_documento' => $f->caminho_documento,
            'caminho_comprovativo' => $f->caminho_comprovativo,
            'estado' => $f->estado,
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
            'encomendas_fornecedor' => EncomendaFornecedor::with('entidade')
                ->orderByDesc('id')
                ->get(['id', 'numero', 'entidade_id'])
                ->map(fn (EncomendaFornecedor $ef) => [
                    'id' => $ef->id,
                    'numero' => $ef->numero,
                    'entidade_id' => $ef->entidade_id,
                ]),
        ];
    }

    public function index(): Response
    {
        $faturas = FaturaFornecedor::with(['entidade', 'encomendaFornecedor'])
            ->orderByDesc('id')
            ->get()
            ->map(fn (FaturaFornecedor $f) => [
                'id' => $f->id,
                'numero' => $f->numero,
                'data_fatura' => $f->data_fatura?->format('d/m/Y'),
                'entidade_nome' => $f->entidade?->nome,
                'encomenda_numero' => $f->encomendaFornecedor?->numero,
                'valor_total' => $f->valor_total,
                'caminho_documento' => $f->caminho_documento,
                'estado' => $f->estado,
            ]);

        return Inertia::render('financeiro/faturas-fornecedores/Index', [
            'faturas' => $faturas,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('financeiro/faturas-fornecedores/Form', $this->formData());
    }

    public function store(FaturaFornecedorRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['documento', 'comprovativo']);

        if ($request->hasFile('documento')) {
            $data['caminho_documento'] = $request->file('documento')
                ->store('faturas/documentos', 'private');
        }

        if ($request->hasFile('comprovativo')) {
            $data['caminho_comprovativo'] = $request->file('comprovativo')
                ->store('faturas/comprovativos', 'private');
        }

        $fatura = FaturaFornecedor::create($data);

        if (
            $fatura->estado === 'paga'
            && $fatura->caminho_comprovativo
            && $fatura->entidade?->email
        ) {
            Mail::to($fatura->entidade->email)
                ->send(new ComprovativoFornecedorMail($fatura, $fatura->caminho_comprovativo));
        }

        return redirect()->route('financeiro.faturas-fornecedores.index')
            ->with('success', 'Fatura registada com sucesso.');
    }

    public function edit(FaturaFornecedor $faturaFornecedor): Response
    {
        $faturaFornecedor->load(['entidade', 'encomendaFornecedor']);

        return Inertia::render('financeiro/faturas-fornecedores/Form', array_merge(
            $this->formData(),
            ['fatura' => $this->mapFatura($faturaFornecedor)],
        ));
    }

    public function update(FaturaFornecedorRequest $request, FaturaFornecedor $faturaFornecedor): RedirectResponse
    {
        $wasNotPaga = $faturaFornecedor->estado !== 'paga';
        $data = $request->safe()->except(['documento', 'comprovativo']);

        if ($request->hasFile('documento')) {
            if ($faturaFornecedor->caminho_documento) {
                Storage::disk('private')->delete($faturaFornecedor->caminho_documento);
            }
            $data['caminho_documento'] = $request->file('documento')
                ->store('faturas/documentos', 'private');
        }

        if ($request->hasFile('comprovativo')) {
            if ($faturaFornecedor->caminho_comprovativo) {
                Storage::disk('private')->delete($faturaFornecedor->caminho_comprovativo);
            }
            $data['caminho_comprovativo'] = $request->file('comprovativo')
                ->store('faturas/comprovativos', 'private');
        }

        $faturaFornecedor->update($data);

        if (
            $wasNotPaga
            && $faturaFornecedor->estado === 'paga'
            && $faturaFornecedor->caminho_comprovativo
            && $faturaFornecedor->entidade?->email
        ) {
            Mail::to($faturaFornecedor->entidade->email)
                ->send(new ComprovativoFornecedorMail($faturaFornecedor, $faturaFornecedor->caminho_comprovativo));
        }

        return redirect()->route('financeiro.faturas-fornecedores.index')
            ->with('success', 'Fatura atualizada com sucesso.');
    }

    public function destroy(FaturaFornecedor $faturaFornecedor): RedirectResponse
    {
        if ($faturaFornecedor->caminho_documento) {
            Storage::disk('private')->delete($faturaFornecedor->caminho_documento);
        }
        if ($faturaFornecedor->caminho_comprovativo) {
            Storage::disk('private')->delete($faturaFornecedor->caminho_comprovativo);
        }

        $faturaFornecedor->delete();

        return redirect()->route('financeiro.faturas-fornecedores.index')
            ->with('success', 'Fatura eliminada com sucesso.');
    }
}
