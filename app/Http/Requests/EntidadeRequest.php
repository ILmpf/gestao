<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Entidade;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class EntidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nif' => $this->nif ?: null,
            'morada' => $this->morada ?: null,
            'codigo_postal' => $this->codigo_postal ?: null,
            'cidade' => $this->cidade ?: null,
            'telefone' => $this->telefone ?: null,
            'telemovel' => $this->telemovel ?: null,
            'website' => $this->website ?: null,
            'email' => $this->email ?: null,
            'notas' => $this->notas ?: null,
            'prazo_pagamento_dias' => $this->prazo_pagamento_dias ?: null,
            'pais_id' => $this->pais_id ?: null,
        ]);
    }

    public function rules(): array
    {
        $entidadeId = $this->route('entidade')?->id;

        return [
            'tipos' => ['required', 'array', 'min:1'],
            'tipos.*' => ['in:cliente,fornecedor'],
            'nif' => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, Closure $fail) use ($entidadeId): void {
                    if (empty($value)) {
                        return;
                    }
                    $hash = hash('sha256', $value);
                    $exists = Entidade::where('nif_hash', $hash)
                        ->when($entidadeId, fn ($q) => $q->where('id', '!=', $entidadeId))
                        ->exists();
                    if ($exists) {
                        $fail('Este NIF já se encontra registado.');
                    }
                },
            ],
            'nome' => ['required', 'string', 'max:255'],
            'morada' => ['nullable', 'string', 'max:255'],
            'codigo_postal' => ['nullable', 'string', 'regex:/^\d{4}-\d{3}$/'],
            'cidade' => ['nullable', 'string', 'max:100'],
            'pais_id' => ['nullable', 'integer', 'exists:paises,id'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string'],
            'prazo_pagamento_dias' => ['nullable', 'integer', 'min:0', 'max:365'],
            'estado' => ['required', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipos.required' => 'Selecione pelo menos um tipo.',
            'tipos.min' => 'Selecione pelo menos um tipo.',
            'nome.required' => 'O nome é obrigatório.',
            'codigo_postal.regex' => 'Formato inválido. Use XXXX-XXX (ex: 1000-001).',
            'pais_id.exists' => 'País selecionado inválido.',
            'email.email' => 'Endereço de email inválido.',
            'website.url' => 'URL inválido. Deve começar com http:// ou https://.',
        ];
    }
}
