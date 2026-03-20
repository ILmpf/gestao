<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do grupo é obrigatório.',
            'name.unique'   => 'Já existe um grupo com este nome.',
        ];
    }
}
