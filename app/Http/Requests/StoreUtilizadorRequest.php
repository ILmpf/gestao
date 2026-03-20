<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUtilizadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telemovel' => $this->telemovel ?: null,
            'role'      => $this->role ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'telemovel'             => ['nullable', 'string', 'max:20'],
            'password'              => ['required', 'string', 'confirmed', Password::defaults()],
            'role'                  => ['nullable', 'string', 'exists:roles,name'],
            'estado'                => ['required', 'in:ativo,inativo'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'O nome é obrigatório.',
            'email.required'     => 'O email é obrigatório.',
            'email.unique'       => 'Este email já está em uso.',
            'password.required'  => 'A palavra-passe é obrigatória.',
            'password.confirmed' => 'A confirmação da palavra-passe não coincide.',
        ];
    }
}
