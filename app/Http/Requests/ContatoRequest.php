<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContatoRequest extends FormRequest
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
            'telefone' => $this->telefone ?: null,
            'telemovel' => $this->telemovel ?: null,
            'email' => $this->email ?: null,
            'notas' => $this->notas ?: null,
            'funcao_contacto_id' => $this->funcao_contacto_id ?: null,
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
            'primeiro_nome' => ['required', 'string', 'max:100'],
            'apelido' => ['required', 'string', 'max:100'],
            'funcao_contacto_id' => ['nullable', 'integer', 'exists:funcoes_contato,id'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string'],
            'estado' => ['required', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'entidade_id.required' => 'A entidade é obrigatória.',
            'entidade_id.exists' => 'Entidade selecionada inválida.',
            'primeiro_nome.required' => 'O nome é obrigatório.',
            'apelido.required' => 'O apelido é obrigatório.',
            'email.email' => 'Endereço de email inválido.',
        ];
    }
}
