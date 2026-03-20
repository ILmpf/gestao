<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FaturaFornecedorRequest extends FormRequest
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
            'data_fatura' => $this->data_fatura ?: null,
            'data_vencimento' => $this->data_vencimento ?: null,
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
            'numero' => ['required', 'string', 'max:50'],
            'data_fatura' => ['required', 'date'],
            'data_vencimento' => ['nullable', 'date', 'after_or_equal:data_fatura'],
            'entidade_id' => ['required', 'integer', 'exists:entidades,id'],
            'encomenda_fornecedor_id' => ['nullable', 'integer', 'exists:encomendas_fornecedores,id'],
            'valor_total' => ['required', 'numeric', 'min:0'],
            'documento' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'comprovativo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'estado' => ['required', 'in:pendente,paga'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero.required' => 'O número da fatura é obrigatório.',
            'data_fatura.required' => 'A data da fatura é obrigatória.',
            'entidade_id.required' => 'O fornecedor é obrigatório.',
            'valor_total.required' => 'O valor total é obrigatório.',
            'documento.mimes' => 'O documento deve ser PDF ou imagem.',
            'comprovativo.mimes' => 'O comprovativo deve ser PDF ou imagem.',
        ];
    }
}
