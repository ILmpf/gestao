<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NotaCreditoFornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'data_nota_credito' => $this->data_nota_credito ?: null,
        ]);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'numero' => ['required', 'string', 'max:50'],
            'data_nota_credito' => ['required', 'date'],
            'entidade_id' => ['required', 'integer', 'exists:entidades,id'],
            'encomenda_fornecedor_id' => ['nullable', 'integer', 'exists:encomendas_fornecedores,id'],
            'fatura_fornecedor_id' => ['nullable', 'integer', 'exists:faturas_fornecedores,id'],
            'valor_total' => ['required', 'numeric', 'min:0'],
            'motivo' => ['nullable', 'string', 'max:500'],
            'estado' => ['required', 'in:pendente,processada'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'O número da nota de crédito é obrigatório.',
            'data_nota_credito.required' => 'A data da nota de crédito é obrigatória.',
            'entidade_id.required' => 'O fornecedor é obrigatório.',
            'valor_total.required' => 'O valor total é obrigatório.',
        ];
    }
}
