<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PropostaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'data_proposta' => $this->data_proposta ?: null,
            'validade' => $this->validade ?: null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'entidade_id' => ['required', 'integer', 'exists:entidades,id'],
            'data_proposta' => ['nullable', 'date'],
            'validade' => ['nullable', 'date'],
            'estado' => ['required', 'in:apresentada,concluida,rejeitada'],
            'linhas' => ['required', 'array', 'min:1'],
            'linhas.*.artigo_id' => ['required', 'integer', 'exists:artigos,id'],
            'linhas.*.entidade_fornecedor_id' => ['nullable', 'integer', 'exists:entidades,id'],
            'linhas.*.taxa_iva_id' => ['nullable', 'integer', 'exists:taxas_iva,id'],
            'linhas.*.quantidade' => ['required', 'numeric', 'min:0.01'],
            'linhas.*.preco_unitario' => ['required', 'numeric', 'min:0'],
            'linhas.*.preco_custo' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'entidade_id.required' => 'O cliente é obrigatório.',
            'entidade_id.exists' => 'Cliente inválido.',
            'linhas.required' => 'Adicione pelo menos uma linha.',
            'linhas.min' => 'Adicione pelo menos uma linha.',
            'linhas.*.artigo_id.required' => 'Selecione o artigo em cada linha.',
            'linhas.*.quantidade.min' => 'A quantidade deve ser superior a zero.',
            'linhas.*.preco_unitario.min' => 'O preço não pode ser negativo.',
        ];
    }
}
