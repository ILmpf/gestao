<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContaCorrenteClienteRequest extends FormRequest
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
            'data' => $this->has('data_lancamento')
                ? ($this->data_lancamento ?: null)
                : ($this->data ?: null),
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
            'tipo_lancamento' => ['required', 'string', 'in:fatura,nota_credito,pagamento'],
            'valor' => ['required', 'numeric', 'min:0'],
            'descricao' => ['required', 'string', 'max:255'],
            'data' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'entidade_id.required' => 'Selecione um cliente.',
            'descricao.required' => 'A descrição é obrigatória.',
            'data.required' => 'A data é obrigatória.',
        ];
    }
}
