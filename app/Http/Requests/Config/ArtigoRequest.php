<?php

declare(strict_types=1);

namespace App\Http\Requests\Config;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArtigoRequest extends FormRequest
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
            'descricao' => $this->descricao ?: null,
            'notas' => $this->notas ?: null,
            'taxa_iva_id' => $this->taxa_iva_id ?: null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $artigo = $this->route('artigo');

        return [
            'referencia' => [
                'required',
                'string',
                'max:50',
                Rule::unique('artigos', 'referencia')->ignore($artigo?->id)->whereNull('deleted_at'),
            ],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'preco' => ['required', 'numeric', 'min:0'],
            'taxa_iva_id' => ['nullable', 'integer', 'exists:taxas_iva,id'],
            'imagem_artigo' => ['nullable', 'image', 'max:4096'],
            'notas' => ['nullable', 'string'],
            'estado' => ['required', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'referencia.required' => 'A referência é obrigatória.',
            'referencia.unique' => 'Esta referência já existe.',
            'nome.required' => 'O nome é obrigatório.',
            'preco.required' => 'O preço é obrigatório.',
            'preco.numeric' => 'O preço deve ser um valor numérico.',
            'imagem_artigo.image' => 'O ficheiro deve ser uma imagem (jpg, png, gif, webp).',
            'imagem_artigo.max' => 'A imagem não pode ultrapassar 4 MB.',
        ];
    }
}
